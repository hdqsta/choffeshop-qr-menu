<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use DateTimeInterface;

class PaymentIntent
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public string $gateway,
        public int $amount,
        public PaymentStatus $status = PaymentStatus::Pending,
        public ?string $providerRef = null,
        public ?string $qrString = null,
        public ?string $qrUrl = null,
        public ?DateTimeInterface $expiresAt = null,
        public array $rawResponse = [],
    ) {}
}
