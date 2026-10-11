<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_token' => Str::random(32),
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'table_id' => Table::factory(),
            'notes' => fake()->optional()->sentence(),
            'total_amount' => fake()->numberBetween(20, 100) * 1000,
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::PendingPayment,
            'needs_review' => false,
            'expires_at' => now()->addMinutes(15),
            'paid_at' => null,
            'receipt_printed_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'order_status' => OrderStatus::Confirmed,
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Expired,
        ]);
    }

    public function needsReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'needs_review' => true,
        ]);
    }
}
