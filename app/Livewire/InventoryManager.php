<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class InventoryManager extends Component
{
    // UI state
    public bool   $showForm    = false;
    public string $formMode    = 'new'; // 'new' | 'restock'
    public string $filterCategory = 'all';

    // New product fields
    public string $name          = '';
    public string $category      = 'mifi';
    public string $purchasePrice = '';
    public string $sellPrice     = '';
    public string $stockRumah    = '';
    public string $stockToko     = '';

    // Restock fields
    public string $restockId    = '';
    public string $restockRumah = '';
    public string $restockToko  = '';

    // Inline edit
    public ?int   $editingId        = null;
    public string $editName         = '';
    public string $editCategory     = 'mifi';
    public string $editPurchasePrice = '';
    public string $editSellPrice    = '';
    public string $editStockRumah   = '';
    public string $editStockToko    = '';

    // Delete confirm
    public ?int $deletingId = null;

    // ── Form visibility ─────────────────────────────────────────────────────────

    public function openNewForm(): void
    {
        $this->resetNewForm();
        $this->formMode = 'new';
        $this->showForm = true;
        $this->editingId = null;
        $this->deletingId = null;
    }

    public function openRestockForm(): void
    {
        $this->resetRestockForm();
        $this->formMode = 'restock';
        $this->showForm = true;
        $this->editingId = null;
        $this->deletingId = null;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetNewForm();
        $this->resetRestockForm();
    }

    // ── Add product ─────────────────────────────────────────────────────────────

    public function addProduct(): void
    {
        $this->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|in:mifi,router,battery,simcard,accessory',
            'purchasePrice' => 'required|numeric|min:0',
            'sellPrice'     => 'required|numeric|min:0',
            'stockRumah'    => 'required|integer|min:0',
            'stockToko'     => 'required|integer|min:0',
        ], [], [
            'name'          => 'Nama Produk',
            'category'      => 'Kategori',
            'purchasePrice' => 'Harga Beli',
            'sellPrice'     => 'Harga Jual',
            'stockRumah'    => 'Stok Rumah',
            'stockToko'     => 'Stok Toko',
        ]);

        Product::create([
            'name'           => $this->name,
            'category'       => $this->category,
            'purchase_price' => (int) $this->purchasePrice,
            'sell_price'     => (int) $this->sellPrice,
            'stock_rumah'    => (int) $this->stockRumah,
            'stock_toko'     => (int) $this->stockToko,
            'date_added'     => now()->toDateString(),
        ]);

        $this->closeForm();
        session()->flash('success', 'Produk berhasil ditambahkan.');
    }

    // ── Restock ─────────────────────────────────────────────────────────────────

    public function restock(): void
    {
        $this->validate([
            'restockId'    => 'required|exists:products,id',
            'restockRumah' => 'nullable|integer|min:0',
            'restockToko'  => 'nullable|integer|min:0',
        ], [], [
            'restockId' => 'Produk',
        ]);

        $addRumah = (int) $this->restockRumah ?: 0;
        $addToko  = (int) $this->restockToko  ?: 0;

        if ($addRumah === 0 && $addToko === 0) {
            $this->addError('restockRumah', 'Masukkan jumlah restock minimal 1.');
            return;
        }

        $product = Product::findOrFail($this->restockId);
        $product->increment('stock_rumah', $addRumah);
        $product->increment('stock_toko', $addToko);

        $this->closeForm();
        session()->flash('success', "Stok {$product->name} berhasil diperbarui.");
    }

    // ── Inline edit ──────────────────────────────────────────────────────────────

    public function startEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId         = $id;
        $this->editName          = $product->name;
        $this->editCategory      = $product->category;
        $this->editPurchasePrice = (string) $product->purchase_price;
        $this->editSellPrice     = (string) $product->sell_price;
        $this->editStockRumah    = (string) $product->stock_rumah;
        $this->editStockToko     = (string) $product->stock_toko;
        $this->deletingId        = null;
        $this->showForm          = false;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName'          => 'required|string|max:255',
            'editCategory'      => 'required|in:mifi,router,battery,simcard,accessory',
            'editPurchasePrice' => 'required|numeric|min:0',
            'editSellPrice'     => 'required|numeric|min:0',
            'editStockRumah'    => 'required|integer|min:0',
            'editStockToko'     => 'required|integer|min:0',
        ]);

        Product::findOrFail($this->editingId)->update([
            'name'           => $this->editName,
            'category'       => $this->editCategory,
            'purchase_price' => (int) $this->editPurchasePrice,
            'sell_price'     => (int) $this->editSellPrice,
            'stock_rumah'    => (int) $this->editStockRumah,
            'stock_toko'     => (int) $this->editStockToko,
        ]);

        $this->editingId = null;
        session()->flash('success', 'Produk berhasil diperbarui.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    // ── Delete ───────────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->editingId  = null;
    }

    public function deleteProduct(): void
    {
        if ($this->deletingId) {
            $product = Product::findOrFail($this->deletingId);
            $name    = $product->name;
            $product->delete();
            $this->deletingId = null;
            session()->flash('success', "{$name} berhasil dihapus.");
        }
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function resetNewForm(): void
    {
        $this->name = $this->category = $this->purchasePrice =
        $this->sellPrice = $this->stockRumah = $this->stockToko = '';
        $this->category = 'mifi';
        $this->resetValidation();
    }

    private function resetRestockForm(): void
    {
        $this->restockId = $this->restockRumah = $this->restockToko = '';
        $this->resetValidation();
    }

    // ── Render ───────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = Product::query();
        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }
        $products    = $query->orderBy('category')->orderBy('name')->get();
        $allProducts = Product::orderBy('name')->get();

        $categoryCounts = Product::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('livewire.inventory-manager', compact('products', 'allProducts', 'categoryCounts'));
    }
}