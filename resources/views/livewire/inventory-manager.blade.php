<div class="space-y-6">

    {{-- ── Flash ──────────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <button wire:click="openNewForm"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Produk
            </button>
            <button wire:click="openRestockForm"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Restock
            </button>
        </div>

        {{-- Category filter --}}
        <div class="flex flex-wrap gap-1">
            @foreach ([
                'all'       => 'Semua',
                'mifi'      => 'Modem/MiFi',
                'router'    => 'Router',
                'battery'   => 'Baterai',
                'simcard'   => 'SIMcard',
                'accessory' => 'Aksesori',
            ] as $key => $label)
                <button wire:click="$set('filterCategory', '{{ $key }}')"
                        class="rounded-full px-3 py-1 text-xs font-medium transition-colors
                               {{ $filterCategory === $key
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white border border-gray-200 text-gray-600 hover:border-blue-300' }}">
                    {{ $label }}
                    @if ($key !== 'all' && isset($categoryCounts[$key]))
                        ({{ $categoryCounts[$key] }})
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Expandable form panel ────────────────────────────────────────────────── --}}
    @if ($showForm)
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-5">
            @if ($formMode === 'new')
                {{-- Add new product form --}}
                <h3 class="mb-4 font-semibold text-blue-900">Tambah Produk Baru</h3>
                <form wire:submit="addProduct" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-gray-700">Nama Produk</label>
                        <input wire:model="name" type="text" placeholder="e.g. Modem WiFi 4G"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('name') border-red-400 @enderror"/>
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Kategori</label>
                        <select wire:model="category"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="mifi">Modem/MiFi</option>
                            <option value="router">Router</option>
                            <option value="battery">Baterai</option>
                            <option value="simcard">SIMcard</option>
                            <option value="accessory">Aksesori</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Harga Beli (Rp)</label>
                        <input wire:model="purchasePrice" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('purchasePrice') border-red-400 @enderror"/>
                        @error('purchasePrice')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Harga Jual (Rp)</label>
                        <input wire:model="sellPrice" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('sellPrice') border-red-400 @enderror"/>
                        @error('sellPrice')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Stok Rumah</label>
                        <input wire:model="stockRumah" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Stok Toko</label>
                        <input wire:model="stockToko" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                    </div>

                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                            Simpan
                        </button>
                        <button type="button" wire:click="closeForm"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>

            @else
                {{-- Restock form --}}
                <h3 class="mb-4 font-semibold text-blue-900">Restock Produk</h3>
                <form wire:submit="restock" class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-gray-700">Pilih Produk</label>
                        <select wire:model="restockId"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('restockId') border-red-400 @enderror">
                            <option value="">— Pilih produk —</option>
                            @foreach ($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->total_stock }} unit)</option>
                            @endforeach
                        </select>
                        @error('restockId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Tambah Stok Rumah</label>
                        <input wire:model="restockRumah" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                        @error('restockRumah')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Tambah Stok Toko</label>
                        <input wire:model="restockToko" type="number" min="0" placeholder="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                            Update Stok
                        </button>
                        <button type="button" wire:click="closeForm"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- ── Products table ───────────────────────────────────────────────────────── --}}
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Kategori</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Harga Beli</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Harga Jual</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Margin</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rumah</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Toko</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                        @if ($editingId === $product->id)
                            {{-- ── Inline edit row ── --}}
                            <tr class="bg-blue-50">
                                <td class="px-4 py-2">
                                    <input wire:model="editName" type="text"
                                           class="w-full rounded border border-blue-300 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                                </td>
                                <td class="px-4 py-2">
                                    <select wire:model="editCategory"
                                            class="rounded border border-blue-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="mifi">Modem/MiFi</option>
                                        <option value="router">Router</option>
                                        <option value="battery">Baterai</option>
                                        <option value="simcard">SIMcard</option>
                                        <option value="accessory">Aksesori</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model="editPurchasePrice" type="number" min="0"
                                           class="w-28 rounded border border-blue-300 px-2 py-1 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model="editSellPrice" type="number" min="0"
                                           class="w-28 rounded border border-blue-300 px-2 py-1 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                                </td>
                                <td class="px-4 py-2 text-right text-xs text-gray-400">—</td>
                                <td class="px-4 py-2">
                                    <input wire:model="editStockRumah" type="number" min="0"
                                           class="w-16 rounded border border-blue-300 px-2 py-1 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model="editStockToko" type="number" min="0"
                                           class="w-16 rounded border border-blue-300 px-2 py-1 text-sm text-right font-mono focus:outline-none focus:ring-1 focus:ring-blue-500"/>
                                </td>
                                <td class="px-4 py-2 text-right text-xs text-gray-400">—</td>
                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="saveEdit"
                                                class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700">
                                            Simpan
                                        </button>
                                        <button wire:click="cancelEdit"
                                                class="rounded border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                            Batal
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @elseif ($deletingId === $product->id)
                            {{-- ── Delete confirm row ── --}}
                            <tr class="bg-red-50">
                                <td colspan="8" class="px-4 py-3 text-sm text-red-800">
                                    Hapus <strong>{{ $product->name }}</strong>? Tindakan ini tidak bisa dibatalkan.
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="deleteProduct"
                                                class="rounded bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700">
                                            Hapus
                                        </button>
                                        <button wire:click="cancelDelete"
                                                class="rounded border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                            Batal
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @else
                            {{-- ── Normal row ── --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Models\Product::categoryColor($product->category) }}">
                                        {{ \App\Models\Product::categoryLabel($product->category) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-600">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-900 font-medium">
                                    Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-xs font-medium text-green-600">
                                        {{ $product->margin_percent }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">{{ $product->stock_rumah }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-700">{{ $product->stock_toko }}</td>
                                <td class="px-4 py-3 text-right font-mono font-semibold
                                    {{ $product->total_stock === 0 ? 'text-red-600' : ($product->total_stock < 3 ? 'text-amber-600' : 'text-gray-900') }}">
                                    {{ $product->total_stock }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="startEdit({{ $product->id }})"
                                                class="rounded border border-gray-200 p-1.5 text-gray-500 hover:border-blue-300 hover:text-blue-600 transition-colors"
                                                title="Edit">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $product->id }})"
                                                class="rounded border border-gray-200 p-1.5 text-gray-500 hover:border-red-300 hover:text-red-600 transition-colors"
                                                title="Hapus">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                Tidak ada produk ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>