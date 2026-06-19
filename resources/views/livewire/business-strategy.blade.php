<div class="space-y-6">
    {{-- Header dengan tombol kembali --}}
    <div class="flex items-start gap-3">
        <a href="{{ route('profit') }}" wire:navigate
           class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 transition-colors mt-0.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Strategi Pemasaran &amp; Pengembangan Usaha</h2>
            <p class="text-xs text-gray-400 mt-1">
                Disusun oleh Business Strategist — Valent Fortune Setiawan — berdasarkan analisis tren penjualan dan prediksi KNIME Random Forest 2026–2027
            </p>
        </div>
    </div>

    {{-- Paragraf Pembuka --}}
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-600 leading-relaxed">
            Selama delapan tahun beroperasi, Juragan Mifi telah berkembang dan bertahan di tengah berbagai dinamika pasar — termasuk era pandemi Covid-19 dan krisis ekonomi yang sempat menekan penjualan. Berdasarkan data prediksi penjualan 2026–2027, berikut tujuh strategi yang dapat diterapkan untuk terus menjaga pertumbuhan dan daya saing usaha.
        </p>
    </div>

    {{-- Accordion 7 Strategi --}}
    <div class="divide-y divide-gray-100 rounded-xl border border-gray-100 bg-white overflow-hidden shadow-sm">
        @foreach ($strategies as $i => $s)
            <div>
                <button type="button"
                        class="strategy-toggle w-full flex items-center justify-between gap-3 px-5 py-4 text-left hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-base">
                            {{ $s['icon'] }}
                        </span>
                        <span class="text-sm font-semibold text-gray-900">{{ $i + 1 }}. {{ $s['title'] }}</span>
                    </div>
                    <svg class="strategy-chevron h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200 {{ $i === 0 ? 'rotate-180' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="strategy-panel {{ $i === 0 ? '' : 'hidden' }} px-5 pb-5 pl-[3.25rem] text-sm text-gray-600 leading-relaxed space-y-2">
                    <p>{{ $s['description'] }}</p>
                    @if ($s['example'])
                        <div class="rounded-lg bg-blue-50/60 border border-blue-100 px-3 py-2 text-xs text-blue-700">
                            <span class="font-semibold">Contoh:</span> {{ $s['example'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Catatan Penutup --}}
    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-5">
        <p class="text-sm text-amber-800 leading-relaxed">
            <span class="font-semibold">Catatan penting:</span> untuk meningkatkan daya saing Juragan Mifi, promosi tidak cukup hanya fokus pada harga murah — pelayanan yang cepat dan kemampuan mendukung kebutuhan pelanggan adalah nilai tambah yang membedakan bisnis ini dari kompetitor.
        </p>
    </div>

    @push('scripts')
    <script>
        (function () {
            function bindStrategyAccordion() {
                document.querySelectorAll('.strategy-toggle').forEach(function (btn) {
                    if (btn.dataset.bound === '1') return; // hindari double-binding saat navigasi SPA
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', function () {
                        var panel = btn.nextElementSibling;
                        var chevron = btn.querySelector('.strategy-chevron');
                        panel.classList.toggle('hidden');
                        chevron.classList.toggle('rotate-180');
                    });
                });
            }
            document.addEventListener('DOMContentLoaded', bindStrategyAccordion);
            document.addEventListener('livewire:navigated', bindStrategyAccordion);
        })();
    </script>
    @endpush
</div>