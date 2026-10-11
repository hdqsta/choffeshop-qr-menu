<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemModifier>
 */
class OrderItemModifierFactory extends Factory
{
    protected $model = OrderItemModifier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'group_name' => 'Level Gula',
            'option_name' => 'Normal Sugar (100%)',
            'price_delta' => 0,
        ];
    }
}
