<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class ManualQrisGateway implements PaymentGateway
{
    /**
     * Manual mode shows the merchant's static QRIS image. Because a static QRIS
     * carries no nominal, the customer screen also shows the exact amount to key
     * into their banking app, and the cashier confirms settlement afterwards.
     */
    public function createPayment(Order $order): PaymentIntent
    {
        $providerRef = 'MAN-'.$order->order_number.'-'.time();
        $expiresAt = $order->expires_at ?? now()->addMinutes(config('payment.expiry_minutes', 15));

        return new PaymentIntent(
            gateway: 'manual',
            amount: $order->total_amount,
            status: PaymentStatus::Pending,
            providerRef: $providerRef,
            qrString: null,
            qrUrl: (string) config('payment.manual.qris_image', ''),
            expiresAt: $expiresAt,
            rawResponse: [
                'mode' => 'manual_cashier_confirmation',
                'merchant_name' => config('payment.manual.merchant_name'),
            ]
        );
    }

    /**
     * Manual QRIS has no provider and therefore no signed callback. The cashier
     * confirms settlement from the cashier board, so any request claiming to be
     * a manual webhook is untrusted by definition.
     */
    public function parseWebhook(Request $request): PaymentNotification
    {
        return new PaymentNotification(
            gateway: 'manual',
            providerRef: (string) $request->input('order_id', ''),
            amount: 0,
            status: PaymentStatus::Pending,
            dedupeKey: 'manual_rejected_'.$request->ip(),
            payload: $request->all(),
            signatureValid: false
        );
    }

    /**
     * Internal status inquiry used by reconciliation. There is no remote provider
     * to ask, so the locally recorded status is authoritative.
     */
    public function fetchStatus(Payment $payment): PaymentNotification
    {
        return new PaymentNotification(
            gateway: 'manual',
            providerRef: (string) $payment->provider_ref,
            amount: (int) $payment->amount,
            status: $payment->status,
            dedupeKey: 'manual_fetch_'.$payment->provider_ref.'_'.$payment->status->value,
            payload: ['status' => $payment->status->value],
            signatureValid: true
        );
    }
}
