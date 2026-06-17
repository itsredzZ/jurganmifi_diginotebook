<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitReport extends Component
{
    public string $period = 'Semua';
    public string $startDate = '';
    public string $endDate = '';

    public int $selectedChartYear = 2026;
    public string $volumeChartMode = 'bulan'; // 'bulan' | 'tahun'

    public function mount(): void
    {
        $this->selectedChartYear = (int) now()->year;
    }

    public function updatedPeriod(): void
    {
        $this->dispatch('refresh-side-charts', data: $this->getSideChartData());
    }

    public function updatedSelectedChartYear(): void
    {
        $this->dispatch('refresh-volume-chart', data: $this->getVolumeChartData());
    }

    public function setVolumeMode(string $mode): void
    {
        $this->volumeChartMode = $mode;
        $this->dispatch('refresh-volume-chart', data: $this->getVolumeChartData());
    }

    public function applyCustomDate(): void
    {
        $this->period = 'Custom';
        $this->dispatch('refresh-side-charts', data: $this->getSideChartData());
    }

    private function applyPeriodFilter($query)
    {
        switch ($this->period) {
            case '7 Hari':
                return $query->where('date', '>=', now()->subDays(7));
            case '30 Hari':
                return $query->where('date', '>=', now()->subDays(30));
            case 'Bulan Ini':
                return $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
            case 'Bulan Lalu':
                return $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year);
            case 'Custom':
                if ($this->startDate && $this->endDate) {
                    return $query->whereBetween('date', [$this->startDate, $this->endDate]);
                }
                return $query;
            default:
                return $query;
        }
    }

    private function getVolumeChartData(): array
    {
        if ($this->volumeChartMode === 'tahun') {
            $years  = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
            $totals = [];
            foreach ($years as $yr) {
                $totals[] = (int) Sale::whereYear('date', $yr)->sum('quantity');
            }

            return [
                'mode'     => 'tahun',
                'labels'   => $years,
                'datasets' => [
                    ['label' => 'Total Unit per Tahun', 'data' => $totals, 'color' => '#06b6d4'],
                ],
            ];
        }

        $months  = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $monthly = Sale::select(DB::raw('MONTH(date) as month'), DB::raw('SUM(quantity) as total_qty'))
            ->whereYear('date', $this->selectedChartYear)
            ->groupBy(DB::raw('MONTH(date)'))
            ->get()
            ->keyBy('month');

        $data = [];
        foreach ($months as $m) {
            $data[] = (int) ($monthly[$m]->total_qty ?? 0);
        }

        return [
            'mode'     => 'bulan',
            'labels'   => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'datasets' => [
                ['label' => (string) $this->selectedChartYear, 'data' => $data, 'color' => '#06b6d4'],
            ],
        ];
    }

    private function getSideChartData(): array
    {
        $dailyProfitQuery = $this->applyPeriodFilter(Sale::select('date', DB::raw('SUM(profit) as total_profit')));
        $dailyProfits     = $dailyProfitQuery->groupBy('date')->orderBy('date', 'asc')->get();

        $productProfitQuery = $this->applyPeriodFilter(Sale::select('product_name', DB::raw('SUM(profit) as total_profit')));
        $productProfits     = $productProfitQuery->groupBy('product_name')->orderByDesc('total_profit')->take(3)->get();

        return [
            'barLabels' => $dailyProfits->map(fn ($s) => Carbon::parse($s->date)->format('d M'))->toArray(),
            'barData'   => $dailyProfits->map(fn ($s) => (int) $s->total_profit)->toArray(),
            'pieLabels' => $productProfits->map(fn ($s) => $s->product_name)->toArray(),
            'pieData'   => $productProfits->map(fn ($s) => (int) $s->total_profit)->toArray(),
        ];
    }

    public function render()
    {
        $filteredQuery = $this->applyPeriodFilter(Sale::query());
        $salesData     = $filteredQuery->orderBy('date', 'desc')->get();

        $totalSales       = $salesData->sum(fn ($s) => $s->sell_price * $s->quantity);
        $totalProfit      = $salesData->sum('profit');
        $transactionCount = $salesData->count();
        $averageMargin    = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;

        $yearsRange   = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
        $yearlyTotals = [];
        foreach ($yearsRange as $yr) {
            $yearlyTotals[$yr] = (int) Sale::whereYear('date', $yr)->sum('quantity');
        }

        return view('livewire.profit-report', array_merge(
            $this->getVolumeChartData(),
            $this->getSideChartData(),
            [
                'totalSales'       => $totalSales,
                'totalProfit'      => $totalProfit,
                'transactionCount' => $transactionCount,
                'averageMargin'    => $averageMargin,
                'yearlyTotals'     => $yearlyTotals,
                'yearsRange'       => $yearsRange,
                'salesData'        => $salesData,
            ]
        ));
    }
}