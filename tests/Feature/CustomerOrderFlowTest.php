<?php

namespace Tests\Feature;

use App\Livewire\Customer\Cart;
use App\Livewire\Customer\Menu;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\Table;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_menu_renders_available_products(): void
    {
        $table = Table::factory()->create(['number' => '01', 'is_active' => true]);
        $category = Category::factory()->create(['name' => 'Signature Coffee']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Es Kopi Gula Aren',
            'price' => 22000,
            'is_available' => true,
        ]);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
            'table_token' => $table->token,
        ]);

        Livewire::test(Menu::class)
            ->assertSee('Es Kopi Gula Aren')
            ->assertSee('Signature Coffee');
    }

    public function test_customer_can_customize_product_and_add_to_cart(): void
    {
        $table = Table::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['name' => 'Latte', 'price' => 25000]);

        $group = ModifierGroup::factory()->create([
            'name' => 'Ukuran',
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
        ]);

        $optRegular = ModifierOption::factory()->create([
            'modifier_group_id' => $group->id,
            'name' => 'Regular',
            'price_delta' => 0,
        ]);

        $optLarge = ModifierOption::factory()->create([
            'modifier_group_id' => $group->id,
            'name' => 'Large',
            'price_delta' => 5000,
        ]);

        $product->modifierGroups()->attach($group->id);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
        ]);

        Livewire::test(Menu::class)
            ->call('openProductModal', $product->id)
            ->set("selectedModifiers.{$group->id}", $optLarge->id)
            ->set('quantity', 2)
            ->set('notes', 'less ice please')
            ->call('addToCart')
            ->assertDispatched('cart-updated');

        $cartService = app(CartService::class);
        $this->assertSame(2, $cartService->count());
        // (25000 + 5000) * 2 = 60000
        $this->assertSame(60000, $cartService->total());

        $items = $cartService->get();
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertSame('Latte', $item['product_name']);
        $this->assertSame(30000, $item['unit_price']);
        $this->assertSame('less ice please', $item['notes']);
    }

    public function test_customer_cart_management_updates_quantities_and_removes(): void
    {
        $table = Table::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['name' => 'Croissant', 'price' => 20000]);

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
        ]);

        $cartService = app(CartService::class);
        $cartService->add($product, [], 1);

        $items = $cartService->get();
        $itemId = array_key_first($items);

        Livewire::test(Cart::class)
            ->assertSee('Croissant')
            ->call('increment', $itemId);

        $this->assertSame(2, $cartService->count());
        $this->assertSame(40000, $cartService->total());

        Livewire::test(Cart::class)
            ->call('decrement', $itemId);

        $this->assertSame(1, $cartService->count());

        Livewire::test(Cart::class)
            ->call('removeItem', $itemId);

        $this->assertSame(0, $cartService->count());
    }
}
