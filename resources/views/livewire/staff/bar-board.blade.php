<div class="py-6" wire:poll.4s="pollBarQueue"
    x-data="{
        initialReceipts: @js($pendingReceipts),
        autoPrint: (localStorage.getItem('bar-autoprint') ?? '1') === '1',
        toggleAutoPrint() {
            this.autoPrint = ! this.autoPrint;
            localStorage.setItem('bar-autoprint', this.autoPrint ? '1' : '0');
        },
        enqueueReceipts(receipts) {
            if (! this.autoPrint || ! Array.isArray(receipts) || receipts.length === 0) {
                return;
            }

            const printed = JSON.parse(localStorage.getItem('bar-printed-receipts') ?? '[]');

            receipts.forEach((receipt) => {
                if (printed.includes(receipt.id)) {
                    return;
                }

                printed.push(receipt.id);

                // Hidden iframe: the receipt view prints itself on load, so the
                // ticket comes out at the bar without anyone touching the screen.
                const frame = document.createElement('iframe');
                frame.setAttribute('aria-hidden', 'true');
                frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:1px;height:1px;border:0;';
                frame.src = receipt.url;
                document.body.appendChild(frame);
                setTimeout(() => frame.remove(), 30000);
            });

            localStorage.setItem('bar-printed-receipts', JSON.stringify(printed.slice(-200)));
        }
    }"
    x-init="enqueueReceipts(initialReceipts)"
    x-on:bar-receipts-pending.window="enqueueReceipts($event.detail.receipts)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Live Pulse -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <span>☕ Kitchen & Bar Station Display</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live KDS (Hanya Order Lunas)
                    </span>
                </h1>
                <p class="text-xs text-gray-500 mt-1">Antrean pesanan khusus barista. Urutan pengerjaan FIFO (pesanan paling awal di depan).</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="toggleAutoPrint()"
                    :class="autoPrint ? 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                    class="px-4 py-2 border font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span x-text="autoPrint ? '🖨️ Cetak Otomatis: AKTIF' : '🖨️ Cetak Otomatis: NONAKTIF'">🖨️ Cetak Otomatis: AKTIF</span>
                </button>

                <a href="{{ route('staff.cashier') }}" wire:navigate class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span>💵 Buka Meja Kasir</span>
                </a>
            </div>
        </div>

        <!-- Flash Notification -->
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold rounded-2xl flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Toolbar -->
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <button type="button" wire:click="setFilter('active')" class="px-4 py-2 rounded-xl transition {{ $filter === 'active' ? 'bg-stone-900 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua Aktif ({{ $counts['confirmed'] + $counts['preparing'] + $counts['ready'] }})
                </button>

                <button type="button" wire:click="setFilter('confirmed')" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 {{ $filter === 'confirmed' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>Antrean Masuk</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'confirmed' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }} font-black">
                        {{ $counts['confirmed'] }}
                    </span>
                </button>

                <button type="button" wire:click="setFilter('preparing')" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 {{ $filter === 'preparing' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>Sedang Dibuat</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'preparing' ? 'bg-white text-blue-700' : 'bg-blue-100 text-blue-800' }} font-black">
                        {{ $counts['preparing'] }}
                    </span>
                </button>

                <button type="button" wire:click="setFilter('ready')" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 {{ $filter === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>Siap Diantar</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'ready' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }} font-black">
                        {{ $counts['ready'] }}
                    </span>
                </button>

                <button type="button" wire:click="setFilter('done')" class="px-4 py-2 rounded-xl transition {{ $filter === 'done' ? 'bg-stone-900 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Selesai Hari Ini ({{ $counts['done'] }})
                </button>
            </div>
        </div>

        <!-- Bar Queue Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($orders as $order)
                @php
                    $elapsedMinutes = $order->created_at->diffInMinutes();
                    $isLate = $elapsedMinutes >= 15;
                    $isWarning = $elapsedMinutes >= 10 && ! $isLate;
                @endphp

                <div class="bg-white rounded-3xl border-2 transition shadow-xs flex flex-col justify-between overflow-hidden
                    @if($order->order_status === \App\Enums\OrderStatus::Confirmed) border-amber-300 ring-2 ring-amber-100
                    @elseif($order->order_status === \App\Enums\OrderStatus::Preparing) border-blue-400 ring-2 ring-blue-100
                    @elseif($order->order_status === \App\Enums\OrderStatus::Ready) border-emerald-400 ring-2 ring-emerald-100
                    @else border-gray-200
                    @endif">

                    <!-- Header with Table & Timer -->
                    <div class="p-4 border-b flex items-start justify-between gap-3
                        @if($order->order_status === \App\Enums\OrderStatus::Confirmed) bg-amber-50/80 border-amber-100
                        @elseif($order->order_status === \App\Enums\OrderStatus::Preparing) bg-blue-50/80 border-blue-100
                        @elseif($order->order_status === \App\Enums\OrderStatus::Ready) bg-emerald-50/80 border-emerald-100
                        @else bg-gray-50 border-gray-100
                        @endif">

                        <div class="flex items-center gap-2.5">
                            <span class="px-3.5 py-1.5 rounded-2xl font-black text-base bg-stone-900 text-white shadow-xs">
                                MEJA {{ $order->table->number ?? '-' }}
                            </span>
                            <div>
                                <span class="font-mono text-xs font-bold text-gray-700 block">{{ $order->order_number }}</span>
                                <span class="text-[11px] font-semibold text-gray-500">{{ $order->created_at->format('H:i') }} WIB</span>
                            </div>
                        </div>

                        <!-- Timer / Status Badge -->
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black
                                @if($isLate) bg-rose-600 text-white animate-pulse
                                @elseif($isWarning) bg-amber-500 text-white
                                @else bg-gray-100 text-gray-700
                                @endif">
                                ⏱ {{ $elapsedMinutes }}m lalu
                            </span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="p-5 flex-1 space-y-4">
                        <div class="space-y-3">
                            @foreach ($order->items as $item)
                                <div class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                                    <!-- Big Qty Badge -->
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-900 font-black text-sm flex items-center justify-center shrink-0 border border-amber-200">
                                        {{ $item->quantity }}x
                                    </div>

                                    <div class="flex-1">
                                        <div class="font-black text-gray-900 text-sm leading-tight">
                                            {{ $item->product_name }}
                                        </div>

                                        @if ($item->modifiers->isNotEmpty())
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach ($item->modifiers as $mod)
                                                    <span class="inline-block px-2 py-0.5 rounded-md bg-stone-100 text-stone-800 text-[11px] font-bold">
                                                        • {{ $mod->option_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($item->notes)
                                            <div class="mt-1 px-2 py-1 bg-amber-50 border border-amber-200/80 rounded-lg text-amber-900 text-[11px] font-bold">
                                                📝 "{{ $item->notes }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->notes)
                            <div class="p-3 bg-stone-100 border border-stone-200 rounded-xl text-xs text-stone-800">
                                <span class="font-bold">Catatan Umum:</span> "{{ $order->notes }}"
                            </div>
                        @endif
                    </div>

                    <!-- Action Button Area -->
                    <div class="p-4 pt-3 border-t border-gray-100 bg-gray-50/70 space-y-2">
                        <a
                            href="{{ route('staff.orders.receipt', $order) }}"
                            target="_blank"
                            rel="noopener"
                            class="w-full py-2 px-3 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
                            <span>🖨️ Cetak Struk</span>
                            @if ($order->receipt_printed_at)
                                <span class="text-[9px] text-emerald-600 font-black">✓ Terkirim ke bar</span>
                            @endif
                        </a>

                        @if ($order->order_status === \App\Enums\OrderStatus::Confirmed)
                            <button
                                type="button"
                                wire:click="startPreparing({{ $order->id }})"
                                class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-700 active:scale-[0.99] text-white font-black text-xs rounded-2xl shadow-md shadow-amber-900/10 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Mulai Buat Pesanan</span>
                            </button>
                        @elseif ($order->order_status === \App\Enums\OrderStatus::Preparing)
                            <button
                                type="button"
                                wire:click="markReady({{ $order->id }})"
                                class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-black text-xs rounded-2xl shadow-md shadow-blue-900/10 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Selesai Dibuat (Siap Diantar)</span>
                            </button>
                        @elseif ($order->order_status === \App\Enums\OrderStatus::Ready)
                            <button
                                type="button"
                                wire:click="markCompleted({{ $order->id }})"
                                class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-black text-xs rounded-2xl shadow-md shadow-emerald-900/10 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Tandai Selesai Disajikan</span>
                            </button>
                        @else
                            <div class="text-center py-2 text-xs font-bold text-gray-500">
                                ✓ Pesanan Selesai
                            </div>
                        @endif
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-gray-200">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-3">
                        ☕
                    </div>
                    <h3 class="text-base font-extrabold text-gray-900">Semua Pesanan Selesai Diracik!</h3>
                    <p class="text-xs text-gray-500 mt-1">Tidak ada antrean tertunda di bar saat ini. Pesanan baru akan otomatis muncul di sini.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
