<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan QR Meja - Coffee Shop</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-amber-50/40 text-gray-800 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 shadow-xl shadow-amber-900/5 text-center border border-amber-100/80">
        <div class="w-16 h-16 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-2">Scan QR Meja Anda</h1>
        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
            {{ $message ?? 'Silakan arahkan kamera HP Anda ke kode QR yang terpasang di meja untuk memesan makanan dan minuman.' }}
        </p>

        <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-200/50 text-xs text-amber-900 text-left space-y-2">
            <div class="font-bold flex items-center gap-1.5 text-amber-950">
                <span>💡</span> Cara Memesan:
            </div>
            <ol class="list-decimal list-inside space-y-1 text-gray-600">
                <li>Buka kamera ponsel Anda.</li>
                <li>Pindai (scan) QR code pada tent card meja.</li>
                <li>Pilih menu favorit & bayar via QRIS.</li>
            </ol>
        </div>

        <div class="mt-6 text-xs text-gray-400">
            Perlu bantuan? Panggil barista atau staf kami.
        </div>
    </div>
</body>
</html>
