<div class="px-4 py-5 flex flex-col gap-4" wire:poll.3s="checkPaymentStatus">
    @if (session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-3.5 rounded-2xl flex items-center gap-2.5 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Order Header Card -->
    <div class="bg-white rounded-2xl p-4 border border-stone-200/80 shadow-xs flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider">Nomor Pesanan</span>
            <span class="text-base font-extrabold text-stone-900 font-mono">{{ $order->order_number }}</span>
            <span class="text-[11px] text-stone-500 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
        </div>
        <div class="text-right">
            <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider block">Lokasi Meja</span>
            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-900 border border-amber-200 px-3 py-1 rounded-xl font-bold text-sm">
                🪑 Meja {{ $order->table->number ?? '-' }}
            </span>
        </div>
    </div>

    <!-- NEEDS REVIEW ALERT -->
    @if ($order->needs_review)
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 text-amber-950 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-amber-900">Perlu Verifikasi Kasir</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        Pembayaran Anda terdeteksi melewati batas waktu atau butuh konfirmasi manual. Barista/kasir kami sedang memverifikasi pesanan Anda. Silakan hubungi kasir jika memerlukan bantuan segera.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- CANCELLED / EXPIRED ALERT -->
    @if ($order->order_status === \App\Enums\OrderStatus::Cancelled && ! $order->needs_review)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-center shadow-xs">
            <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="font-extrabold text-stone-900 text-base">Pesanan Dibatalkan</h3>
            <p class="text-xs text-stone-500 mt-1 max-w-xs mx-auto">
                Waktu pembayaran telah kedaluwarsa atau pesanan telah dibatalkan oleh kasir.
            </p>
            <a href="{{ route('customer.menu') }}" wire:navigate class="inline-flex items-center justify-center gap-2 mt-4 px-5 py-2.5 bg-stone-900 text-white rounded-xl text-xs font-bold hover:bg-stone-800 transition">
                Kembali ke Menu
            </a>
        </div>
    @endif

    <!-- QRIS PAYMENT PENDING SECTION -->
    @if ($order->order_status === \App\Enums\OrderStatus::PendingPayment && ! $order->isExpired())
        <div class="bg-white rounded-3xl p-5 border border-stone-200/90 shadow-sm flex flex-col items-center text-center">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold mb-3">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                Menunggu Pembayaran
            </div>

            <p class="text-xs text-stone-500">Total Pembayaran</p>
            <h2 class="text-2xl font-black text-stone-900 mt-0.5 tracking-tight">
                {{ $order->formatted_total }}
            </h2>

            <!-- Countdown Timer -->
            @if ($order->expires_at)
                <div x-data="{
                    expiresAt: new Date('{{ $order->expires_at->toISOString() }}').getTime(),
                    timeLeft: '',
                    isExpired: false,
                    updateTimer() {
                        const now = new Date().getTime();
                        const diff = this.expiresAt - now;
                        if (diff <= 0) {
                            this.timeLeft = '00:00';
                            this.isExpired = true;
                            return;
                        }
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        this.timeLeft = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    }
                }" x-init="updateTimer(); setInterval(() => updateTimer(), 1000)" class="mt-2 flex items-center gap-1.5 text-xs text-stone-600 bg-stone-50 px-3 py-1 rounded-full border border-stone-200">
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Bayar sebelum:</span>
                    <span class="font-bold text-amber-700 font-mono" x-text="timeLeft">--:--</span>
                </div>
            @endif

            <!-- QR Code Card -->
            @php
                $payment = $order->latestPayment;
                $qrisImage = $payment?->qr_url;
                $isManualQris = $payment?->gateway === 'manual';
            @endphp

            <div class="mt-4 p-4 bg-stone-50 rounded-2xl border-2 border-stone-200/80 w-full max-w-xs flex flex-col items-center">
                <div class="flex items-center justify-between w-full mb-3 px-1">
                    <span class="text-xs font-black tracking-widest text-stone-800">QRIS</span>
                    <span class="text-[10px] text-stone-500 font-semibold">GPN • Standar Nasional</span>
                </div>

                @if ($qrisImage)
                    <div class="bg-white p-3 rounded-xl shadow-xs border border-stone-200/60">
                        <img src="{{ asset($qrisImage) }}" alt="QRIS {{ $isManualQris ? config('payment.manual.merchant_name') : $order->order_number }}" class="w-56 h-56 object-contain rounded-lg">
                    </div>
                @else
                    <div class="w-56 h-56 bg-stone-200 rounded-xl flex items-center justify-center text-stone-500 text-xs font-medium text-center px-4">
                        @if ($isManualQris)
                            QRIS statis merchant belum dikonfigurasi. Silakan bayar langsung di kasir.
                        @else
                            QRIS Tidak Tersedia
                        @endif
                    </div>
                @endif

                @if ($isManualQris)
                    <div class="w-full mt-3 p-3 bg-white rounded-xl border border-stone-200/80 text-left">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-stone-500">Merchant</span>
                            <span class="font-bold text-stone-900">{{ config('payment.manual.merchant_name') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] mt-1">
                            <span class="text-stone-500">Nominal yang harus diisi</span>
                            <span class="font-black text-amber-700 font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[10px] text-stone-500 mt-2 leading-snug">
                            QRIS statis tidak memuat nominal, jadi masukkan jumlah tersebut secara manual di aplikasi pembayaran Anda, lalu tekan "Cek Status Pembayaran" setelah kasir memverifikasi.
                        </p>
                    </div>
                @endif

                <p class="text-[11px] text-stone-500 mt-3 leading-tight">
                    Scan menggunakan BCA, GoPay, OVO, ShopeePay, DANA, atau aplikasi m-Banking Anda.
                </p>
            </div>

            <div class="mt-4 flex flex-col gap-2 w-full max-w-xs">
                <button type="button" wire:click="checkPaymentStatus" class="w-full py-2.5 px-4 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Cek Status Pembayaran</span>
                </button>

                <!-- Demo Simulation Button for Testing/Development only -->
                @if (app()->environment(['local', 'testing']))
                    <button type="button" wire:click="simulatePayment" wire:confirm="Simulasikan pembayaran sukses untuk pesanan ini?" class="w-full py-2 px-3 rounded-xl bg-amber-50 border border-amber-300/80 text-amber-900 font-bold text-[11px] hover:bg-amber-100 transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>⚡ Simulasi Bayar Berhasil (Demo Testing)</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- ACTIVE ORDER TIMELINE / STATUS (PAID & PROCESSING) -->
    @if ($order->payment_status === \App\Enums\PaymentStatus::Paid && $order->order_status !== \App\Enums\OrderStatus::Cancelled)
        <div class="bg-white rounded-3xl p-5 border border-stone-200/90 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">
                        ✓
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-stone-900">Status Pesanan</h2>
                        <span class="text-[11px] text-emerald-600 font-semibold">Pembayaran Lunas</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                    @if($order->order_status === \App\Enums\OrderStatus::Confirmed) bg-amber-100 text-amber-800
                    @elseif($order->order_status === \App\Enums\OrderStatus::Preparing) bg-blue-100 text-blue-800
                    @elseif($order->order_status === \App\Enums\OrderStatus::Ready) bg-emerald-100 text-emerald-800 animate-pulse
                    @elseif($order->order_status === \App\Enums\OrderStatus::Completed) bg-stone-100 text-stone-700
                    @endif">
                    {{ $order->order_status->label() }}
                </span>
            </div>

            <!-- Visual Progress Stepper -->
            @php
                $steps = [
                    ['key' => \App\Enums\OrderStatus::Confirmed, 'title' => 'Diterima', 'desc' => 'Masuk antrean bar'],
                    ['key' => \App\Enums\OrderStatus::Preparing, 'title' => 'Dirajik', 'desc' => 'Sedang dibuat barista'],
                    ['key' => \App\Enums\OrderStatus::Ready, 'title' => 'Siap', 'desc' => 'Diantar ke meja'],
                    ['key' => \App\Enums\OrderStatus::Completed, 'title' => 'Selesai', 'desc' => 'Selamat menikmati'],
                ];
                $statusOrder = [
                    \App\Enums\OrderStatus::Confirmed->value => 1,
                    \App\Enums\OrderStatus::Preparing->value => 2,
                    \App\Enums\OrderStatus::Ready->value => 3,
                    \App\Enums\OrderStatus::Completed->value => 4,
                ];
                $currentStepIndex = $statusOrder[$order->order_status->value] ?? 1;
            @endphp

            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-stone-200">
                @foreach ($steps as $index => $step)
                    @php
                        $stepNumber = $index + 1;
                        $isPast = $currentStepIndex > $stepNumber;
                        $isCurrent = $currentStepIndex === $stepNumber;
                        $isPending = $currentStepIndex < $stepNumber;
                    @endphp
                    <div class="relative flex items-start gap-3">
                        <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black transition-all
                            @if($isPast) bg-emerald-600 text-white ring-4 ring-emerald-50
                            @elseif($isCurrent) bg-amber-600 text-white ring-4 ring-amber-100 animate-pulse
                            @else bg-stone-200 text-stone-400
                            @endif">
                            @if($isPast)
                                ✓
                            @else
                                {{ $stepNumber }}
                            @endif
                        </div>
                        <div>
                            <h4 class="text-xs font-bold leading-none @if($isCurrent) text-amber-900 @elseif($isPast) text-stone-800 @else text-stone-400 @endif">
                                {{ $step['title'] }}
                            </h4>
                            <p class="text-[11px] text-stone-500 mt-0.5">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Highlight Banner Based on Status -->
            <div class="mt-5 p-3.5 rounded-2xl text-xs font-medium
                @if($order->order_status === \App\Enums\OrderStatus::Confirmed) bg-amber-50 text-amber-900 border border-amber-200
                @elseif($order->order_status === \App\Enums\OrderStatus::Preparing) bg-blue-50 text-blue-900 border border-blue-200
                @elseif($order->order_status === \App\Enums\OrderStatus::Ready) bg-emerald-50 text-emerald-950 border border-emerald-300 font-semibold
                @elseif($order->order_status === \App\Enums\OrderStatus::Completed) bg-stone-50 text-stone-800 border border-stone-200
                @endif">
                @if($order->order_status === \App\Enums\OrderStatus::Confirmed)
                    ⏳ Pesanan Anda sudah masuk antrean bar. Barista akan segera meracik pesanan Anda.
                @elseif($order->order_status === \App\Enums\OrderStatus::Preparing)
                    ☕ Barista kami sedang meracik kopi & makanan Anda. Mohon tunggu dengan nyaman ya!
                @elseif($order->order_status === \App\Enums\OrderStatus::Ready)
                    🎉 <strong>Pesanan Siap!</strong> Barista sedang mengantarkannya langsung ke <strong>Meja {{ $order->table->number }}</strong>.
                @elseif($order->order_status === \App\Enums\OrderStatus::Completed)
                    ✨ Pesanan selesai disajikan. Terima kasih telah memesan, selamat menikmati hari Anda!
                @endif
            </div>
        </div>
    @endif

    <!-- ORDER ITEMS SUMMARY CARD -->
    <div class="bg-white rounded-3xl p-5 border border-stone-200/90 shadow-sm flex flex-col gap-3">
        <h3 class="text-xs font-extrabold text-stone-400 uppercase tracking-wider">Rincian Menu</h3>

        <div class="divide-y divide-stone-100">
            @foreach ($order->items as $item)
                <div class="py-3 flex items-start justify-between gap-3 first:pt-0 last:pb-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-stone-800 text-xs">{{ $item->quantity }}x</span>
                            <span class="font-bold text-stone-900 text-sm leading-tight">{{ $item->product_name }}</span>
                        </div>

                        @if ($item->modifiers->isNotEmpty())
                            <div class="pl-5 mt-1 flex flex-wrap gap-1 text-[11px] text-stone-500">
                                @foreach ($item->modifiers as $mod)
                                    <span class="inline-block bg-stone-100 px-2 py-0.5 rounded-md text-stone-600">
                                        {{ $mod->option_name }}
                                        @if ($mod->price_delta > 0)
                                            (+Rp {{ number_format($mod->price_delta, 0, ',', '.') }})
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($item->notes)
                            <p class="pl-5 text-[11px] text-amber-700 italic mt-1">
                                Catatan: "{{ $item->notes }}"
                            </p>
                        @endif
                    </div>

                    <span class="font-bold text-stone-900 text-xs font-mono">
                        Rp {{ number_format($item->line_total, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </div>

        @if ($order->notes)
            <div class="mt-2 p-2.5 rounded-xl bg-stone-50 border border-stone-200/60 text-xs text-stone-600">
                <span class="font-bold text-stone-700">Catatan Pesanan:</span> {{ $order->notes }}
            </div>
        @endif

        <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
            <span class="text-xs font-bold text-stone-600">Total Pembayaran</span>
            <span class="text-base font-black text-stone-900 font-mono">{{ $order->formatted_total }}</span>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="pt-2 flex flex-col gap-2">
        <a href="{{ route('customer.menu') }}" wire:navigate class="w-full py-3 px-4 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs text-center transition flex items-center justify-center gap-2">
            <span>Tambah Menu Lain / Pesan Lagi</span>
            <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
