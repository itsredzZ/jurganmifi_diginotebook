<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;

class ProfitReport extends Component
{
    public string $filterYear = '';

    public function mount(): void
    {
        $this->filterYear = (string) now()->year;
    }

    public function updatedFilterYear(): void
    {
        $data = $this->buildChartData((int) $this->filterYear);
        $this->dispatch('chart-updated',
            labels:  $data['chartLabels'],
            revenue: $data['chartRevenue'],
            profit:  $data['chartProfit'],
        );
    }

    private function buildChartData(int $year): array
    {
        $chartLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartRevenue = [];
        $chartProfit  = [];

        for ($m = 1; $m <= 12; $m++) {
            $sales          = Sale::whereYear('date', $year)->whereMonth('date', $m)->get();
            $chartRevenue[] = $sales->sum(fn ($s) => $s->sell_price * $s->quantity);
            $chartProfit[]  = $sales->sum('profit');
        }

        return compact('chartLabels', 'chartRevenue', 'chartProfit');
    }

    public function render()
    {
        $year = (int) $this->filterYear;

        $chartData = $this->buildChartData($year);

        // Monthly table
        $monthNames  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $sales = Sale::whereYear('date', $year)->whereMonth('date', $m)->get();
            $rev   = $sales->sum(fn ($s) => $s->sell_price * $s->quantity);
            $prof  = $sales->sum('profit');
            $monthlyData[$m] = [
                'label'        => $monthNames[$m - 1],
                'revenue'      => $rev,
                'profit'       => $prof,
                'transactions' => $sales->count(),
                'margin'       => $rev > 0 ? round(($prof / $rev) * 100, 1) : 0,
            ];
        }

        // Year totals
        $yearlySales      = Sale::whereYear('date', $year)->get();
        $yearRevenue      = $yearlySales->sum(fn ($s) => $s->sell_price * $s->quantity);
        $yearProfit       = $yearlySales->sum('profit');
        $yearTransactions = $yearlySales->count();
        $yearMargin       = $yearRevenue > 0 ? round(($yearProfit / $yearRevenue) * 100, 1) : 0;

        // Platform breakdown
        $platformData = $yearlySales
            ->groupBy(fn ($s) => $s->platform ?? 'Lainnya')
            ->map(fn ($group, $platform) => [
                'platform'     => $platform,
                'transactions' => $group->count(),
                'revenue'      => $group->sum(fn ($s) => $s->sell_price * $s->quantity),
                'profit'       => $group->sum('profit'),
            ])
            ->sortByDesc('revenue')
            ->values();

        // Year selector
        $availableYears = Sale::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return view('livewire.profit-report', array_merge($chartData, compact(
            'monthlyData', 'yearRevenue', 'yearProfit', 'yearTransactions',
            'yearMargin', 'platformData', 'availableYears'
        )));
    }
}