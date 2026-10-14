<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Services\Order\OrderPricingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_order_pricing_accurately_with_modifiers(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Caramel Macchiato',
            'price' => 30000,
            'is_available' => true,
        ]);

        $group = ModifierGroup::factory()->create(['name' => 'Size', 'is_required' => true]);
        $optLarge = ModifierOption::factory()->create([
            'modifier_group_id' => $group->id,
            'name' => 'Large (+5k)',
            'price_delta' => 5000,
        ]);
        $product->modifierGroups()->attach($group->id);

        $cartItems = [
            'item_1' => [
                'product_id' => $product->id,
                'quantity' => 2,
                'notes' => 'Extra hot',
                'modifiers' => [
                    [
                        'group_id' => $group->id,
                        'option_id' => $optLarge->id,
                    ],
                ],
            ],
        ];

        $pricingService = app(OrderPricingService::class);
        $result = $pricingService->calculate($cartItems);

        // Unit price = 30000 + 5000 = 35000
        // Line total = 35000 * 2 = 70000
        $this->assertSame(70000, $result['total_amount']);
        $this->assertCount(1, $result['items']);

        $item = $result['items'][0];
        $this->assertSame('Caramel Macchiato', $item['product_name']);
        $this->assertSame(35000, $item['unit_price']);
        $this->assertSame(2, $item['quantity']);
        $this->assertSame(70000, $item['line_total']);
        $this->assertSame('Extra hot', $item['notes']);
        $this->assertCount(1, $item['modifiers']);
        $this->assertSame('Size', $item['modifiers'][0]['group_name']);
        $this->assertSame('Large (+5k)', $item['modifiers'][0]['option_name']);
        $this->assertSame(5000, $item['modifiers'][0]['price_delta']);
    }

    public function test_throws_exception_if_product_becomes_unavailable(): void
    {
        $product = Product::factory()->create([
            'name' => 'Pastry Croissant',
            'price' => 20000,
            'is_available' => false, // Out of stock!
        ]);

        $cartItems = [
            'item_1' => [
                'product_id' => $product->id,
                'quantity' => 1,
                'modifiers' => [],
                'notes' => null,
            ],
        ];

        $pricingService = app(OrderPricingService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Produk 'Pastry Croissant' sedang habis dan tidak dapat dipesan.");

        $pricingService->calculate($cartItems);
    }
}
