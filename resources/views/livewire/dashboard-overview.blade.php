<div class="space-y-6">

    {{-- ── Flash message ──────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Stat cards ──────────────────────────────────────────────────────────── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Inventory value --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Nilai Inventori</p>
                    <p class="font-mono text-2xl font-semibold text-gray-900">
                        Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-full bg-blue-50 p-3">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ $totalUnits }} unit total &bull; {{ $totalProducts }} jenis produk</p>
        </div>

        {{-- Today's revenue --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                    <p class="font-mono text-2xl font-semibold text-gray-900">
                        Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-full bg-blue-50 p-3">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ $todaySales->count() }} transaksi</p>
        </div>

        {{-- Today's profit --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Keuntungan Hari Ini</p>
                    <p class="font-mono text-2xl font-semibold text-green-600">
                        Rp {{ number_format($todayProfit, 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-full bg-green-50 p-3">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Margin {{ $todayMargin }}%</p>
        </div>

        {{-- Restock needed --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Perlu Restock</p>
                    <p class="font-mono text-2xl font-semibold text-amber-600">
                        {{ $lowStockItems->count() + $outOfStockItems->count() }}
                    </p>
                </div>
                <div class="rounded-full bg-amber-50 p-3">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                @if($outOfStockItems->count() > 0){{ $outOfStockItems->count() }} habis &bull; @endif
                {{ $lowStockItems->count() }} stok rendah
            </p>
        </div>
    </div>

    {{-- ── Out of stock alert ───────────────────────────────────────────────────── --}}
    @if ($outOfStockItems->count() > 0)
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="mb-2 text-sm font-semibold text-red-800">
                Stok Habis ({{ $outOfStockItems->count() }} produk)
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach ($outOfStockItems as $item)
                    <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700">
                        {{ $item->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Low stock table ─────────────────────────────────────────────────────── --}}
    @if ($lowStockItems->count() > 0)
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900">Peringatan Stok Rendah</h3>
                <p class="text-sm text-gray-500">Produk dengan total stok kurang dari 3 unit</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Nama Produk</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rumah</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Toko</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Harga Jual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($lowStockItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">{{ $item->stock_rumah }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">{{ $item->stock_toko }}</td>
                                <td class="px-4 py-3 text-right font-mono font-medium text-amber-600">{{ $item->total_stock }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Recent sales ─────────────────────────────────────────────────────────── --}}
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900">Penjualan Terbaru</h3>
            <p class="text-sm text-gray-500">5 transaksi terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Platform</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Keuntungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentSales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                {{ $sale->date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $sale->product_name }}</td>
                            <td class="px-4 py-3">
                                @if ($sale->platform)
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Models\Sale::platformColor($sale->platform) }}">
                                        {{ $sale->platform }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">
                                Rp {{ number_format($sale->sell_price * $sale->quantity, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-green-600">
                                Rp {{ number_format($sale->profit, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Belum ada penjualan tercatat
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>