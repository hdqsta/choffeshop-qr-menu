<div class="py-6" wire:poll.5s="pollOrders">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Live Status -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <span>💵 Meja Kasir & Monitor Pesanan</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Update
                    </span>
                </h1>
                <p class="text-xs text-gray-500 mt-1">Kelola konfirmasi pembayaran, cetak struk kasir, dan penanganan review pesanan.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('staff.bar') }}" wire:navigate class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span>☕ Buka Monitor Bar</span>
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

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-xs font-semibold text-gray-500">Omzet Hari Ini</span>
                <div class="text-xl font-black text-emerald-700 mt-1 font-mono">
                    Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-gray-400 mt-0.5 block">{{ $stats['paid_today_count'] }} pesanan lunas</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-xs font-semibold text-gray-500">Menunggu Bayar</span>
                <div class="text-xl font-black text-amber-600 mt-1 font-mono">
                    {{ $stats['pending_count'] }}
                </div>
                <span class="text-[11px] text-amber-600/80 mt-0.5 block">Perlu konfirmasi/QRIS</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs @if($stats['review_count'] > 0) border-rose-300 bg-rose-50/50 @endif">
                <span class="text-xs font-semibold @if($stats['review_count'] > 0) text-rose-700 font-bold @else text-gray-500 @endif">
                    Perlu Review Kasir
                </span>
                <div class="text-xl font-black @if($stats['review_count'] > 0) text-rose-600 animate-pulse @else text-gray-700 @endif mt-1 font-mono">
                    {{ $stats['review_count'] }}
                </div>
                <span class="text-[11px] text-gray-400 mt-0.5 block">Pembayaran telat/anomali</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-xs font-semibold text-gray-500">Lunas Hari Ini</span>
                <div class="text-xl font-black text-gray-900 mt-1 font-mono">
                    {{ $stats['paid_today_count'] }}
                </div>
                <span class="text-[11px] text-emerald-600 mt-0.5 block">Diteruskan ke Bar</span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <button type="button" wire:click="setTab('active')" class="px-3.5 py-2 rounded-xl transition {{ $tab === 'active' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua Aktif
                </button>
                <button type="button" wire:click="setTab('needs_review')" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $tab === 'needs_review' ? 'bg-rose-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>Perlu Review</span>
                    @if ($stats['review_count'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white text-rose-700 font-black">{{ $stats['review_count'] }}</span>
                    @endif
                </button>
                <button type="button" wire:click="setTab('pending')" class="px-3.5 py-2 rounded-xl transition {{ $tab === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Menunggu Bayar
                </button>
                <button type="button" wire:click="setTab('paid')" class="px-3.5 py-2 rounded-xl transition {{ $tab === 'paid' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Lunas / Di Bar
                </button>
                <button type="button" wire:click="setTab('history')" class="px-3.5 py-2 rounded-xl transition {{ $tab === 'history' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Riwayat / Selesai
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full md:w-64">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari no pesanan / meja..."
                    class="w-full text-xs pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-amber-500 focus:border-amber-500 text-gray-800 placeholder-gray-400">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Orders Cards Grid / List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($orders as $order)
                <div class="bg-white rounded-2xl border transition shadow-xs flex flex-col justify-between overflow-hidden
                    @if($order->needs_review) border-rose-400 ring-2 ring-rose-200
                    @elseif($order->isPaid()) border-emerald-200
                    @else border-gray-200
                    @endif">

                    <!-- Card Header -->
                    <div class="p-4 border-b border-gray-100 flex items-start justify-between gap-2
                        @if($order->needs_review) bg-rose-50/70
                        @elseif($order->isPaid()) bg-emerald-50/40
                        @else bg-gray-50/60
                        @endif">

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-xl font-black text-xs bg-amber-900 text-white shadow-xs">
                                    Meja {{ $order->table->number ?? '-' }}
                                </span>
                                <span class="font-mono text-xs font-extrabold text-gray-800">
                                    {{ $order->order_number }}
                                </span>
                            </div>
                            <span class="text-[11px] text-gray-500 mt-1 block">
                                {{ $order->created_at->diffForHumans() }} ({{ $order->created_at->format('H:i') }})
                            </span>
                        </div>

                        <!-- Status Badges -->
                        <div class="text-right flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                @if($order->payment_status === \App\Enums\PaymentStatus::Paid) bg-emerald-100 text-emerald-800
                                @elseif($order->payment_status === \App\Enums\PaymentStatus::Pending) bg-amber-100 text-amber-800
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ $order->payment_status->label() }}
                            </span>

                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                @if($order->order_status === \App\Enums\OrderStatus::Confirmed) bg-blue-100 text-blue-800
                                @elseif($order->order_status === \App\Enums\OrderStatus::Preparing) bg-indigo-100 text-indigo-800
                                @elseif($order->order_status === \App\Enums\OrderStatus::Ready) bg-emerald-100 text-emerald-800
                                @elseif($order->order_status === \App\Enums\OrderStatus::Completed) bg-gray-100 text-gray-700
                                @elseif($order->order_status === \App\Enums\OrderStatus::Cancelled) bg-rose-100 text-rose-800
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ $order->order_status->label() }}
                            </span>
                        </div>
                    </div>

                    <!-- Needs Review Banner -->
                    @if ($order->needs_review)
                        <div class="px-4 py-2 bg-rose-100 text-rose-900 text-xs font-bold flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                ⚠️ Perlu Review Kasir (Bayar setelah kedaluwarsa)
                            </span>
                            <button type="button" wire:click="resolveReview({{ $order->id }})" class="px-2 py-1 bg-rose-700 hover:bg-rose-800 text-white rounded-lg text-[10px] font-extrabold shadow-xs">
                                Setujui & Kirim ke Bar
                            </button>
                        </div>
                    @endif

                    <!-- Card Body: Item List -->
                    <div class="p-4 flex-1 space-y-2 text-xs">
                        <div class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <div class="py-1.5 flex items-start justify-between gap-2 first:pt-0 last:pb-0">
                                    <div>
                                        <div class="font-bold text-gray-900">
                                            <span class="text-amber-700 font-extrabold">{{ $item->quantity }}x</span> {{ $item->product_name }}
                                        </div>
                                        @if ($item->modifiers->isNotEmpty())
                                            <div class="text-[11px] text-gray-500 pl-4">
                                                {{ $item->modifiers->pluck('option_name')->join(', ') }}
                                            </div>
                                        @endif
                                        @if ($item->notes)
                                            <div class="text-[10px] text-amber-700 italic pl-4">
                                                "{{ $item->notes }}"
                                            </div>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-gray-700 font-mono">
                                        Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->notes)
                            <div class="mt-2 p-2 rounded-xl bg-gray-50 text-[11px] text-gray-600 border border-gray-100">
                                <strong>Catatan Meja:</strong> {{ $order->notes }}
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer: Total & Actions -->
                    <div class="p-4 pt-3 border-t border-gray-100 bg-gray-50/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-500">Total Pembayaran</span>
                            <span class="text-base font-black text-gray-900 font-mono">{{ $order->formatted_total }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Confirm Payment Button (for Unpaid) -->
                            @if ($order->payment_status === \App\Enums\PaymentStatus::Pending && $order->order_status !== \App\Enums\OrderStatus::Cancelled)
                                <button
                                    type="button"
                                    wire:click="confirmPayment({{ $order->id }})"
                                    wire:confirm="Konfirmasi pembayaran LUNAS untuk pesanan {{ $order->order_number }}?"
                                    class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition text-center flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Konfirmasi Lunas</span>
                                </button>
                            @endif

                            <!-- Print Receipt Button -->
                            <a
                                href="{{ route('staff.orders.receipt', $order) }}"
                                target="_blank"
                                class="py-2 px-3 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 font-bold text-xs rounded-xl transition flex items-center gap-1 shadow-xs">
                                <span>🖨️ Struk</span>
                                @if ($order->receipt_printed_at)
                                    <span class="text-[9px] text-emerald-600 font-black">✓</span>
                                @endif
                            </a>

                            <!-- Cancel Order Button (Unpaid Only) -->
                            @if (! $order->isPaid() && $order->order_status !== \App\Enums\OrderStatus::Cancelled)
                                <button
                                    type="button"
                                    wire:click="cancelOrder({{ $order->id }})"
                                    wire:confirm="Batalkan pesanan {{ $order->order_number }}?"
                                    class="py-2 px-2.5 text-rose-600 hover:bg-rose-50 font-bold text-xs rounded-xl transition">
                                    ✕ Batal
                                </button>
                            @endif

                            <!-- Mark Completed (if Ready) -->
                            @if ($order->order_status === \App\Enums\OrderStatus::Ready)
                                <button
                                    type="button"
                                    wire:click="markCompleted({{ $order->id }})"
                                    class="flex-1 py-2 px-3 bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                    ✓ Selesai Disajikan
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-gray-200">
                    <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-xl mb-3">
                        📋
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Tidak ada pesanan ditemukan</h3>
                    <p class="text-xs text-gray-500 mt-1">Pesanan masuk akan otomatis muncul di sini.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
