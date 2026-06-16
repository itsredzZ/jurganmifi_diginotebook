<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithFileUploads; // Diperlukan untuk handle upload file di Livewire
use Maatwebsite\Excel\Facades\Excel;

class SalesRecorder extends Component
{
    use WithFileUploads;

    // Properti Form Input Manual
    public string $platform = 'Tokopedia'; 
    public string $search = '';
    public string $productId = '';
    public string $quantity = '1';
    public string $date = '';

    // Properti Modal & Upload Excel
    public bool $showImportModal = false; // Mengontrol visibility modal popup
    public string $importPlatform = 'Tokopedia'; // Menyimpan pill marketplace terpilih di modal
    public $excelFile; // Menampung file spreadsheet mentah

    // Properti Live Preview Transaksi Manual
    public ?Product $selectedProduct = null;
    public ?int $previewRevenue = null;
    public ?int $previewProfit = null;
    public int $previewMargin = 0; // Menyimpan persentase margin keuntungan

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    // ── AKSI KONTROL MODAL POPUP ───────────────────────────────────────────────
    public function openImportModal(): void
    {
        $this->resetValidation('excelFile');
        $this->excelFile = null;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
    }

    // ── LOGIKA PRATINJAU / LIVE PREVIEW MANUAL ──────────────────────────────────
    public function selectProduct($id): void
    {
        $this->productId = $id;
        $this->computePreview();
    }

    public function updatedQuantity(): void
    {
        $this->computePreview();
    }

    private function computePreview(): void
    {
        if ($this->productId && is_numeric($this->quantity) && (int) $this->quantity > 0) {
            $this->selectedProduct = Product::find($this->productId);
            if ($this->selectedProduct) {
                $qty = (int) $this->quantity;
                $this->previewRevenue = $this->selectedProduct->sell_price * $qty;
                $this->previewProfit = ($this->selectedProduct->sell_price - $this->selectedProduct->purchase_price) * $qty;
                
                // Hitung rasio margin keuntungan
                if ($this->previewRevenue > 0) {
                    $this->previewMargin = (int) round(($this->previewProfit / $this->previewRevenue) * 100);
                }
                return;
            }
        }
        $this->selectedProduct = null;
        $this->previewRevenue = $this->previewProfit = null;
        $this->previewMargin = 0;
    }

    // ── SIMPAN PENJUALAN MANUAL ──────────────────────────────────────────────────
    public function recordSale(): void
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'quantity'  => 'required|integer|min:1',
            'date'      => 'required|date',
            'platform'  => 'required|string|max:100',
        ], [
            'productId.required' => 'Silahkan pilih produk dari daftar terlebih dahulu.',
        ]);

        $product = Product::findOrFail($this->productId);
        $qty     = (int) $this->quantity;

        if ($product->total_stock < $qty) {
            $this->addError('quantity', "Stok tidak cukup! Sisa total stok: {$product->total_stock} unit.");
            return;
        }

        // Pengurangan stok cerdas: Kurangi toko dulu, sisanya dari rumah
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
            'platform'       => $this->platform,
        ]);

        // Reset form input manual
        $this->productId       = '';
        $this->search          = '';
        $this->quantity        = '1';
        $this->selectedProduct = null;
        $this->previewRevenue  = $this->previewProfit = null;
        $this->previewMargin   = 0;

        session()->flash('success', 'Penjualan manual berhasil dicatat.');
    }

    // ── PROSES PARSING & IMPORT EXCEL MARKETPLACE ──────────────────────────────
    public function importExcel(): void
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excelFile.required' => 'Pilih atau letakkan file laporan Excel Anda.',
            'excelFile.mimes'    => 'Format file wajib berupa .xlsx, .xls, atau .csv',
        ]);

        // Baca file spreadsheet menjadi format array
        $dataSheets = Excel::toArray([], $this->excelFile->getRealPath());
        $rows = $dataSheets[0] ?? [];

        if (count($rows) <= 1) {
            $this->addError('excelFile', 'File Excel kosong atau tidak memiliki baris data.');
            return;
        }

        // Ambil baris pertama sebagai susunan nama kolom (Header)
        $header = array_map('strtolower', $rows[0]);
        $colNamaProduk = $colQty = $colTanggal = null;

        // Auto-detect kolom berdasarkan kemiripan istilah marketplace umum
        foreach ($header as $index => $colName) {
            if (str_contains($colName, 'nama produk') || str_contains($colName, 'product name') || str_contains($colName, 'nama barang')) {
                $colNamaProduk = $index;
            }
            if (str_contains($colName, 'jumlah') || str_contains($colName, 'qty') || str_contains($colName, 'quantity') || str_contains($colName, 'jumlah terjual')) {
                $colQty = $index;
            }
            if (str_contains($colName, 'tanggal') || str_contains($colName, 'date') || str_contains($colName, 'waktu pesanan')) {
                $colTanggal = $index;
            }
        }

        if ($colNamaProduk === null || $colQty === null) {
            $this->addError('excelFile', 'Gagal mengenali struktur kolom. Pastikan file Excel memuat header seperti "Nama Produk" dan "Qty".');
            return;
        }

        $importedCount = 0;

        // Iterasi data mulai dari baris ke-2
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[$colNamaProduk])) continue;

            $excelProductName = trim($row[$colNamaProduk]);
            $qty = (int) $row[$colQty];

            // Tentukan tanggal record
            $saleDate = now()->toDateString();
            if ($colTanggal !== null && !empty($row[$colTanggal])) {
                $parsedDate = date('Y-m-d', strtotime(str_replace('/', '-', $row[$colTanggal])));
                if ($parsedDate && $parsedDate !== '1970-01-01') {
                    $saleDate = $parsedDate;
                }
            }

            if ($qty <= 0) continue;

            // Pencarian produk berbasis kemiripan nama
            $product = Product::where('name', 'like', '%' . $excelProductName . '%')->first();

            if ($product) {
                $actualQty = min($qty, $product->total_stock);

                if ($actualQty > 0) {
                    $fromToko  = min($actualQty, $product->stock_toko);
                    $fromRumah = $actualQty - $fromToko;

                    if ($fromToko > 0)  $product->decrement('stock_toko',  $fromToko);
                    if ($fromRumah > 0) $product->decrement('stock_rumah', $fromRumah);

                    Sale::create([
                        'product_id'     => $product->id,
                        'product_name'   => $product->name,
                        'quantity'       => $actualQty,
                        'purchase_price' => $product->purchase_price,
                        'sell_price'     => $product->sell_price,
                        'profit'         => ($product->sell_price - $product->purchase_price) * $actualQty,
                        'date'           => $saleDate,
                        'platform'       => $this->importPlatform,
                    ]);
                    $importedCount++;
                }
            }
        }

        $this->showImportModal = false;
        $this->excelFile = null;

        session()->flash('success', "Berhasil mengimpor {$importedCount} baris transaksi dari laporan marketplace.");
    }

    public function render()
    {
        $products = Product::when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->get();

        $recentSales = Sale::orderByDesc('date')
            ->orderByDesc('id')
            ->take(3) // Batasi hanya 3 baris riwayat terakhir
            ->get();

        return view('livewire.sales-recorder', compact('products', 'recentSales'));
    }
}