<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireUnpaidOrdersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_expires_unpaid_orders_past_expiration_time(): void
    {
        $table = Table::factory()->create();

        // Expired order (expired 5 minutes ago)
        $expiredOrder = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'expires_at' => now()->subMinutes(5),
        ]);

        $expiredPayment = Payment::create([
            'order_id' => $expiredOrder->id,
            'gateway' => 'manual',
            'amount' => 25000,
            'status' => PaymentStatus::Pending,
        ]);

        // Fresh active order (expires in 10 minutes)
        $freshOrder = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->artisan('orders:expire-unpaid')
            ->expectsOutputToContain('Berhasil membatalkan 1 pesanan kedaluwarsa.')
            ->assertSuccessful();

        $expiredOrder->refresh();
        $this->assertSame(OrderStatus::Cancelled, $expiredOrder->order_status);
        $this->assertSame(PaymentStatus::Expired, $expiredOrder->payment_status);

        $expiredPayment->refresh();
        $this->assertSame(PaymentStatus::Expired, $expiredPayment->status);

        // Fresh order must remain unaffected
        $freshOrder->refresh();
        $this->assertSame(OrderStatus::PendingPayment, $freshOrder->order_status);
        $this->assertSame(PaymentStatus::Pending, $freshOrder->payment_status);
    }
}
