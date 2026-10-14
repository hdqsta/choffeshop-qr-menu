<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class ManualQrisGateway implements PaymentGateway
{
    public function createPayment(Order $order): PaymentIntent
    {
        $providerRef = 'MAN-'.$order->order_number.'-'.time();
        $expiresAt = $order->expires_at ?? now()->addMinutes(config('payment.expiry_minutes', 15));

        // Use static merchant QRIS or generated QR placeholder with order info
        $qrisData = url('/order/'.$order->public_token);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.urlencode($qrisData);

        return new PaymentIntent(
            gateway: 'manual',
            amount: $order->total_amount,
            status: PaymentStatus::Pending,
            providerRef: $providerRef,
            qrString: $qrisData,
            qrUrl: $qrUrl,
            expiresAt: $expiresAt,
            rawResponse: ['mode' => 'manual_cashier_confirmation']
        );
    }

    public function parseWebhook(Request $request): PaymentNotification
    {
        $ref = (string) $request->input('order_id', '');
        $statusStr = (string) $request->input('status', 'paid');
        $amount = (int) $request->input('amount', 0);

        return new PaymentNotification(
            gateway: 'manual',
            providerRef: $ref,
            amount: $amount,
            status: $statusStr === 'paid' ? PaymentStatus::Paid : PaymentStatus::Failed,
            dedupeKey: 'manual_'.$ref.'_'.$statusStr,
            payload: $request->all(),
            signatureValid: true
        );
    }

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
