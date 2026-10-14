<div class="flex-1 flex flex-col p-4 space-y-4">

    <!-- Top Bar Navigation -->
    <div class="flex items-center justify-between pb-2 border-b border-stone-100">
        <a href="{{ route('customer.menu') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-amber-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
            Tambah Menu Lain
        </a>

        @if (!empty($cartItems))
            <button
                type="button"
                wire:click="clearCart"
                wire:confirm="Kosongkan seluruh keranjang?"
                class="text-xs text-rose-500 hover:text-rose-700 font-semibold">
                Kosongkan
            </button>
        @endif
    </div>

    <!-- Title Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight">Rincian Pesanan</h1>
            <p class="text-xs text-stone-500 mt-0.5">Pesanan untuk Meja <span class="font-extrabold text-amber-800">{{ $tableNumber }}</span></p>
        </div>
        <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800">
            {{ $totalQuantity }} Item
        </span>
    </div>

    @if (!empty($cartItems))
        <!-- Cart Items List -->
        <div class="space-y-3">
            @foreach ($cartItems as $itemId => $item)
                <div wire:key="cart-item-{{ $itemId }}" class="bg-white p-3.5 rounded-2xl border border-stone-200/70 shadow-xs space-y-3">
                    <div class="flex gap-3">
                        <!-- Thumbnail -->
                        <div class="w-16 h-16 rounded-xl bg-amber-50 border border-stone-100 overflow-hidden shrink-0">
                            @if (!empty($item['product_image']))
                                <img src="{{ Storage::url($item['product_image']) }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-amber-300">
                                    <span class="text-xl">☕</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1">
                                <h3 class="text-sm font-bold text-stone-900 leading-snug line-clamp-1">{{ $item['product_name'] }}</h3>
                                <button
                                    type="button"
                                    wire:click="removeItem('{{ $itemId }}')"
                                    class="text-stone-300 hover:text-rose-500 p-0.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="text-xs text-stone-400 mt-0.5">
                                @ Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                            </div>

                            <!-- Modifiers selected -->
                            @if (!empty($item['modifiers']))
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @foreach ($item['modifiers'] as $mod)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-900 border border-amber-200/40">
                                            {{ $mod['option_name'] }}
                                            @if ($mod['price_delta'] > 0)
                                                (+{{ number_format($mod['price_delta'], 0, ',', '.') }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Notes -->
                            @if (!empty($item['notes']))
                                <div class="mt-1.5 text-[11px] text-stone-500 bg-stone-50 p-1.5 rounded-lg border border-stone-100 italic">
                                    "{{ $item['notes'] }}"
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quantity Counter & Line Total -->
                    <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                        <div class="flex items-center bg-stone-100 rounded-xl p-0.5">
                            <button
                                type="button"
                                wire:click="decrement('{{ $itemId }}')"
                                class="w-7 h-7 rounded-lg bg-white shadow-xs flex items-center justify-center font-bold text-stone-700 active:scale-90 transition">
                                -
                            </button>
                            <span class="w-8 text-center text-xs font-black text-stone-900">{{ $item['quantity'] }}</span>
                            <button
                                type="button"
                                wire:click="increment('{{ $itemId }}')"
                                class="w-7 h-7 rounded-lg bg-white shadow-xs flex items-center justify-center font-bold text-stone-700 active:scale-90 transition">
                                +
                            </button>
                        </div>

                        <div class="text-sm font-extrabold text-stone-900">
                            Rp {{ number_format($item['line_total'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary Receipt Card -->
        <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200/80 space-y-2.5 text-xs text-stone-600">
            <div class="flex items-center justify-between">
                <span>Subtotal Menu</span>
                <span class="font-bold text-stone-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Pajak Restoran</span>
                <span class="text-emerald-700 font-semibold">Termasuk</span>
            </div>
            <div class="pt-2 border-t border-stone-200 flex items-center justify-between text-sm font-black text-stone-900">
                <span>Total Pembayaran</span>
                <span class="text-base text-amber-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Checkout Action Button (Tahap 4 will attach the payment process) -->
        <div class="pt-2">
            <a
                href="{{ route('customer.checkout') }}"
                wire:navigate
                class="w-full py-3.5 bg-amber-600 hover:bg-amber-700 active:scale-[0.99] text-white rounded-2xl font-bold text-sm shadow-lg shadow-amber-900/15 flex items-center justify-between px-5 transition">
                <span>Lanjut ke Pembayaran</span>
                <div class="flex items-center gap-1.5 font-black">
                    <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16 px-4 bg-white rounded-3xl border border-stone-100 shadow-xs space-y-3">
            <div class="w-16 h-16 bg-amber-50 text-amber-700 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                🛒
            </div>
            <h3 class="text-base font-bold text-stone-900">Keranjang Masih Kosong</h3>
            <p class="text-xs text-stone-500 max-w-xs mx-auto">
                Yuk jelajahi menu kopi dan camilan favorit kami dan tambahkan ke pesanan Anda.
            </p>
            <div class="pt-2">
                <a
                    href="{{ route('customer.menu') }}"
                    wire:navigate
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    Lihat Daftar Menu
                </a>
            </div>
        </div>
    @endif

</div>
