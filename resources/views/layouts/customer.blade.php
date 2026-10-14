<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-stone-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kedai Kopi Kita') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-stone-900 bg-stone-100 flex flex-col selection:bg-amber-500 selection:text-white">
    <div class="w-full max-w-md mx-auto min-h-screen bg-white shadow-xl flex flex-col relative pb-24">
        <!-- Top Sticky Header -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-stone-200/80 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center font-black shadow-sm shadow-amber-900/20 text-lg">
                    ☕
                </div>
                <div>
                    <h1 class="text-sm font-extrabold tracking-tight text-stone-900 leading-tight">Kedai Kopi Kita</h1>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-semibold text-stone-500">Buka • Pesan dari Meja</span>
                    </div>
                </div>
            </div>

            @if (session('table_number'))
                <div class="flex items-center gap-1.5 bg-amber-50 text-amber-900 border border-amber-200/60 px-3 py-1 rounded-full text-xs font-bold shadow-xs">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Meja {{ session('table_number') }}</span>
                </div>
            @endif
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
