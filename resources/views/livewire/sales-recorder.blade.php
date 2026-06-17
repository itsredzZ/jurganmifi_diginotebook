<div class="space-y-6">

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ── Form pencatatan + live preview Alpine ──────────────────────────── --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
                 x-data="{
                    products: @js($products->map(fn ($p) => [
                        'id' => $p->id, 'name' => $p->name,
                        'sell_price' => $p->sell_price, 'purchase_price' => $p->purchase_price,
                        'stock' => $p->total_stock,
                    ])),
                    get selected() { return this.products.find(p => p.id == $wire.productId) ?? null },
                    get qty() { return Number($wire.quantity) || 0 },
                    get previewRevenue() { return this.selected ? this.selected.sell_price * this.qty : null },
                    get previewProfit() { return this.selected ? (this.selected.sell_price - this.selected.purchase_price) * this.qty : null },
                    rupiah(n) { return 'Rp ' + Number(n ?? 0).toLocaleString('id-ID') }
                 }">

                <h3 class="mb-4 font-semibold text-gray-900">Catat Penjualan</h3>

                <form wire:submit="recordSale" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Produk</label>
                        <select wire:model="productId"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('productId') border-red-400 @enderror">
                            <option value="">— Pilih produk —</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (stok: {{ $p->total_stock }})</option>
                            @endforeach
                        </select>
                        @error('productId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Jumlah</label>
                        <input wire:model="quantity" type="number" min="1"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('quantity') border-red-400 @enderror"/>
                        @error('quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p x-show="selected && qty > selected.stock" x-cloak class="mt-1 text-xs text-amber-600">
                            Hanya tersedia <span x-text="selected?.stock"></span> unit.
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Platform</label>
                        <select wire:model="platform"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">— Tidak ditentukan —</option>
                            <option value="Tokopedia">Tokopedia</option>
                            <option value="Shopee">Shopee</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Tanggal</label>
                        <input wire:model="date" type="date"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('date') border-red-400 @enderror"/>
                        @error('date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Live preview — murni client-side, tidak ada request server --}}
                    <div x-show="selected" x-cloak class="rounded-lg border border-blue-100 bg-blue-50 p-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Penjualan</span>
                            <span class="font-mono font-medium text-gray-900" x-text="rupiah(previewRevenue)"></span>
                        </div>
                        <div class="mt-1 flex justify-between text-gray-600">
                            <span>Estimasi Keuntungan</span>
                            <span class="font-mono font-medium text-green-600" x-text="rupiah(previewProfit)"></span>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                            wire:loading.attr="disabled" wire:target="recordSale">
                        <span wire:loading.remove wire:target="recordSale">Catat Penjualan</span>
                        <span wire:loading wire:target="recordSale">Menyimpan...</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Riwayat penjualan ────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-medium text-gray-700">Bulan</label>
                    <input type="month" wire:model.live="filterMonth"
                           class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                </div>
                <div class="flex gap-6 text-sm">
                    <div><span class="text-gray-500">Total: </span>
                        <span class="font-mono font-medium text-gray-900">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</span>
                    </div>
                    <div><span class="text-gray-500">Profit: </span>
                        <span class="font-mono font-medium text-green-600">Rp {{ number_format($monthProfit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Platform</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Profit</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($sales as $sale)
                                <tr x-data="{ confirming: false }" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $sale->date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $sale->product_name }}</td>
                                    <td class="px-4 py-3">
                                        @if ($sale->platform)
                                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Models\Sale::platformColor($sale->platform) }}">
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
                                    <td class="px-4 py-3 text-center">
                                        <div x-show="!confirming">
                                            <button @click="confirming = true"
                                                    class="rounded border border-gray-200 p-1.5 text-gray-500 hover:border-red-300 hover:text-red-600 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="confirming" x-cloak class="flex justify-center gap-1">
                                            <button wire:click="deleteSale({{ $sale->id }})"
                                                    class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700">
                                                Hapus
                                            </button>
                                            <button @click="confirming = false"
                                                    class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                                Batal
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                        Belum ada penjualan di bulan ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>