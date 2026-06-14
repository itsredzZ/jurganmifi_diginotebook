<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Juragan Mifi Surabaya')</title>

    {{-- Tailwind CDN (swap with Vite if you already have it set up) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js (used by Profit Report) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 antialiased">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Juragan Mifi Surabaya</h1>
                    <p class="text-sm text-gray-500">Sistem Manajemen Inventori &amp; Penjualan</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Hari ini</p>
                    <p class="font-mono text-sm font-medium text-gray-800">{{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    {{-- ── Navigation tabs ─────────────────────────────────────────────────── --}}
    <div class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="flex overflow-x-auto">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 border-b-2 px-3 py-4 text-sm font-medium transition-colors whitespace-nowrap
                          {{ request()->routeIs('dashboard') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                {{-- Inventori --}}
                <a href="{{ route('inventory') }}"
                   class="flex items-center gap-2 border-b-2 px-3 py-4 text-sm font-medium transition-colors whitespace-nowrap
                          {{ request()->routeIs('inventory') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Inventori
                </a>

                {{-- Catat Penjualan --}}
                <a href="{{ route('sales') }}"
                   class="flex items-center gap-2 border-b-2 px-3 py-4 text-sm font-medium transition-colors whitespace-nowrap
                          {{ request()->routeIs('sales') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Catat Penjualan
                </a>

                {{-- Laporan --}}
                <a href="{{ route('profit') }}"
                   class="flex items-center gap-2 border-b-2 px-3 py-4 text-sm font-medium transition-colors whitespace-nowrap
                          {{ request()->routeIs('profit') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Laporan Keuntungan
                </a>
            </nav>
        </div>
    </div>

    {{-- ── Content ──────────────────────────────────────────────────────────── --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>