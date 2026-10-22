<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Livewire\Staff\BarBoard;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BarBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_barista_and_admin_can_access_bar_board(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        $this->actingAs($barista)
            ->get(route('staff.bar'))
            ->assertOk();

        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        $this->actingAs($cashier)
            ->get(route('staff.bar'))
            ->assertForbidden();
    }

    public function test_bar_board_never_displays_unpaid_orders(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '99']);

        // Unpaid Order
        $unpaidOrder = Order::factory()->create([
            'order_number' => 'ORD-UNPAID-999',
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        // Paid Order
        $paidOrder = Order::factory()->create([
            'order_number' => 'ORD-PAID-888',
            'table_id' => $table->id,
            'order_status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        OrderItem::create([
            'order_id' => $paidOrder->id,
            'product_name' => 'Caramel Latte',
            'unit_price' => 30000,
            'quantity' => 1,
            'line_total' => 30000,
        ]);

        $this->actingAs($barista);

        Livewire::test(BarBoard::class)
            ->assertSee('ORD-PAID-888')
            ->assertSee('Caramel Latte')
            ->assertDontSee('ORD-UNPAID-999');
    }

    public function test_barista_can_advance_order_workflow_statuses(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '03']);

        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($barista);

        // 1. Confirmed -> Preparing
        Livewire::test(BarBoard::class)
            ->call('startPreparing', $order->id);

        $order->refresh();
        $this->assertSame(OrderStatus::Preparing, $order->order_status);

        // 2. Preparing -> Ready
        Livewire::test(BarBoard::class)
            ->call('markReady', $order->id);

        $order->refresh();
        $this->assertSame(OrderStatus::Ready, $order->order_status);

        // 3. Ready -> Completed
        Livewire::test(BarBoard::class)
            ->call('markCompleted', $order->id);

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->order_status);
    }

    public function test_cashier_cannot_invoke_bar_board_actions_directly(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        Livewire::actingAs($cashier)
            ->test(BarBoard::class)
            ->assertForbidden();
    }

    public function test_barista_can_print_a_receipt_from_the_bar_board(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '21']);
        $order = Order::factory()->paid()->create(['table_id' => $table->id]);

        $this->actingAs($barista)
            ->get(route('staff.orders.receipt', $order))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->assertNotNull($order->fresh()->receipt_printed_at);
    }

    public function test_waiter_cannot_print_a_receipt(): void
    {
        $waiter = User::factory()->create(['role' => UserRole::Waiter, 'is_active' => true]);
        $order = Order::factory()->paid()->create();

        $this->actingAs($waiter)
            ->get(route('staff.orders.receipt', $order))
            ->assertForbidden();
    }

    public function test_bar_board_lists_unprinted_paid_orders_for_automatic_printing(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);
        $table = Table::factory()->create(['number' => '31']);

        $pendingPrint = Order::factory()->paid()->create([
            'table_id' => $table->id,
            'receipt_printed_at' => null,
        ]);

        $alreadyPrinted = Order::factory()->paid()->create([
            'table_id' => $table->id,
            'receipt_printed_at' => now(),
        ]);

        $unpaid = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $this->actingAs($barista);

        $receipts = Livewire::test(BarBoard::class)
            ->call('pollBarQueue')
            ->assertDispatched('bar-receipts-pending')
            ->instance()
            ->pendingReceipts();

        $this->assertSame([$pendingPrint->id], array_column($receipts, 'id'));
        $this->assertSame(route('staff.orders.receipt', $pendingPrint), $receipts[0]['url']);
        $this->assertSame('31', $receipts[0]['table']);

        $this->assertNotContains($alreadyPrinted->id, array_column($receipts, 'id'));
        $this->assertNotContains($unpaid->id, array_column($receipts, 'id'));
    }

    public function test_bar_board_renders_a_print_trigger_for_each_paid_ticket(): void
    {
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);
        $order = Order::factory()->paid()->create();

        $this->actingAs($barista);

        Livewire::test(BarBoard::class)
            ->assertSee(route('staff.orders.receipt', $order))
            ->assertSee('Cetak Struk');
    }
}
