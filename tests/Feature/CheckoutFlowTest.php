<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Customer\Checkout;
use App\Livewire\Customer\OrderStatus as OrderStatusComponent;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_empty_cart_redirects_to_menu(): void
    {
        $table = Table::factory()->create(['is_active' => true]);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
            'table_token' => $table->token,
        ]);

        Livewire::test(Checkout::class)
            ->assertRedirect(route('customer.menu'));
    }

    public function test_customer_can_place_order_and_flow_to_order_status(): void
    {
        $table = Table::factory()->create(['number' => '12', 'is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'price' => 20000,
            'is_available' => true,
        ]);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
            'table_token' => $table->token,
        ]);

        $cartService = app(CartService::class);
        $cartService->add($product, [], 2, 'Less ice');

        Livewire::test(Checkout::class)
            ->set('orderNotes', 'Tolong jangan terlalu manis')
            ->call('placeOrder')
            ->assertHasNoErrors();

        // Cart must be cleared
        $this->assertSame(0, $cartService->count());

        // Order created in DB
        $order = Order::where('table_id', $table->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertSame(40000, $order->total_amount);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame('Tolong jangan terlalu manis', $order->notes);

        // Check Items
        $this->assertCount(1, $order->items);
        $this->assertSame('Americano', $order->items->first()->product_name);
        $this->assertSame(2, $order->items->first()->quantity);

        // Check Payment intent
        $this->assertNotNull($order->latestPayment);
        $this->assertSame(40000, $order->latestPayment->amount);

        // Now test OrderStatus component
        Livewire::test(OrderStatusComponent::class, ['publicToken' => $order->public_token])
            ->assertSee($order->order_number)
            ->assertSee('Meja 12')
            ->assertSee('Menunggu Pembayaran')
            ->assertSee('Americano')
            ->call('simulatePayment')
            ->assertSee('Pesanan Diterima');

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
    }

    public function test_customer_is_redirected_to_the_order_status_route_after_checkout(): void
    {
        $table = Table::factory()->create(['number' => '15', 'is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 20000,
            'is_available' => true,
        ]);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
            'table_token' => $table->token,
        ]);

        app(CartService::class)->add($product, [], 1);

        $component = Livewire::test(Checkout::class)
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::where('table_id', $table->id)->latest()->firstOrFail();

        $component->assertRedirect(
            route('customer.order.status', ['publicToken' => $order->public_token])
        );
    }

    /**
     * Guards the Livewire mount() argument name against the route parameter name:
     * a mismatch only surfaces when the page is requested through the router.
     */
    public function test_order_status_page_is_reachable_through_its_public_route(): void
    {
        $table = Table::factory()->create(['number' => '21']);
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->get(route('customer.order.status', ['publicToken' => $order->public_token]));

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Meja 21');
        $response->assertSee('Menunggu Pembayaran');
    }

    public function test_order_status_route_returns_not_found_for_an_unknown_token(): void
    {
        $this->get(route('customer.order.status', ['publicToken' => 'token-yang-tidak-ada']))
            ->assertNotFound();
    }
}
