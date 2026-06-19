<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Laporan Keuntungan</h2>
        <p class="text-xs text-gray-400">Analisis penjualan dan keuntungan bisnis</p>
    </div>

    {{-- FILTER PERIODE --}}
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-600">
            <span class="flex items-center gap-1.5 mr-1 text-gray-400">📅 Filter Periode Utama:</span>
            @foreach(['Semua', '7 Hari', '30 Hari', 'Bulan Ini', 'Bulan Lalu'] as $per)
                <button type="button" wire:click="$set('period', '{{ $per }}')"
                        class="rounded-full px-4 py-1.5 font-bold transition-all
                        {{ $period === $per ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $per }}
                </button>
            @endforeach
            <button type="button" wire:click="$set('period', 'Custom')"
                    class="rounded-full px-4 py-1.5 font-bold transition-all {{ $period === 'Custom' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                Custom
            </button>
        </div>

        @if($period === 'Custom')
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-50 text-xs">
                <input type="date" wire:model="startDate" class="rounded-lg border border-gray-200 p-2 focus:ring-1 focus:ring-blue-500 outline-none font-mono"/>
                <span class="text-gray-400 font-bold">s/d</span>
                <input type="date" wire:model="endDate" class="rounded-lg border border-gray-200 p-2 focus:ring-1 focus:ring-blue-500 outline-none font-mono"/>
                <button type="button" wire:click="applyCustomDate" class="rounded-lg bg-gray-800 text-white px-4 py-2 font-bold hover:bg-gray-900">
                    Terapkan Rentang
                </button>
            </div>
        @endif
    </div>

    {{-- 4 STAT CARDS --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-400">Total Penjualan</p>
                <p class="text-xl font-extrabold text-gray-900 font-mono tracking-tight">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 font-bold text-lg">$</div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-400">Total Keuntungan</p>
                <p class="text-xl font-extrabold text-emerald-600 font-mono tracking-tight">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-400">Rata-rata Margin</p>
                <p class="text-xl font-extrabold text-gray-900 font-mono tracking-tight">{{ $averageMargin }}%</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-400">Transaksi</p>
                <p class="text-xl font-extrabold text-gray-900 font-mono tracking-tight">{{ $transactionCount }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l3.5 2"/></svg>
            </div>
        </div>
    </div>

    {{-- PANEL: RIWAYAT VOLUME PENJUALAN --}}
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Riwayat Volume Penjualan (2018-2027)</h3>
                <p class="text-[11px] text-gray-400">Klik beberapa tahun untuk membandingkan garisnya</p>
            </div>
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg text-xs font-bold">
                <button type="button" wire:click="setVolumeMode('tahun')"
                        class="px-3 py-1.5 rounded transition-colors {{ $volumeChartMode === 'tahun' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-500 bg-transparent' }}">
                    Per Tahun
                </button>
                <button type="button" wire:click="setVolumeMode('bulan')"
                        class="px-3 py-1.5 rounded transition-colors {{ $volumeChartMode === 'bulan' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-500 bg-transparent' }}">
                    Per Bulan
                </button>
            </div>
        </div>

        {{-- GANTI @if DENGAN MANIPULASI CLASS HIDDEN --}}
        <div class="items-center gap-2 text-xs font-semibold border-b border-gray-50 pb-3 {{ $volumeChartMode === 'tahun' ? 'hidden' : 'flex flex-wrap' }}">
            <span class="text-gray-400 mr-1">Bandingkan Tahun:</span>
            @foreach([2021, 2022, 2023, 2024, 2025, 2026, 2027] as $chartYr)
                <button type="button" wire:click="toggleChartYear({{ $chartYr }})"
                        class="px-3 py-1 rounded-lg border font-bold font-mono transition-all
                        {{ in_array($chartYr, $selectedChartYears) ? 'bg-cyan-500 text-white border-cyan-500 shadow-sm' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' }}">
                    {{ $chartYr }}
                </button>
            @endforeach
        </div>

        {{-- CANVAS TETAP AMAN KARENA STRUKTUR DOM DI ATASNYA TIDAK PERNAH DIHAPUS --}}
        <div class="relative h-72 w-full" wire:ignore>
            <canvas id="lineVolumeChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
        <h3 class="font-bold text-gray-900 text-sm">Strategi Meningkatkan Keuntungan</h3>
        <p class="text-sm text-gray-600 leading-relaxed">
            Berdasarkan analisis data penjualan, beberapa strategi kunci untuk meningkatkan keuntungan Juragan Mifi meliputi:
        </p>
        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
            <li><strong>Optimasi Harga Dinamis:</strong> Menyesuaikan harga berdasarkan permintaan dan kompetisi untuk memaksimalkan margin.</li>
            <li><strong>Fokus pada Produk dengan Margin Tinggi:</strong> Meningkatkan promosi dan stok produk yang memberikan keuntungan lebih besar.</li>
            <li><strong>Efisiensi Operasional:</strong> Mengurangi biaya melalui negosiasi dengan supplier dan optimasi proses pengiriman.</li>
        </ul>
        <a href="{{ route('business-strategy') }}" class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-xs hover:bg-blue-700 transition-colors">
            Lihat Strategi Lengkap
        </a>
    </div>

    {{-- DUA GRAFIK BAWAH --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
            <div><h3 class="font-bold text-gray-900 text-sm">Keuntungan Harian</h3></div>
            <div class="relative h-60 w-full" wire:ignore>
                <canvas id="barDailyProfitChart"></canvas>
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4 relative">
            <div><h3 class="font-bold text-gray-900 text-sm">Keuntungan per Produk</h3></div>
            <div class="relative h-60 w-full flex justify-center items-center" wire:ignore>
                <div class="w-full h-full">
                    <canvas id="pieProductProfitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DETAIL TRANSAKSI DENGAN FILTER & PAGINASI --}}
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Detail Transaksi</h3>
                <p class="text-[11px] text-gray-400 font-mono">{{ $salesData->total() }} transaksi ditemukan</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.300ms="tableSearch" placeholder="Cari produk..." class="text-xs rounded-lg border border-gray-200 px-3 py-1.5 focus:ring-1 focus:ring-blue-500 outline-none w-40">
                <select wire:model.live="tablePlatform" class="text-xs rounded-lg border border-gray-200 px-3 py-1.5 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="Semua">Semua Platform</option>
                    <option value="Tokopedia">Tokopedia</option>
                    <option value="Shopee">Shopee</option>
                    <option value="TikTok Shop">TikTok Shop</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold border-b border-gray-100 tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Platform</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Keuntungan</th>
                        <th class="px-4 py-3 text-center">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @forelse($salesData as $sale)
                        @php
                            $totalRow = $sale->sell_price * $sale->quantity;
                            $rowMargin = $totalRow > 0 ? round(($sale->profit / $totalRow) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-gray-400 font-mono">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-bold text-gray-900 flex items-center gap-1">
                                {{ $sale->product_name }}
                                @if($sale->is_forecast) <span class="text-[9px] bg-amber-100 text-amber-700 px-1 py-0.5 rounded">PREDIKSI</span> @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 font-bold text-[10px] tracking-wide uppercase
                                    {{ $sale->platform === 'Tokopedia' ? 'bg-green-50 text-green-700' : '' }}
                                    {{ $sale->platform === 'Shopee' ? 'bg-orange-50 text-orange-700' : '' }}
                                    {{ !in_array($sale->platform, ['Tokopedia', 'Shopee']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ $sale->platform ?? 'Lainnya' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-gray-900">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-500">Rp {{ number_format($sale->sell_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($totalRow, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($sale->profit, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-gray-500">{{ $rowMargin }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada data transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINASI --}}
        <div class="pt-4 border-t border-gray-50">
            {{ $salesData->links() }}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let volumeChart = null;
            function renderVolumeChart(payload) {
                if (volumeChart) volumeChart.destroy();
                const ctx = document.getElementById('lineVolumeChart').getContext('2d');
                const isYearly = payload.mode === 'tahun';
                
                volumeChart = new Chart(ctx, {
                    type: isYearly ? 'bar' : 'line',
                    data: {
                        labels: payload.labels,
                        datasets: payload.datasets.map(ds => ({
                            label: ds.label,
                            data: ds.data,
                            borderColor: ds.color,
                            // Jika forecast, garis putus-putus. Jika aktual, garis lurus. Tahunan = bar
                            borderDash: ds.is_forecast ? [5, 5] : [],
                            backgroundColor: isYearly ? ds.color : (ds.is_forecast ? 'transparent' : 'transparent'),
                            tension: 0.3,
                            borderWidth: isYearly ? 0 : 3,
                            pointRadius: isYearly ? 0 : 4,
                            pointBackgroundColor: ds.color,
                            borderRadius: isYearly ? 4 : 0,
                            spanGaps: true // Penting agar garis menyambung meski ada nilai null di array forecast
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { 
                                position: 'bottom',
                                labels: { boxWidth: 20, padding: 15 } 
                            } 
                        },
                        scales: { 
                            y: { beginAtZero: true }, 
                            x: { grid: { display: false } } 
                        }
                    }
                });
            }
            renderVolumeChart(@json(['mode' => $mode, 'labels' => $labels, 'datasets' => $datasets]));
            window.addEventListener('refresh-volume-chart', e => renderVolumeChart(e.detail.data));

            const ctxBar = document.getElementById('barDailyProfitChart').getContext('2d');
            let barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: @json($barLabels),
                    datasets: [{ data: @json($barData), backgroundColor: '#dbeafe', hoverBackgroundColor: '#3b82f6', borderRadius: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } },
                        x: { grid: { display: false } }
                    }
                }
            });

            const ctxPie = document.getElementById('pieProductProfitChart').getContext('2d');
            let pieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: @json($pieLabels),
                    datasets: [{ data: @json($pieData), backgroundColor: ['#2563eb', '#10b981', '#f59e0b'] }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '70%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
                }
            });

            window.addEventListener('refresh-side-charts', event => {
                const d = event.detail.data;
                barChart.data.labels = d.barLabels; barChart.data.datasets[0].data = d.barData; barChart.update();
                pieChart.data.labels = d.pieLabels; pieChart.data.datasets[0].data = d.pieData; pieChart.update();
            });
        });
    </script>
    @endpush
</div>