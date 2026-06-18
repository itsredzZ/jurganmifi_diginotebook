<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
    public string $importStep = 'upload'; // 'upload' -> 'preview' -> (commit lalu modal ditutup)
    public string $importPlatform = 'Tokopedia'; // Menyimpan pill marketplace terpilih di modal
    public $excelFile; // Menampung file spreadsheet/csv mentah

    // Properti Hasil Preview (ditampilkan sebelum benar-benar commit ke DB)
    public ?string $pendingImportId = null; // kunci cache yang menyimpan baris siap-commit
    public int $previewMatchedCount = 0;
    public int $previewPartialStockCount = 0;
    public int $previewSkippedStatus = 0;
    public int $previewSkippedNoStock = 0;
    public array $previewUnmatched = []; // [nama_produk_excel => total_qty_gagal]
    public int $previewTotalRows = 0;
    public bool $isHistoricalImport = false; 

    // Properti Live Preview Transaksi Manual
    public ?Product $selectedProduct = null;
    public ?int $previewRevenue = null;
    public ?int $previewProfit = null;
    public int $previewMargin = 0; // Menyimpan persentase margin keuntungan

    // ── KAMUS KATA KUNCI UNTUK AUTO-DETECT KOLOM ────────────────────────────────
    // Laporan marketplace (Tokopedia, Shopee, Lazada, TikTok Shop, dll) biasanya
    // memiliki 50-60+ kolom (alamat pembeli, ongkir, tracking, dll) yang tidak
    // relevan. Kita hanya mencari 4 kolom ini; sisanya otomatis diabaikan.
    // Urutan keyword = urutan prioritas, kandidat pertama yang cocok akan dipakai.
    private const PRODUCT_NAME_KEYWORDS = [
        'nama produk', 'product name', 'nama barang', 'item name', 'produk',
    ];

    private const QUANTITY_KEYWORDS = [
        'jumlah terjual', 'quantity ordered', 'jumlah', 'qty', 'quantity',
    ];

    private const DATE_KEYWORDS = [
        'waktu pesanan dibuat', 'tanggal pesanan dibuat', 'created time',
        'order time', 'paid time', 'tanggal pesanan', 'order date', 'tanggal', 'date',
    ];

    private const STATUS_KEYWORDS = [
        'order status', 'status pesanan', 'status',
    ];

    // Nilai status yang dianggap "transaksi selesai" lintas platform.
    private const COMPLETED_STATUS_VALUES = [
        'completed', 'selesai', 'done', 'delivered', 'finished',
    ];

    // Minimal persentase kata kunci nama produk database yang harus ditemukan
    // di dalam judul produk Excel agar dianggap "cocok". Nama produk di
    // database biasanya singkat ("Modem WiFi E5586 Orbit"), sedangkan judul
    // di marketplace panjang dan penuh kata promosi — jadi kita cek apakah
    // SEBAGIAN BESAR kata kunci nama produk DB muncul di judul tersebut,
    // bukan mencari substring penuh secara langsung.
    private const PRODUCT_MATCH_THRESHOLD = 0.7;

    // Berapa lama hasil preview disimpan di cache sebelum dianggap basi.
    private const PREVIEW_CACHE_MINUTES = 20;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    // ── AKSI KONTROL MODAL POPUP ───────────────────────────────────────────────
    public function openImportModal(): void
    {
        $this->resetValidation('excelFile');
        $this->excelFile = null;
        $this->resetPreviewState();
        $this->importStep = 'upload';
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->resetPreviewState();
        $this->showImportModal = false;
    }

    /**
     * Batalkan hasil preview yang sedang ditampilkan dan kembali ke layar
     * upload (misalnya karena user mau pilih file lain atau mengurungkan
     * import setelah melihat banyak produk yang tidak cocok).
     */
    public function cancelPreview(): void
    {
        $this->resetPreviewState();
        $this->excelFile = null;
        $this->importStep = 'upload';
    }

    private function resetPreviewState(): void
    {
        if ($this->pendingImportId) {
            Cache::forget($this->pendingImportId);
        }
        $this->pendingImportId          = null;
        $this->previewMatchedCount      = 0;
        $this->previewPartialStockCount = 0;
        $this->previewSkippedStatus     = 0;
        $this->previewSkippedNoStock    = 0;
        $this->previewUnmatched         = [];
        $this->previewTotalRows         = 0;
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

    // ── HELPER: NORMALISASI HEADER & PENCARIAN KOLOM ────────────────────────────

    /**
     * Membersihkan teks header kolom: hapus BOM (karakter tersembunyi yang
     * sering muncul di awal file CSV hasil export marketplace), trim spasi,
     * dan ubah ke huruf kecil supaya pencocokan kata kunci tidak case-sensitive.
     */
    private function normalizeHeaderCell($value): string
    {
        $value = (string) $value;
        $value = str_replace("\xEF\xBB\xBF", '', $value); // strip raw UTF-8 BOM bytes
        $value = preg_replace('/^\x{FEFF}/u', '', $value); // strip BOM jika sudah ter-decode
        return trim(strtolower($value ?? ''));
    }

    /**
     * Mencari index kolom berdasarkan daftar kata kunci (prioritas dari atas
     * ke bawah). Kolom-kolom lain yang tidak cocok dengan kata kunci manapun
     * otomatis diabaikan — ini yang membuat sistem tahan terhadap laporan
     * marketplace yang punya puluhan kolom tidak relevan.
     */
    private function findColumn(array $header, array $keywords): ?int
    {
        foreach ($keywords as $keyword) {
            foreach ($header as $index => $colName) {
                if (str_contains($colName, $keyword)) {
                    return $index;
                }
            }
        }
        return null;
    }

    /**
     * Beberapa marketplace (terutama Tokopedia) menyisipkan karakter tab atau
     * tanda kutip di belakang nilai numerik/tanggal agar spreadsheet tidak
     * mengubahnya ke notasi ilmiah. Fungsi ini membersihkan artefak tersebut.
     */
    private function cleanCellValue($value): string
    {
        return trim(preg_replace('/[\t"]/', '', (string) ($value ?? '')));
    }

    // ── HELPER: PENCOCOKAN NAMA PRODUK (TOKEN-MATCHING) ─────────────────────────

    /**
     * Memecah teks jadi kumpulan kata kunci (huruf kecil, tanpa simbol, token
     * minimal 3 karakter supaya kata sambung seperti "di"/"ke" tidak ikut).
     */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $tokens = array_filter(explode(' ', $text), fn ($t) => mb_strlen($t) >= 3);
        return array_values(array_unique($tokens));
    }

    /**
     * Mencari produk di database yang paling cocok dengan judul produk dari
     * Excel/CSV. Nama produk di database biasanya singkat (mis. "Modem WiFi
     * E5586 Orbit"), sedangkan judul di marketplace panjang & penuh kata
     * promosi. Maka dari itu kita cek persentase kata kunci nama produk DB
     * yang ditemukan di dalam judul tersebut — bukan mencari substring penuh
     * secara langsung.
     */
    private function findMatchingProduct(string $excelProductName, Collection $products): ?Product
    {
        $titleTokens = $this->tokenize($excelProductName);
        if (empty($titleTokens)) return null;

        $bestProduct = null;
        $bestScore   = 0.0;

        foreach ($products as $product) {
            $nameTokens = $this->tokenize($product->name);
            if (empty($nameTokens)) continue;

            $matchedTokens = array_intersect($nameTokens, $titleTokens);
            $score = count($matchedTokens) / count($nameTokens);

            if ($score > $bestScore) {
                $bestScore   = $score;
                $bestProduct = $product;
            }
        }

        return $bestScore >= self::PRODUCT_MATCH_THRESHOLD ? $bestProduct : null;
    }

    /**
     * Mengubah satu sel tanggal mentah dari Excel/CSV jadi format Y-m-d.
     * Format tanggal Indonesia adalah hari/bulan/tahun — mengganti '/' dengan
     * '-' memaksa strtotime membaca sebagai d-m-Y (bukan format Amerika
     * m/d/Y) sehingga tidak salah baca tanggal.
     */
    private function parseDateCell($rawValue): string
    {
        if (empty($rawValue)) {
            return now()->toDateString();
        }
        $rawDate = $this->cleanCellValue($rawValue);
        $parsedDate = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));
        return ($parsedDate && $parsedDate !== '1970-01-01') ? $parsedDate : now()->toDateString();
    }

    // ── TAHAP 1: PARSING & PREVIEW (TIDAK MENULIS KE DATABASE) ──────────────────
    /**
     * Membaca file Excel/CSV dan menyiapkan baris-baris yang SIAP diimport,
     * tapi belum benar-benar menulis apa pun ke tabel sales atau mengubah
     * stok produk. Hasilnya disimpan sementara di cache, dan ringkasannya
     * ditampilkan ke user untuk direview sebelum mengklik "Konfirmasi Import".
     */
        // ── TAHAP 1: PARSING & PREVIEW (TIDAK MENULIS KE DATABASE) ──────────────────
    public function previewImport(): void
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excelFile.required' => 'Pilih atau letakkan file laporan Excel/CSV Anda.',
            'excelFile.mimes'    => 'Format file wajib berupa .xlsx, .xls, atau .csv',
        ]);

        $dataSheets = Excel::toArray([], $this->excelFile->getRealPath());
        $rows = $dataSheets[0] ?? [];

        if (count($rows) <= 1) {
            $this->addError('excelFile', 'File kosong atau tidak memiliki baris data.');
            return;
        }

        $header = array_map(fn ($cell) => $this->normalizeHeaderCell($cell), $rows[0]);

        $colNamaProduk = $this->findColumn($header, self::PRODUCT_NAME_KEYWORDS);
        $colQty        = $this->findColumn($header, self::QUANTITY_KEYWORDS);
        $colTanggal    = $this->findColumn($header, self::DATE_KEYWORDS);
        $colStatus     = $this->findColumn($header, self::STATUS_KEYWORDS);

        if ($colNamaProduk === null || $colQty === null) {
            $this->addError('excelFile', 'Gagal mengenali struktur kolom. Pastikan file memuat header seperti "Nama Produk"/"Product Name" dan "Qty"/"Quantity".');
            return;
        }

        $allProducts = Product::all();
        $remainingStock = $allProducts->pluck('total_stock', 'id')->toArray();

        $matchedRows      = [];
        $unmatchedNames    = [];
        $matchedCount      = 0;
        $partialStockCount = 0;
        $skippedStatus     = 0;
        $skippedNoStock    = 0;
        $totalRows         = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[$colNamaProduk])) continue;

            $totalRows++;

            if ($colStatus !== null && !empty($row[$colStatus])) {
                $statusValue = strtolower($this->cleanCellValue($row[$colStatus]));
                $isCompleted = false;
                foreach (self::COMPLETED_STATUS_VALUES as $completedValue) {
                    if (str_contains($statusValue, $completedValue)) {
                        $isCompleted = true;
                        break;
                    }
                }
                if (!$isCompleted) {
                    $skippedStatus++;
                    continue;
                }
            }

            $excelProductName = $this->cleanCellValue($row[$colNamaProduk]);
            $qty = (int) preg_replace('/[^\d\-]/', '', $this->cleanCellValue($row[$colQty] ?? '0'));
            $saleDate = $this->parseDateCell($row[$colTanggal] ?? null);

            if ($qty <= 0 || $excelProductName === '') continue;

            $product = $this->findMatchingProduct($excelProductName, $allProducts);

            if (!$product) {
                $unmatchedNames[$excelProductName] = ($unmatchedNames[$excelProductName] ?? 0) + $qty;
                continue;
            }

            // LOGIKA HISTORIS: Kalau mode riwayat aktif, lewati pengecekan stok
            if ($this->isHistoricalImport) {
                $actualQty = $qty;
            } else {
                $available = $remainingStock[$product->id] ?? 0;
                $actualQty = min($qty, $available);

                if ($actualQty <= 0) {
                    $skippedNoStock++;
                    continue;
                }

                if ($actualQty < $qty) {
                    $partialStockCount++;
                }
                $remainingStock[$product->id] = $available - $actualQty;
            }

            $matchedRows[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'quantity'     => $actualQty,
                'date'         => $saleDate,
            ];
            $matchedCount++;
        }

        // Simpan flag isHistoricalImport ke dalam cache agar ikut ke confirmImport()
        $importId = (string) Str::uuid();
        Cache::put($importId, [
            'platform'      => $this->importPlatform,
            'rows'          => $matchedRows,
            'is_historical' => $this->isHistoricalImport,
        ], now()->addMinutes(self::PREVIEW_CACHE_MINUTES));

        arsort($unmatchedNames);

        $this->pendingImportId          = $importId;
        $this->previewMatchedCount      = $matchedCount;
        $this->previewPartialStockCount = $partialStockCount;
        $this->previewSkippedStatus     = $skippedStatus;
        $this->previewSkippedNoStock    = $skippedNoStock;
        $this->previewUnmatched         = $unmatchedNames;
        $this->previewTotalRows         = $totalRows;

        $this->importStep = 'preview';
    }

    // ── TAHAP 2: KONFIRMASI — DI SINI BARU DITULIS KE DATABASE ──────────────────
    public function confirmImport(): void
    {
        if (!$this->pendingImportId || !Cache::has($this->pendingImportId)) {
            $this->addError('excelFile', 'Sesi preview sudah kedaluwarsa. Silakan upload ulang file Anda.');
            $this->importStep = 'upload';
            return;
        }

        $payload  = Cache::get($this->pendingImportId);
        $platform = $payload['platform'] ?? $this->importPlatform;
        $rows     = $payload['rows'] ?? [];
        $isHistorical = $payload['is_historical'] ?? false; // Ambil flag dari cache

        $importedCount  = 0;
        $skippedNoStock = 0;

        foreach ($rows as $row) {
            $product = Product::find($row['product_id']);
            if (!$product) continue;

            // LOGIKA HISTORIS: Kalau true, langsung buat Sale tanpa decrement stok
            if ($isHistorical) {
                $actualQty = $row['quantity']; // Ambil mentah dari preview
            } else {
                $actualQty = min($row['quantity'], $product->total_stock);
                if ($actualQty <= 0) {
                    $skippedNoStock++;
                    continue;
                }

                $fromToko  = min($actualQty, $product->stock_toko);
                $fromRumah = $actualQty - $fromToko;

                if ($fromToko > 0)  $product->decrement('stock_toko',  $fromToko);
                if ($fromRumah > 0) $product->decrement('stock_rumah', $fromRumah);
            }

            Sale::create([
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'quantity'       => $actualQty,
                'purchase_price' => $product->purchase_price,
                'sell_price'     => $product->sell_price,
                'profit'         => ($product->sell_price - $product->purchase_price) * $actualQty,
                'date'           => $row['date'],
                'platform'       => $platform,
            ]);
            $importedCount++;
        }

        Cache::forget($this->pendingImportId);

        $message = "Berhasil mengimpor {$importedCount} transaksi dari laporan marketplace.";
        if ($skippedNoStock > 0) {
            $message .= " {$skippedNoStock} baris dilewati karena stok berubah sejak preview dilakukan.";
        }
        if ($isHistorical) {
            $message = "Berhasil mengimpor {$importedCount} data riwayat penjualan ke database.";
        }
        session()->flash('success', $message);

        $this->resetPreviewState();
        $this->excelFile = null;
        $this->isHistoricalImport = false; // Reset checkbox
        $this->importStep = 'upload';
        $this->showImportModal = false;
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
            ->take(10) // Batasi hanya 10 baris riwayat terakhir
            ->get();

        return view('livewire.sales-recorder', compact('products', 'recentSales'));
    }
}