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
    
    // Properti filter tahun aktif untuk grafik utama
    public int $selectedChartYear = 2026;

    public function mount(): void
    {
        $this->selectedChartYear = (int) now()->year;
    }

    // Listener interaktif saat filter periode atau tahun grafik diubah oleh user
    public function updatedPeriod(): void
    {
        $this->dispatch('refresh-charts', data: $this->getChartData());
    }

    public function updatedSelectedChartYear(): void
    {
        $this->dispatch('refresh-charts', data: $this->getChartData());
    }

    public function applyCustomDate(): void
    {
        $this->period = 'Custom';
        $this->dispatch('refresh-charts', data: $this->getChartData());
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
                return $query; // Semua
        }
    }

    private function getChartData(): array
    {
        // 1. Data Grafik Utama: Riwayat Volume Penjualan (Per Bulan untuk tahun 2024, 2025, 2026)
        $months = [1,2,3,4,5,6,7,8,9,10,11,12];
        $lineData = [2024 => [], 2025 => [], 2026 => []];
        
        foreach ([2024, 2025, 2026] as $yr) {
            $monthlyQty = Sale::select(DB::raw('MONTH(date) as month'), DB::raw('SUM(quantity) as total_qty'))
                ->whereYear('date', $yr)
                ->groupBy(DB::raw('MONTH(date)'))
                ->get()
                ->keyBy('month');
                
            foreach ($months as $m) {
                $lineData[$yr][] = (int) ($monthlyQty[$m]->total_qty ?? 0);
            }
        }

        // 2. Data Grafik Bawah Kiri: Keuntungan Harian (Berdasarkan periode filter)
        $dailyProfitQuery = Sale::select('date', DB::raw('SUM(profit) as total_profit'));
        $dailyProfitQuery = $this->applyPeriodFilter($dailyProfitQuery);
        $dailyProfits = $dailyProfitQuery->groupBy('date')->orderBy('date', 'asc')->get();
        
        $barLabels = $dailyProfits->map(fn($s) => Carbon::parse($s->date)->format('d Jun'))->toArray();
        $barData = $dailyProfits->map(fn($s) => (int)$s->total_profit)->toArray();

        // 3. Data Grafik Bawah Kanan: Keuntungan per Produk (Pie)
        $productProfitQuery = Sale::select('product_name', DB::raw('SUM(profit) as total_profit'));
        $productProfitQuery = $this->applyPeriodFilter($productProfitQuery);
        $productProfits = $productProfitQuery->groupBy('product_name')->orderByDesc('total_profit')->take(3)->get();
        
        $pieLabels = $productProfits->map(fn($s) => $s->product_name)->toArray();
        $pieData = $productProfits->map(fn($s) => (int)$s->total_profit)->toArray();

        return [
            'line2024' => $lineData[2024],
            'line2025' => $lineData[2025],
            'line2026' => $lineData[2026],
            'barLabels' => $barLabels,
            'barData' => $barData,
            'pieLabels' => $pieLabels,
            'pieData' => $pieData,
        ];
    }

    public function render()
    {
        // Kueri dasar yang terfilter untuk Statistik Ringkasan & Tabel Detail
        $filteredQuery = Sale::query();
        $filteredQuery = $this->applyPeriodFilter($filteredQuery);
        $salesData = $filteredQuery->orderBy('date', 'desc')->get();

        // Hitung nilai Stat Cards utama
        $totalSales = $salesData->sum(fn($s) => $s->sell_price * $s->quantity);
        $totalProfit = $salesData->sum('profit');
        $transactionCount = $salesData->count();
        $averageMargin = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;

        // Tabel Rekap Unit Tahunan (Bawah Grafik Garis)
        $yearsRange = [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
        $yearlyTotals = [];
        foreach ($yearsRange as $yr) {
            $yearlyTotals[$yr] = (int) Sale::whereYear('date', $yr)->sum('quantity');
        }

        // Ambil inisialisasi data grafik awal untuk dilempar ke blade view
        $initialChartData = $this->getChartData();

        return view('livewire.profit-report', array_merge($initialChartData, [
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'transactionCount' => $transactionCount,
            'averageMargin' => $averageMargin,
            'yearlyTotals' => $yearlyTotals,
            'yearsRange' => $yearsRange,
            'salesData' => $salesData
        ]));
    }
}