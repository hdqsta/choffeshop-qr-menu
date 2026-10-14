<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;

class PaymentNotification
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $gateway,
        public string $providerRef,
        public int $amount,
        public PaymentStatus $status,
        public string $dedupeKey,
        public array $payload = [],
        public bool $signatureValid = false,
    ) {}
}
