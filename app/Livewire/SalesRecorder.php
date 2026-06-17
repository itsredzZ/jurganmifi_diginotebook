<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;

class SalesRecorder extends Component
{
    public string $productId   = '';
    public string $quantity    = '1';
    public string $platform    = '';
    public string $date        = '';
    public string $filterMonth = '';

    public function mount(): void
    {
        $this->date        = now()->toDateString();
        $this->filterMonth = now()->format('Y-m');
    }

    public function recordSale(): void
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'quantity'  => 'required|integer|min:1',
            'date'      => 'required|date',
            'platform'  => 'nullable|string|max:100',
        ], [], [
            'productId' => 'Produk',
            'quantity'  => 'Jumlah',
            'date'      => 'Tanggal',
        ]);

        $product = Product::findOrFail($this->productId);
        $qty     = (int) $this->quantity;

        // Validasi stok cukup — sebelumnya tidak dicek, bisa bikin stok negatif
        if ($qty > $product->total_stock) {
            $this->addError('quantity', "Stok tidak cukup. Tersedia: {$product->total_stock}");
            return;
        }

        $fromToko  = min($qty, $product->stock_toko);
        $fromRumah = $qty - $fromToko;

        if ($fromToko > 0)  $product->decrement('stock_toko', $fromToko);
        if ($fromRumah > 0) $product->decrement('stock_rumah', $fromRumah);

        Sale::create([
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'quantity'       => $qty,
            'purchase_price' => $product->purchase_price,
            'sell_price'     => $product->sell_price,
            'profit'         => ($product->sell_price - $product->purchase_price) * $qty,
            'date'           => $this->date,
            'platform'       => $this->platform ?: null,
        ]);

        $this->reset(['productId', 'platform']);
        $this->quantity = '1';

        session()->flash('success', 'Penjualan berhasil dicatat.');
    }

    public function deleteSale(int $id): void
    {
        $sale = Sale::find($id);
        if ($sale) {
            if ($product = Product::find($sale->product_id)) {
                $product->increment('stock_rumah', $sale->quantity);
            }
            $sale->delete();
            session()->flash('success', 'Penjualan dihapus & stok dikembalikan.');
        }
    }

    public function render()
    {
        $products = Product::orderBy('name')->get();

        [$year, $month] = explode('-', $this->filterMonth . '-01');
        $sales = Sale::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $monthRevenue = $sales->sum(fn ($s) => $s->sell_price * $s->quantity);
        $monthProfit  = $sales->sum('profit');

        return view('livewire.sales-recorder', compact(
            'products', 'sales', 'monthRevenue', 'monthProfit'
        ));
    }
}