<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    protected array $drivers = [];

    public function gateway(?string $name = null): PaymentGateway
    {
        $name = $name ?: config('payment.default', 'manual');

        return $this->drivers[$name] ??= $this->resolve($name);
    }

    protected function resolve(string $name): PaymentGateway
    {
        return match ($name) {
            'manual' => new ManualQrisGateway,
            'midtrans' => new MidtransGateway,
            'xendit' => new XenditGateway,
            default => throw new InvalidArgumentException("Payment gateway driver [{$name}] is not supported."),
        };
    }
}
