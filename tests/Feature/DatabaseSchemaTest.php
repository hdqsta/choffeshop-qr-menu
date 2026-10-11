<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\Table;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_foundational_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@coffeeshop.test',
            'role' => UserRole::Admin->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'kasir@coffeeshop.test',
            'role' => UserRole::Cashier->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'barista@coffeeshop.test',
            'role' => UserRole::Bar->value,
        ]);

        $this->assertDatabaseCount('tables', 10);
        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, ModifierGroup::count());
    }

    public function test_order_model_relationships_and_casts(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::PendingPayment,
            'total_amount' => 50000,
        ]);

        $product = Product::factory()->create([
            'price' => 25000,
        ]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'unit_price' => 25000,
            'quantity' => 2,
            'line_total' => 50000,
        ]);

        $modifier = OrderItemModifier::factory()->create([
            'order_item_id' => $item->id,
            'group_name' => 'Ukuran',
            'option_name' => 'Large',
            'price_delta' => 5000,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 50000,
            'status' => PaymentStatus::Pending,
        ]);

        $event = PaymentEvent::factory()->create([
            'payment_id' => $payment->id,
            'gateway' => 'manual',
            'dedupe_key' => 'test-dedupe-key-1',
        ]);

        $this->assertInstanceOf(Table::class, $order->table);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $item->modifiers);
        $this->assertCount(1, $order->payments);
        $this->assertCount(1, $payment->events);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertFalse($order->isPaid());
    }
}
