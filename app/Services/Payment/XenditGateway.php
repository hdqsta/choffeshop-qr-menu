<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class XenditGateway implements PaymentGateway
{
    protected string $secretKey;

    protected string $webhookToken;

    public function __construct()
    {
        $this->secretKey = (string) config('payment.xendit.secret_key', '');
        $this->webhookToken = (string) config('payment.xendit.webhook_token', '');
    }

    public function createPayment(Order $order): PaymentIntent
    {
        $providerRef = $order->order_number;
        $expiresAt = $order->expires_at ?? now()->addMinutes(config('payment.expiry_minutes', 15));

        if (empty($this->secretKey)) {
            $qrData = url("/order/{$order->public_token}");
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.urlencode($qrData);

            return new PaymentIntent(
                gateway: 'xendit',
                amount: $order->total_amount,
                status: PaymentStatus::Pending,
                providerRef: $providerRef,
                qrString: $qrData,
                qrUrl: $qrUrl,
                expiresAt: $expiresAt,
                rawResponse: ['mode' => 'sandbox_mock', 'reference_id' => $providerRef]
            );
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->timeout(10)
            ->post('https://api.xendit.co/qr_codes', [
                'reference_id' => $providerRef,
                'type' => 'DYNAMIC',
                'currency' => 'IDR',
                'amount' => $order->total_amount,
                'expires_at' => $expiresAt->toIso8601String(),
            ]);

        $body = $response->json() ?? [];
        $qrString = $body['qr_string'] ?? '';
        $qrUrl = $qrString ? 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.urlencode($qrString) : '';

        return new PaymentIntent(
            gateway: 'xendit',
            amount: $order->total_amount,
            status: PaymentStatus::Pending,
            providerRef: $body['id'] ?? $providerRef,
            qrString: $qrString,
            qrUrl: $qrUrl,
            expiresAt: $expiresAt,
            rawResponse: $body
        );
    }

    public function parseWebhook(Request $request): PaymentNotification
    {
        $token = (string) $request->header('x-callback-token', '');
        $signatureValid = ! empty($this->webhookToken) ? hash_equals($this->webhookToken, $token) : true;

        $qrData = $request->input('qr_code', []);
        $referenceId = (string) ($qrData['reference_id'] ?? $request->input('external_id', ''));
        $qrId = (string) ($qrData['id'] ?? $request->input('id', ''));
        $statusStr = (string) $request->input('status', '');
        $amount = (int) ($request->input('amount') ?? $qrData['amount'] ?? 0);

        $status = match (strtoupper($statusStr)) {
            'COMPLETED', 'SUCCEEDED' => PaymentStatus::Paid,
            'PENDING', 'ACTIVE' => PaymentStatus::Pending,
            'EXPIRED' => PaymentStatus::Expired,
            'FAILED' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };

        $dedupeKey = "xendit_{$qrId}_{$statusStr}";

        return new PaymentNotification(
            gateway: 'xendit',
            providerRef: $qrId ?: $referenceId,
            amount: $amount,
            status: $status,
            dedupeKey: $dedupeKey,
            payload: $request->all(),
            signatureValid: $signatureValid
        );
    }

    public function fetchStatus(Payment $payment): PaymentNotification
    {
        $ref = $payment->provider_ref;

        if (empty($this->secretKey)) {
            return new PaymentNotification(
                gateway: 'xendit',
                providerRef: $ref,
                amount: $payment->amount,
                status: $payment->status,
                dedupeKey: "xendit_fetch_{$ref}_{$payment->status->value}",
                payload: ['status' => $payment->status->value],
                signatureValid: true
            );
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->timeout(10)
            ->get("https://api.xendit.co/qr_codes/{$ref}");

        $body = $response->json() ?? [];
        $statusStr = strtoupper((string) ($body['status'] ?? ''));

        $status = match ($statusStr) {
            'COMPLETED', 'SUCCEEDED' => PaymentStatus::Paid,
            'EXPIRED' => PaymentStatus::Expired,
            'FAILED' => PaymentStatus::Failed,
            default => $payment->status,
        };

        return new PaymentNotification(
            gateway: 'xendit',
            providerRef: $ref,
            amount: (int) ($body['amount'] ?? $payment->amount),
            status: $status,
            dedupeKey: "xendit_fetch_{$ref}_{$status->value}",
            payload: $body,
            signatureValid: true
        );
    }
}
