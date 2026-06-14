<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;

class SalesRecorder extends Component
{
    // Form fields
    public string $productId = '';
    public string $quantity  = '1';
    public string $platform  = '';
    public string $date      = '';

    // Live preview
    public ?int $previewRevenue = null;
    public ?int $previewProfit  = null;

    // Sales history filter
    public string $filterMonth = '';

    // Delete confirm
    public ?int $deletingId = null;

    public function mount(): void
    {
        $this->date        = now()->toDateString();
        $this->filterMonth = now()->format('Y-m');
    }

    // ── Reactive preview ─────────────────────────────────────────────────────────

    public function updatedProductId(): void  { $this->computePreview(); }
    public function updatedQuantity(): void   { $this->computePreview(); }

    private function computePreview(): void
    {
        if ($this->productId && is_numeric($this->quantity) && (int) $this->quantity > 0) {
            $product = Product::find($this->productId);
            if ($product) {
                $qty = (int) $this->quantity;
                $this->previewRevenue = $product->sell_price * $qty;
                $this->previewProfit  = ($product->sell_price - $product->purchase_price) * $qty;
                return;
            }
        }
        $this->previewRevenue = $this->previewProfit = null;
    }

    // ── Record sale ──────────────────────────────────────────────────────────────

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

        // Deduct stock: toko first, then rumah
        $fromToko  = min($qty, $product->stock_toko);
        $fromRumah = $qty - $fromToko;

        if ($fromToko > 0)  $product->decrement('stock_toko',  $fromToko);
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

        $this->productId      = '';
        $this->quantity       = '1';
        $this->platform       = '';
        $this->previewRevenue = $this->previewProfit = null;

        session()->flash('success', 'Penjualan berhasil dicatat.');
    }

    // ── Delete sale ──────────────────────────────────────────────────────────────

    public function confirmDeleteSale(int $id): void
    {
        $this->deletingId = $id;
    }

    public function deleteSale(): void
    {
        if (! $this->deletingId) return;

        $sale = Sale::find($this->deletingId);
        if ($sale) {
            // Restore stock to rumah
            if ($product = Product::find($sale->product_id)) {
                $product->increment('stock_rumah', $sale->quantity);
            }
            $sale->delete();
        }

        $this->deletingId = null;
        session()->flash('success', 'Penjualan dihapus & stok dikembalikan.');
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    // ── Render ───────────────────────────────────────────────────────────────────

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