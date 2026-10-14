<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use Illuminate\Console\Command;

class ExpireUnpaidOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan pesanan yang belum dibayar setelah melewati batas waktu kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle(OrderStateMachine $stateMachine): int
    {
        $expiredOrders = Order::where('payment_status', PaymentStatus::Pending)
            ->where('order_status', OrderStatus::PendingPayment)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = $expiredOrders->count();

        if ($count === 0) {
            $this->info('Tidak ada pesanan kedaluwarsa.');

            return self::SUCCESS;
        }

        foreach ($expiredOrders as $order) {
            $stateMachine->cancelOrder($order, 'Waktu pembayaran telah kedaluwarsa');

            $order->payments()
                ->where('status', PaymentStatus::Pending)
                ->update(['status' => PaymentStatus::Expired]);

            $this->line("Pesanan {$order->order_number} telah dibatalkan (kedaluwarsa).");
        }

        $this->info("Berhasil membatalkan {$count} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}
