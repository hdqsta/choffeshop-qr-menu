<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Table;
use App\Services\Order\OrderStateMachine;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_as_paid_transitions_pending_order_to_confirmed(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'needs_review' => false,
        ]);

        $stateMachine = app(OrderStateMachine::class);
        $stateMachine->markAsPaid($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertNotNull($order->paid_at);
        $this->assertFalse($order->needs_review);
    }

    public function test_mark_as_paid_on_cancelled_order_flags_needs_review_without_resurrecting(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Expired,
            'needs_review' => false,
        ]);

        $stateMachine = app(OrderStateMachine::class);
        $stateMachine->markAsPaid($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        // Order status must STAY Cancelled
        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
        // needs_review must be set to TRUE so staff can resolve
        $this->assertTrue($order->needs_review);
    }

    public function test_valid_sequential_order_status_transitions(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $stateMachine = app(OrderStateMachine::class);

        // Confirmed -> Preparing
        $stateMachine->transitionOrderStatus($order, OrderStatus::Preparing);
        $this->assertSame(OrderStatus::Preparing, $order->refresh()->order_status);

        // Preparing -> Ready
        $stateMachine->transitionOrderStatus($order, OrderStatus::Ready);
        $this->assertSame(OrderStatus::Ready, $order->refresh()->order_status);

        // Ready -> Completed
        $stateMachine->transitionOrderStatus($order, OrderStatus::Completed);
        $this->assertSame(OrderStatus::Completed, $order->refresh()->order_status);
    }

    public function test_illegal_status_transition_throws_domain_exception(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Preparing,
        ]);

        $stateMachine = app(OrderStateMachine::class);

        $this->expectException(DomainException::class);
        // Cannot jump directly from Preparing to Completed without being Ready
        $stateMachine->transitionOrderStatus($order, OrderStatus::Completed);
    }

    public function test_cancel_order_marks_cancelled_and_payment_expired(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $stateMachine = app(OrderStateMachine::class);
        $stateMachine->cancelOrder($order, 'Time expired');

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
        $this->assertSame(PaymentStatus::Expired, $order->payment_status);
    }

    public function test_cannot_cancel_already_paid_order(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $stateMachine = app(OrderStateMachine::class);

        $this->expectException(DomainException::class);
        $stateMachine->cancelOrder($order);
    }
}
