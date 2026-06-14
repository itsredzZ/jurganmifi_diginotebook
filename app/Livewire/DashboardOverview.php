<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render()
    {
        $inventory = Product::all();
        $today     = now()->toDateString();

        $todaySales   = Sale::whereDate('date', $today)->get();
        $todayRevenue = $todaySales->sum(fn ($s) => $s->sell_price * $s->quantity);
        $todayProfit  = $todaySales->sum('profit');
        $todayMargin  = $todayRevenue > 0 ? round(($todayProfit / $todayRevenue) * 100) : 0;

        $totalInventoryValue = $inventory->sum(
            fn ($item) => $item->purchase_price * ($item->stock_rumah + $item->stock_toko)
        );
        $totalUnits    = $inventory->sum(fn ($item) => $item->stock_rumah + $item->stock_toko);
        $totalProducts = $inventory->count();

        $outOfStockItems = $inventory->filter(fn ($item) => $item->total_stock === 0);
        $lowStockItems   = $inventory->filter(fn ($item) => $item->total_stock > 0 && $item->total_stock < 3);

        $recentSales = Sale::orderByDesc('date')->orderByDesc('id')->take(5)->get();

        return view('livewire.dashboard-overview', compact(
            'inventory', 'totalInventoryValue', 'totalUnits', 'totalProducts',
            'todaySales', 'todayRevenue', 'todayProfit', 'todayMargin',
            'outOfStockItems', 'lowStockItems', 'recentSales'
        ));
    }
}