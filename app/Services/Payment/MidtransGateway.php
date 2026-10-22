<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MidtransGateway implements PaymentGateway
{
    protected string $serverKey;

    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = (string) config('payment.midtrans.server_key', '');
        $this->isProduction = (bool) config('payment.midtrans.is_production', false);
    }

    protected function baseUrl(): string
    {
        return $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function createPayment(Order $order): PaymentIntent
    {
        $providerRef = $order->order_number;
        $expiresAt = $order->expires_at ?? now()->addMinutes(config('payment.expiry_minutes', 15));

        // If credentials are not provided (e.g. testing or local demo without Midtrans keys)
        if (empty($this->serverKey)) {
            $qrData = url("/order/{$order->public_token}");
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.urlencode($qrData);

            return new PaymentIntent(
                gateway: 'midtrans',
                amount: $order->total_amount,
                status: PaymentStatus::Pending,
                providerRef: $providerRef,
                qrString: $qrData,
                qrUrl: $qrUrl,
                expiresAt: $expiresAt,
                rawResponse: ['mode' => 'sandbox_mock', 'order_id' => $providerRef]
            );
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->timeout(10)
            ->post("{$this->baseUrl()}/charge", [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $providerRef,
                    'gross_amount' => $order->total_amount,
                ],
                'qris' => [
                    'acquirer' => 'gopay',
                ],
                'custom_expiry' => [
                    'expiry_duration' => config('payment.expiry_minutes', 15),
                    'unit' => 'minute',
                ],
            ]);

        $body = $response->json() ?? [];
        $qrString = $body['qr_string'] ?? '';
        $qrUrl = '';

        if (! empty($body['actions'])) {
            foreach ($body['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrUrl = $action['url'] ?? '';
                    break;
                }
            }
        }

        if (empty($qrUrl) && ! empty($qrString)) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.urlencode($qrString);
        }

        return new PaymentIntent(
            gateway: 'midtrans',
            amount: $order->total_amount,
            status: PaymentStatus::Pending,
            providerRef: $body['transaction_id'] ?? $providerRef,
            qrString: $qrString,
            qrUrl: $qrUrl,
            expiresAt: $expiresAt,
            rawResponse: $body
        );
    }

    public function parseWebhook(Request $request): PaymentNotification
    {
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '0');
        $signatureKey = (string) $request->input('signature_key', '');
        $transactionStatus = (string) $request->input('transaction_status', '');
        $fraudStatus = (string) $request->input('fraud_status', '');
        $transactionId = (string) $request->input('transaction_id', '');

        // Signature validation: SHA512(order_id + status_code + gross_amount + ServerKey).
        // Fail closed: an unconfigured server key would otherwise let anyone forge
        // a signature by hashing against an empty key.
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
        $signatureValid = $this->serverKey !== ''
            && hash_equals($expectedSignature, $signatureKey);

        // Map status
        $status = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'accept') ? PaymentStatus::Paid : PaymentStatus::Pending,
            'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'deny', 'cancel' => PaymentStatus::Failed,
            'expire' => PaymentStatus::Expired,
            'refund', 'partial_refund' => PaymentStatus::Refunded,
            default => PaymentStatus::Pending,
        };

        $dedupeKey = "midtrans_{$orderId}_{$transactionStatus}_{$statusCode}";

        return new PaymentNotification(
            gateway: 'midtrans',
            providerRef: $transactionId ?: $orderId,
            amount: (int) floatval($grossAmount),
            status: $status,
            dedupeKey: $dedupeKey,
            payload: $request->all(),
            signatureValid: $signatureValid
        );
    }

    public function fetchStatus(Payment $payment): PaymentNotification
    {
        $ref = $payment->provider_ref;

        if (empty($this->serverKey)) {
            return new PaymentNotification(
                gateway: 'midtrans',
                providerRef: $ref,
                amount: $payment->amount,
                status: $payment->status,
                dedupeKey: "midtrans_fetch_{$ref}_{$payment->status->value}",
                payload: ['status' => $payment->status->value],
                signatureValid: true
            );
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->timeout(10)
            ->get("{$this->baseUrl()}/{$ref}/status");

        $body = $response->json() ?? [];
        $transactionStatus = (string) ($body['transaction_status'] ?? '');

        $status = match ($transactionStatus) {
            'capture', 'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'expire' => PaymentStatus::Expired,
            'deny', 'cancel' => PaymentStatus::Failed,
            default => $payment->status,
        };

        return new PaymentNotification(
            gateway: 'midtrans',
            providerRef: $ref,
            amount: (int) floatval($body['gross_amount'] ?? $payment->amount),
            status: $status,
            dedupeKey: "midtrans_fetch_{$ref}_{$status->value}",
            payload: $body,
            signatureValid: true
        );
    }
}
