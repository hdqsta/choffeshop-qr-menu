<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Order\OrderStateMachine;
use App\Services\Payment\PaymentManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePendingPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi status transaksi pembayaran tertunda dengan payment gateway provider';

    /**
     * Execute the console command.
     */
    public function handle(PaymentManager $paymentManager, OrderStateMachine $stateMachine): int
    {
        $pendingPayments = Payment::with('order')
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '>=', now()->subHours(2))
            ->get();

        $count = $pendingPayments->count();

        if ($count === 0) {
            $this->info('Tidak ada pembayaran pending yang perlu direkonsiliasi.');

            return self::SUCCESS;
        }

        $reconciled = 0;

        foreach ($pendingPayments as $payment) {
            try {
                $driver = $paymentManager->gateway($payment->gateway);
                $notification = $driver->fetchStatus($payment);

                if ($notification->status === PaymentStatus::Paid) {
                    $payment->update([
                        'status' => PaymentStatus::Paid,
                        'paid_at' => now(),
                    ]);

                    $stateMachine->markAsPaid($payment->order);
                    $reconciled++;

                    $this->info("Pembayaran {$payment->provider_ref} berhasil direkonsiliasi: LUNAS.");
                } elseif (in_array($notification->status, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                    $payment->update([
                        'status' => $notification->status,
                    ]);

                    $reconciled++;
                    $this->line("Pembayaran {$payment->provider_ref} berstatus: {$notification->status->value}.");
                }
            } catch (Exception $e) {
                Log::error("Gagal rekonsiliasi payment {$payment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai rekonsiliasi. {$reconciled} dari {$count} pembayaran diperbarui.");

        return self::SUCCESS;
    }
}
