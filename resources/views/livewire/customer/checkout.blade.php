<div class="flex-1 flex flex-col p-4 space-y-4">

    <!-- Top Bar Navigation -->
    <div class="flex items-center justify-between pb-2 border-b border-stone-100">
        <a href="{{ route('customer.cart') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-amber-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Keranjang
        </a>
    </div>

    <!-- Title -->
    <div>
        <h1 class="text-xl font-black text-stone-900 tracking-tight">Konfirmasi Pesanan</h1>
        <p class="text-xs text-stone-500 mt-0.5">Periksa pesanan Anda sebelum melakukan pembayaran.</p>
    </div>

    <!-- Error Alerts -->
    @error('order')
        <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <!-- Table Badge Card -->
    <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/60 rounded-2xl flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-black text-sm shadow-xs">
                🪑
            </div>
            <div>
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Lokasi Meja</span>
                <div class="text-base font-black text-stone-900">Meja {{ $tableNumber }}</div>
            </div>
        </div>
        <span class="text-xs text-amber-800 font-semibold bg-amber-200/60 px-2.5 py-1 rounded-lg">Duduk di tempat</span>
    </div>

    <!-- Items Summary Card -->
    <div class="bg-white p-4 rounded-2xl border border-stone-200/70 shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-stone-100">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Menu yang Dipesan</h2>
            <span class="text-xs text-stone-400 font-medium">{{ $totalQuantity }} Item</span>
        </div>

        <div class="divide-y divide-stone-100 text-xs">
            @foreach ($cartItems as $item)
                <div class="py-2.5 flex items-start justify-between gap-2">
                    <div class="space-y-0.5">
                        <div class="font-bold text-stone-900">
                            {{ $item['quantity'] }}x {{ $item['product_name'] }}
                        </div>

                        @if (!empty($item['modifiers']))
                            <div class="text-[11px] text-stone-500">
                                {{ implode(', ', array_column($item['modifiers'], 'option_name')) }}
                            </div>
                        @endif

                        @if (!empty($item['notes']))
                            <div class="text-[10px] text-stone-400 italic">
                                "{{ $item['notes'] }}"
                            </div>
                        @endif
                    </div>
                    <div class="font-extrabold text-stone-900 shrink-0">
                        Rp {{ number_format($item['line_total'], 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-xs font-bold text-stone-700">
            <span>Total Tagihan</span>
            <span class="text-base font-black text-amber-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Payment Method Card -->
    <div class="bg-white p-4 rounded-2xl border border-stone-200/70 shadow-xs space-y-3">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Metode Pembayaran</h2>

        <div class="p-3.5 rounded-2xl border-2 border-amber-600 bg-amber-50/40 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white border border-stone-200 p-1 flex items-center justify-center shrink-0 shadow-xs font-black text-xs text-red-600">
                    QRIS
                </div>
                <div>
                    <div class="text-xs font-extrabold text-stone-900">QRIS Instant</div>
                    <div class="text-[11px] text-stone-500">BCA, Mandiri, GoPay, OVO, ShopeePay, DANA</div>
                </div>
            </div>
            <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs">✓</span>
        </div>
        <p class="text-[11px] text-stone-400">Kode QRIS akan langsung muncul setelah Anda menekan tombol di bawah.</p>
    </div>

    <!-- Notes Card -->
    <div class="bg-white p-4 rounded-2xl border border-stone-200/70 shadow-xs space-y-2">
        <label class="block text-xs font-extrabold uppercase tracking-wider text-stone-800">
            Catatan Tambahan untuk Barista (Opsional)
        </label>
        <textarea
            wire:model="orderNotes"
            rows="2"
            placeholder="Tuliskan jika ada permintaan khusus untuk pesanan Anda..."
            class="w-full text-xs p-3 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:ring-amber-500 focus:border-amber-500 text-stone-800 placeholder-stone-400"></textarea>
    </div>

    <!-- Action Button -->
    <div class="pt-2">
        <button
            type="button"
            wire:click="placeOrder"
            wire:loading.attr="disabled"
            class="w-full py-4 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 active:scale-[0.99] text-white rounded-2xl font-black text-sm shadow-xl shadow-amber-900/15 flex items-center justify-between px-5 transition">
            <span wire:loading.remove wire:target="placeOrder">Bayar Sekarang</span>
            <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses Pesanan...
            </span>
            <span class="font-extrabold text-amber-200">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </button>
    </div>

</div>
