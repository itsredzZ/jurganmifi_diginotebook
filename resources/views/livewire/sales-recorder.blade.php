<div class="space-y-6">
    {{-- Header Utama --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Catat Penjualan</h2>
            <p class="text-xs text-gray-500">Input manual atau import dari Excel marketplace</p>
        </div>
        
        <div>
            <button type="button" wire:click="openImportModal"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-colors">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4-4m4 4V4"/>
                </svg>
                Import dari Excel
            </button>
        </div>
    </div>

    {{-- Alert Banner Notifikasi --}}
    @if (session('success'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Workspace Grid Utama --}}
    <div class="grid gap-6 lg:grid-cols-2 items-start">
        
        {{-- SISI KIRI: Form Input Kontrol --}}
        <div class="space-y-4">
            {{-- Platform Selektor --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-3">
                <label class="block text-sm font-semibold text-gray-900">Platform Penjualan</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Tokopedia', 'Shopee', 'TikTok Shop', 'Lazada', 'WhatsApp', 'Langsung', 'Lainnya'] as $plat)
                        <button type="button" wire:click="$set('platform', '{{ $plat }}')"
                                class="rounded-full px-4 py-1.5 text-xs font-medium transition-all
                                {{ $platform === $plat ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $plat }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- List Produk Inventori --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-3">
                <label class="block text-sm font-semibold text-gray-900">Pilih Produk</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama produk..."
                           class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                </div>

                <div class="max-h-56 overflow-y-auto rounded-lg border border-gray-100 divide-y divide-gray-50">
                    @forelse($products as $p)
                        <button type="button" wire:click="selectProduct({{ $p->id }})"
                                class="w-full text-left px-4 py-3 flex justify-between items-center transition-colors {{ $productId == $p->id ? 'bg-blue-50' : 'hover:bg-gray-50' }}">
                            <div>
                                <p class="text-sm font-medium {{ $productId == $p->id ? 'text-blue-900' : 'text-gray-900' }}">{{ $p->name }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right text-xs text-gray-500 font-mono space-y-0.5">
                                <div class="flex items-center justify-end gap-1"><span>🏠</span><span>{{ $p->stock_rumah }}</span></div>
                                <div class="flex items-center justify-end gap-1"><span>🏪</span><span>{{ $p->stock_toko }}</span></div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Input Jumlah Terjual --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Jumlah Terjual</label>
                    <input wire:model.live="quantity" type="number" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                    @error('quantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="recordSale" class="w-full rounded-lg bg-blue-500 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-600 transition-colors">
                    Catat Penjualan
                </button>
            </div>
        </div>

        {{-- SISI KANAN: Kontainer Tampilan Ringkasan & Petunjuk Impor --}}
        <div class="space-y-4">
            
            {{-- 1. UI Ringkasan Transaksi Padat --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-800 text-base">Ringkasan Transaksi</h3>
                
                @if($selectedProduct)
                    <div class="space-y-3">
                        {{-- Row: Produk --}}
                        <div class="flex justify-between items-start text-sm border-b border-gray-100 pb-2.5">
                            <span class="text-gray-400 font-medium">Produk</span>
                            <span class="font-semibold text-gray-800 text-right max-w-[240px] leading-tight">
                                {{ $selectedProduct->name }}
                            </span>
                        </div>
                        
                        {{-- Row: Platform --}}
                        <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2.5">
                            <span class="text-gray-400 font-medium">Platform</span>
                            <span class="rounded bg-green-50 px-2 py-0.5 text-xs font-bold text-green-700 tracking-wide uppercase">
                                {{ $platform }}
                            </span>
                        </div>
                        
                        {{-- Row: Stok Tersedia --}}
                        <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2.5">
                            <span class="text-gray-400 font-medium">Stok tersedia</span>
                            <span class="font-semibold text-gray-800 font-mono">
                                {{ $selectedProduct->total_stock }} unit
                            </span>
                        </div>
                        
                        {{-- Row: Jumlah Terjual --}}
                        <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2.5">
                            <span class="text-gray-400 font-medium">Jumlah terjual</span>
                            <span class="font-semibold text-gray-800 font-mono">
                                {{ $quantity }} unit
                            </span>
                        </div>
                        
                        {{-- Row: Harga Satuan --}}
                        <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2.5">
                            <span class="text-gray-400 font-medium">Harga satuan</span>
                            <span class="font-semibold text-gray-800 font-mono">
                                Rp {{ number_format($selectedProduct->sell_price, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        {{-- Row: Total Penjualan --}}
                        <div class="flex justify-between items-baseline pt-1 pb-1">
                            <span class="text-sm font-bold text-gray-800">Total Penjualan</span>
                            <span class="text-xl font-extrabold text-gray-900 font-mono tracking-tight">
                                Rp {{ number_format($previewRevenue, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        {{-- Box Hijau Laba Bersih & Margin --}}
                        <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex justify-between items-center mt-1">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-emerald-800">Keuntungan Bersih</p>
                                <p class="text-xs text-emerald-600 font-medium">Margin: {{ $previewMargin }}%</p>
                            </div>
                            <div class="text-lg font-black text-emerald-600 font-mono">
                                Rp {{ number_format($previewProfit, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @else
                    {{-- State Kosong --}}
                    <div class="py-12 text-center space-y-3 text-gray-400 flex flex-col justify-center items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 110 4 2 2 0 010-4z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-gray-400">Pilih produk dari daftar di kiri</p>
                    </div>
                @endif
            </div>

            {{-- 2. Box Panduan Import Excel (KEMBALI DI SINI) --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-5 space-y-2.5">
                <h4 class="text-sm font-bold text-blue-900">Import Excel</h4>
                <ul class="space-y-1.5 text-xs font-medium text-blue-700 list-inside">
                    <li class="flex items-start gap-1">
                        <span class="text-blue-400">•</span>
                        <span>Klik "Import dari Excel" di pojok kanan atas</span>
                    </li>
                    <li class="flex items-start gap-1">
                        <span class="text-blue-400">•</span>
                        <span>Pilih marketplace dan upload file laporan</span>
                    </li>
                    <li class="flex items-start gap-1">
                        <span class="text-blue-400">•</span>
                        <span>Sistem otomatis mendeteksi kolom & mencocokkan produk</span>
                    </li>
                    <li class="flex items-start gap-1">
                        <span class="text-blue-400">•</span>
                        <span>Review dan konfirmasi sebelum diimport</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    {{-- TABEL PENJUALAN TERBARU --}}
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
        <div>
            <h3 class="font-semibold text-gray-900">Penjualan Terbaru</h3>
            <p class="text-xs text-gray-400">3 transaksi terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Platform</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Keuntungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-xs font-mono text-gray-500">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $sale->product_name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 text-xs font-semibold
                                    {{ $sale->platform === 'Tokopedia' ? 'bg-green-50 text-green-700' : '' }}
                                    {{ $sale->platform === 'Shopee' ? 'bg-orange-50 text-orange-700' : '' }}
                                    {{ !in_array($sale->platform, ['Tokopedia', 'Shopee']) ? 'bg-gray-100 text-gray-700' : '' }}">
                                    {{ $sale->platform }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-medium">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">Rp {{ number_format($sale->sell_price * $sale->quantity, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-emerald-600">Rp {{ number_format($sale->profit, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-xs">Belum ada data transaksi penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- WINDOW MODAL POPUP: IMPORT LAPORAN --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 backdrop-blur-xs transition-opacity">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl space-y-5 mx-4">
                
                {{-- Header Modal --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Import Laporan Penjualan</h3>
                            <p class="text-xs text-gray-400">Tokopedia, Shopee, TikTok Shop, Lazada, atau format Excel kustom</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImportModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-50 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Marketplace Pill Selector --}}
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-700">Platform Marketplace</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Tokopedia', 'Shopee', 'TikTok Shop', 'Lazada', 'Manual', 'Lainnya'] as $pImp)
                            <button type="button" wire:click="$set('importPlatform', '{{ $pImp }}')"
                                    class="rounded-full px-3.5 py-1.5 text-xs font-medium transition-all
                                    {{ $importPlatform === $pImp ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $pImp }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Area Dropzone File Drag & Drop --}}
                <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-400 bg-blue-50/10 px-6 py-12 text-center transition-colors hover:bg-blue-50/20">
                    <input type="file" wire:model="excelFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                    
                    <div class="space-y-2 pointer-events-none">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        
                        @if ($excelFile)
                            <p class="text-sm font-semibold text-blue-600 font-mono">{{ $excelFile->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-400">Berkas siap diproses, klik tombol konfirmasi di bawah.</p>
                        @else
                            <p class="text-sm font-semibold text-gray-800">Drag & drop file Excel di sini</p>
                            <p class="text-xs text-gray-400">atau klik untuk memilih file</p>
                            <p class="text-[10px] text-gray-400">Format yang didukung: .xlsx, .xls, .csv</p>
                        @endif
                    </div>
                </div>
                @error('excelFile') <p class="text-xs text-red-600 -mt-2 px-1">{{ $message }}</p> @enderror

                {{-- Tips Info Box --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 space-y-2 text-xs text-gray-500">
                    <h4 class="font-bold text-gray-700">Tips Import:</h4>
                    <ul class="list-disc list-inside space-y-1 pl-1 text-gray-500">
                        <li>Sistem akan otomatis mendeteksi kolom: Nama Produk, Jumlah, Harga, Tanggal</li>
                        <li>Produk akan dicocokkan otomatis dengan inventori berdasarkan nama</li>
                        <li>Anda bisa review dan koreksi pencocokan sebelum konfirmasi</li>
                        <li>Untuk Tokopedia: download dari menu Penjualan &rarr; Laporan &rarr; Export Excel</li>
                        <li>Untuk Shopee: download dari Pesanan Saya &rarr; Export &rarr; Order</li>
                    </ul>
                </div>

                {{-- Footer Button --}}
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <button type="button" wire:click="closeImportModal" class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="importExcel" wire:loading.attr="disabled"
                            class="rounded-lg bg-blue-500 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-600 transition-colors inline-flex items-center gap-1.5">
                        <span wire:loading class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        Mulai Import Data
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>