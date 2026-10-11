<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentEvent>
 */
class PaymentEventFactory extends Factory
{
    protected $model = PaymentEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'gateway' => 'manual',
            'dedupe_key' => 'event_'.Str::random(24),
            'payload' => ['event' => 'payment.success', 'time' => now()->toIso8601String()],
            'signature_valid' => true,
            'processed_at' => now(),
        ];
    }
}
