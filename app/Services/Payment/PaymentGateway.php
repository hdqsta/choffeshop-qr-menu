<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Create payment at provider and return QR, reference, and expiration.
     */
    public function createPayment(Order $order): PaymentIntent;

    /**
     * Verify authenticity of webhook request and transform to standardized DTO.
     */
    public function parseWebhook(Request $request): PaymentNotification;

    /**
     * Inquire status from provider for reconciliation.
     */
    public function fetchStatus(Payment $payment): PaymentNotification;
}
