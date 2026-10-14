<?php

namespace App\Services\Order;

use App\Models\ModifierOption;
use App\Models\Product;
use DomainException;

class OrderPricingService
{
    /**
     * Calculate order item totals and validate availability strictly from server data.
     *
     * @param  array<int, array<string, mixed>>  $cartItems
     * @return array{
     *     total_amount: int,
     *     items: array<int, array{
     *         product_id: int,
     *         product_name: string,
     *         unit_price: int,
     *         quantity: int,
     *         line_total: int,
     *         notes: ?string,
     *         modifiers: array<int, array{
     *             group_name: string,
     *             option_name: string,
     *             price_delta: int
     *         }>
     *     }>
     * }
     */
    public function calculate(array $cartItems): array
    {
        if (empty($cartItems)) {
            throw new DomainException('Keranjang belanja masih kosong.');
        }

        $totalAmount = 0;
        $processedItems = [];

        foreach ($cartItems as $cartItem) {
            $productId = (int) ($cartItem['product_id'] ?? 0);
            $quantity = max(1, (int) ($cartItem['quantity'] ?? 1));
            $notes = isset($cartItem['notes']) ? trim((string) $cartItem['notes']) : null;

            /** @var Product|null $product */
            $product = Product::find($productId);

            if (! $product) {
                throw new DomainException('Produk yang dipilih tidak ditemukan.');
            }

            if (! $product->is_available) {
                throw new DomainException("Produk '{$product->name}' sedang habis dan tidak dapat dipesan.");
            }

            // Extract modifier option ids
            $selectedOptionIds = [];
            if (! empty($cartItem['modifiers']) && is_array($cartItem['modifiers'])) {
                foreach ($cartItem['modifiers'] as $mod) {
                    if (is_array($mod) && isset($mod['option_id'])) {
                        $selectedOptionIds[] = (int) $mod['option_id'];
                    } elseif (is_numeric($mod)) {
                        $selectedOptionIds[] = (int) $mod;
                    }
                }
            }

            $options = ModifierOption::with('group')
                ->whereIn('id', $selectedOptionIds)
                ->get();

            $modifierSnapshots = [];
            $modifiersTotalDelta = 0;

            foreach ($options as $option) {
                if (! $option->is_available) {
                    throw new DomainException("Varian '{$option->name}' pada {$product->name} sedang tidak tersedia.");
                }

                $modifierSnapshots[] = [
                    'group_name' => $option->group->name,
                    'option_name' => $option->name,
                    'price_delta' => (int) $option->price_delta,
                ];

                $modifiersTotalDelta += (int) $option->price_delta;
            }

            $unitPrice = (int) $product->price + $modifiersTotalDelta;
            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;

            $processedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'notes' => $notes ?: null,
                'modifiers' => $modifierSnapshots,
            ];
        }

        return [
            'total_amount' => $totalAmount,
            'items' => $processedItems,
        ];
    }
}
