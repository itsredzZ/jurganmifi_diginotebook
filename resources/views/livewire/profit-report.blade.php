<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Laporan Keuntungan</h2>
        <p class="text-xs text-gray-400">Analisis penjualan dan keuntungan bisnis</p>
    </div>

    {{-- FILTER PERIODE --}}
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-600">
            <span class="flex items-center gap-1.5 mr-1 text-gray-400">📅 Filter Periode:</span>
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
                <h3 class="font-bold text-gray-900 text-sm">Riwayat Volume Penjualan</h3>
                <p class="text-[11px] text-gray-400">Total unit terjual per tahun / per bulan</p>
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

        @if ($volumeChartMode === 'bulan')
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="text-gray-400 mr-1">Pilih tahun:</span>
                @foreach([2021, 2022, 2023, 2024, 2025, 2026] as $chartYr)
                    <button type="button" wire:click="$set('selectedChartYear', {{ $chartYr }})"
                            class="px-3 py-1 rounded-lg border font-bold font-mono transition-all
                            {{ $selectedChartYear === $chartYr ? 'bg-cyan-500 text-white border-cyan-500' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' }}">
                        {{ $chartYr }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="relative h-64 w-full" wire:ignore>
            <canvas id="lineVolumeChart"></canvas>
        </div>

        <div class="overflow-x-auto border-t border-gray-100 pt-4">
            <table class="w-full text-xs font-medium font-mono text-center">
                <thead>
                    <tr class="text-gray-400 font-sans border-b border-gray-50">
                        @foreach($yearsRange as $yr) <th class="pb-2 font-normal">{{ $yr }}</th> @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-gray-900 font-bold text-sm">
                        @foreach($yearsRange as $yr) <td class="pt-1">{{ number_format($yearlyTotals[$yr], 0, ',', '.') }}</td> @endforeach
                    </tr>
                    <tr class="text-[10px] font-sans text-gray-400">
                        @foreach($yearsRange as $yr) <td class="pt-0.5">Total Unit</td> @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
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

    {{-- TABEL DETAIL TRANSAKSI --}}
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
        <div>
            <h3 class="font-bold text-gray-900 text-sm">Detail Transaksi</h3>
            <p class="text-[11px] text-gray-400 font-mono">{{ $salesData->count() }} transaksi ditemukan</p>
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
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $sale->product_name }}</td>
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
                            backgroundColor: isYearly ? ds.color : 'transparent',
                            tension: 0.3,
                            borderWidth: isYearly ? 0 : 3,
                            pointRadius: isYearly ? 0 : 4,
                            borderRadius: isYearly ? 4 : 0,
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
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
                    responsive: true,
                    maintainAspectRatio: false,
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
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
                }
            });

            window.addEventListener('refresh-side-charts', event => {
                const d = event.detail.data;
                barChart.data.labels = d.barLabels;
                barChart.data.datasets[0].data = d.barData;
                barChart.update();

                pieChart.data.labels = d.pieLabels;
                pieChart.data.datasets[0].data = d.pieData;
                pieChart.update();
            });
        });
    </script>
    @endpush
</div>