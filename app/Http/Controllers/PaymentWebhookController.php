<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Order\OrderStateMachine;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentWebhookController extends Controller
{
    /**
     * Gateways that genuinely push signed callbacks. Manual QRIS is deliberately
     * excluded: it settles through cashier confirmation, never through a webhook.
     *
     * @var list<string>
     */
    private const WEBHOOK_GATEWAYS = ['midtrans', 'xendit'];

    /**
     * Handle incoming payment gateway webhooks with signature check and idempotency deduplication.
     */
    public function handle(
        Request $request,
        string $gateway,
        PaymentManager $paymentManager,
        OrderStateMachine $stateMachine
    ): JsonResponse {
        if (! in_array($gateway, self::WEBHOOK_GATEWAYS, true)) {
            Log::warning("Rejected webhook for gateway without callback support [{$gateway}]", [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Unsupported gateway'], 404);
        }

        try {
            $driver = $paymentManager->gateway($gateway);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => 'Unsupported gateway'], 400);
        }

        $notification = $driver->parseWebhook($request);

        // Verify webhook authenticity
        if (! $notification->signatureValid) {
            Log::warning("Payment webhook signature invalid for [{$gateway}]", [
                'payload' => $notification->payload,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Idempotency check: has this event already been processed?
        $existingEvent = PaymentEvent::where('dedupe_key', $notification->dedupeKey)->first();
        if ($existingEvent) {
            return response()->json([
                'status' => 'already_processed',
                'dedupe_key' => $notification->dedupeKey,
            ], 200);
        }

        return DB::transaction(function () use ($notification, $stateMachine): JsonResponse {
            // Find payment by provider_ref or related order number
            $payment = Payment::where('provider_ref', $notification->providerRef)
                ->orWhereHas('order', function ($q) use ($notification): void {
                    $q->where('order_number', $notification->providerRef);
                })
                ->latest()
                ->first();

            // Record payment event for audit and strict idempotency
            PaymentEvent::create([
                'payment_id' => $payment?->id,
                'gateway' => $notification->gateway,
                'dedupe_key' => $notification->dedupeKey,
                'payload' => $notification->payload,
                'signature_valid' => true,
                'processed_at' => now(),
            ]);

            if (! $payment) {
                Log::warning("Payment not found for provider ref: {$notification->providerRef}");

                return response()->json(['status' => 'payment_not_found'], 404);
            }

            $order = $payment->order;

            // Handle PAID status
            if ($notification->status === PaymentStatus::Paid) {
                if ($payment->status !== PaymentStatus::Paid) {
                    $payment->update([
                        'status' => PaymentStatus::Paid,
                        'paid_at' => now(),
                    ]);
                }

                // If order was already cancelled (late payment), stateMachine flags needs_review = true
                $stateMachine->markAsPaid($order);
            } elseif (in_array($notification->status, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                $payment->update([
                    'status' => $notification->status,
                ]);

                // If unpaid and still pending payment, cancel order
                if ($order->payment_status !== PaymentStatus::Paid && $order->order_status === OrderStatus::PendingPayment) {
                    $stateMachine->cancelOrder($order, "Payment {$notification->status->value}");
                }
            }

            return response()->json(['status' => 'success'], 200);
        });
    }
}
