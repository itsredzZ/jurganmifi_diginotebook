<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitReport extends Component
{
    use WithPagination; // Aktifkan paginasi

    public string $period = 'Semua';
    public string $startDate = '';
    public string $endDate = '';

    // Multi-select untuk chart bulanan (defaultnya 2025 & 2026)
    public array $selectedChartYears = [2025, 2026]; 
    public string $volumeChartMode = 'tahun';

    // Filter untuk tabel detail
    public string $tableSearch = '';
    public string $tablePlatform = '';

    protected $updatesQueryString = ['page']; // Agar paginasi berjalan baik di Livewire

    public function mount(): void
    {
        // Default chart mode ke 'bulan' agar fitur multi-select langsung kelihatan
        $this->volumeChartMode = 'bulan';
    }

    public function updatedPeriod(): void
    {
        $this->dispatch('refresh-side-charts', data: $this->getSideChartData());
    }

    public function updatedTableSearch(): void
    {
        $this->resetPage(); // Reset ke halaman 1 saat search
    }

    public function updatedTablePlatform(): void
    {
        $this->resetPage();
    }

    // Fungsi baru untuk menambah/menghapus tahun dari array multi-select
    public function toggleChartYear(int $year): void
    {
        if (in_array($year, $this->selectedChartYears)) {
            $this->selectedChartYears = array_diff($this->selectedChartYears, [$year]);
        } else {
            $this->selectedChartYears[] = $year;
        }
        
        // Reset array keys agar JS chart bisa baca dengan baik
        $this->selectedChartYears = array_values($this->selectedChartYears);
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
            $years  = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027];
            $totals = [];
            
            foreach ($years as $yr) {
                // Langsung jumlahkan semua quantity di tabel SalesVolume berdasarkan tahun
                // Ini akan otomatis menjumlahkan 12 bulan (untuk 2021-2027) 
                // atau langsung ambil nilai tunggal (untuk 2018-2020)
                $volumeTotal = \App\Models\SalesVolume::where('year', $yr)->sum('quantity');
                
                if ($volumeTotal > 0) {
                    $totals[] = (int) $volumeTotal;
                } else {
                    // Kalau kosong, baru fallback ke sum dari tabel Sale aktual
                    $totals[] = (int) Sale::whereYear('date', $yr)->sum('quantity');
                }
            }

            return [
                'mode'     => 'tahun',
                'labels'   => $years,
                'datasets' => [
                    ['label' => 'Total Unit per Tahun', 'data' => $totals, 'color' => '#06b6d4', 'is_forecast' => false],
                ],
            ];
        }

        // MODE BULAN DENGAN MULTI-SELECT
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#6366f1'];
        $datasets = [];

        foreach ($this->selectedChartYears as $index => $year) {
            $color = $colors[$index % count($colors)];
            
            // 1. Cek apakah ada data bulanan di SalesVolume (biasanya untuk data forecast 2026-2027 atau data lama jika ada)
            $volumeData = \App\Models\SalesVolume::where('year', $year)
                ->whereNotNull('month')
                ->get()
                ->keyBy('month');

            $actualData = array_fill(0, 11, null);
            $forecastData = array_fill(0, 11, null);
            $hasForecast = false;

            // 2. Ambil data dari tabel Sale (transaksi nyata)
            $saleData = Sale::select(
                    DB::raw('MONTH(date) as month'),
                    DB::raw('SUM(quantity) as total_qty')
                )
                ->whereYear('date', $year)
                ->groupBy(DB::raw('MONTH(date)'))
                ->get()
                ->keyBy('month');

            for ($m = 1; $m <= 12; $m++) {
                // Prioritaskan data dari SalesVolume (karena di situ ada data 2018-2020 & Forecast)
                if (isset($volumeData[$m])) {
                    $qty = $volumeData[$m]->quantity;
                    $isFc = $volumeData[$m]->is_forecast;
                } else if (isset($saleData[$m])) {
                    $qty = (int) $saleData[$m]->total_qty;
                    $isFc = false; // Data transaksi nyata pasti bukan forecast
                } else {
                    $qty = 0;
                    $isFc = false;
                }

                if ($isFc) {
                    $hasForecast = true;
                    $forecastData[$m - 1] = $qty;
                    if ($m > 1 && $actualData[$m - 2] !== null) {
                        $forecastData[$m - 2] = $actualData[$m - 2];
                    }
                } else {
                    $actualData[$m - 1] = $qty > 0 ? $qty : null;
                }
            }

            $datasets[] = [
                'label' => (string) $year . ' (Aktual)',
                'data' => $actualData,
                'color' => $color,
                'is_forecast' => false
            ];

            if ($hasForecast) {
                $datasets[] = [
                    'label' => (string) $year . ' (Prediksi)',
                    'data' => $forecastData,
                    'color' => $color,
                    'is_forecast' => true
                ];
            }
        }

        return [
            'mode'     => 'bulan',
            'labels'   => $labels,
            'datasets' => $datasets,
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
        
        // Filter Tabel Detail
        if ($this->tableSearch) {
            $filteredQuery->where('product_name', 'like', '%' . $this->tableSearch . '%');
        }
        if ($this->tablePlatform && $this->tablePlatform !== 'Semua') {
            $filteredQuery->where('platform', $this->tablePlatform);
        }

        $salesData = $filteredQuery->orderBy('date', 'desc')->paginate(15); // Paginasi 15 baris per halaman

        $allSalesForStats = $this->applyPeriodFilter(Sale::query())->get();
        $totalSales       = $allSalesForStats->sum(fn ($s) => $s->sell_price * $s->quantity);
        $totalProfit      = $allSalesForStats->sum('profit');
        $transactionCount = $allSalesForStats->count();
        $averageMargin    = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;

        $yearsRange   = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027];
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