<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Livewire\Staff\CashierBoard;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashierBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_and_admin_can_access_cashier_board(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        $this->actingAs($cashier)
            ->get(route('staff.cashier'))
            ->assertOk();

        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        $this->actingAs($barista)
            ->get(route('staff.cashier'))
            ->assertForbidden();
    }

    public function test_barista_cannot_invoke_cashier_board_actions_directly(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        Livewire::actingAs($barista)
            ->test(CashierBoard::class)
            ->assertForbidden();
    }

    public function test_cashier_can_manually_confirm_pending_payment(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '05']);

        $order = Order::factory()->create([
            'table_id' => $table->id,
            'total_amount' => 50000,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'manual',
            'amount' => 50000,
            'status' => PaymentStatus::Pending,
        ]);

        $this->actingAs($cashier);

        Livewire::test(CashierBoard::class)
            ->assertSee($order->order_number)
            ->assertSee('Meja 05')
            ->call('confirmPayment', $order->id)
            ->assertHasNoErrors();

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertNotNull($order->paid_at);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
    }

    public function test_cashier_can_cancel_unpaid_order(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '02']);

        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $this->actingAs($cashier);

        Livewire::test(CashierBoard::class)
            ->call('cancelOrder', $order->id);

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
    }

    public function test_cashier_can_resolve_review_order(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);
        $table = Table::factory()->create();

        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Paid,
            'needs_review' => true,
        ]);

        $this->actingAs($cashier);

        Livewire::test(CashierBoard::class)
            ->call('resolveReview', $order->id);

        $order->refresh();
        $this->assertFalse($order->needs_review);
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
    }

    public function test_cashier_can_view_thermal_receipt_and_updates_printed_timestamp(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '07']);

        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'receipt_printed_at' => null,
        ]);

        $this->actingAs($cashier)
            ->get(route('staff.orders.receipt', $order))
            ->assertOk()
            ->assertSee('KEDAI KOPI KITA')
            ->assertSee($order->order_number)
            ->assertSeeText('Meja: 07');

        $order->refresh();
        $this->assertNotNull($order->receipt_printed_at);
    }
}
