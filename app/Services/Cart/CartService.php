<?php

namespace App\Services\Cart;

use App\Models\ModifierOption;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'customer_cart';

    /**
     * Get all cart items.
     *
     * @return array<string, array<string, mixed>>
     */
    public function get(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Get total quantity of items in cart.
     */
    public function count(): int
    {
        $items = $this->get();

        return array_sum(array_column($items, 'quantity'));
    }

    /**
     * Get grand total price of cart.
     */
    public function total(): int
    {
        $items = $this->get();

        return array_sum(array_column($items, 'line_total'));
    }

    /**
     * Add a product with modifiers to the cart.
     *
     * @param  array<int>  $selectedOptionIds
     */
    public function add(Product $product, array $selectedOptionIds = [], int $quantity = 1, ?string $notes = null): void
    {
        $items = $this->get();

        // Load and format modifier options
        $options = ModifierOption::with('group')
            ->whereIn('id', $selectedOptionIds)
            ->where('is_available', true)
            ->get();

        $modifiers = [];
        $modifierTotal = 0;

        foreach ($options as $option) {
            $modifiers[] = [
                'group_id' => $option->modifier_group_id,
                'group_name' => $option->group->name,
                'option_id' => $option->id,
                'option_name' => $option->name,
                'price_delta' => (int) $option->price_delta,
            ];
            $modifierTotal += (int) $option->price_delta;
        }

        // Sort modifiers by option_id for deterministic hashing
        usort($modifiers, fn ($a, $b) => $a['option_id'] <=> $b['option_id']);

        $cleanNotes = trim($notes ?? '');
        $unitPrice = (int) $product->price + $modifierTotal;

        // Generate unique cart item id
        $hashData = $product->id.'_'.json_encode(array_column($modifiers, 'option_id')).'_'.strtolower($cleanNotes);
        $cartItemId = md5($hashData);

        if (isset($items[$cartItemId])) {
            $items[$cartItemId]['quantity'] += $quantity;
            $items[$cartItemId]['line_total'] = $items[$cartItemId]['unit_price'] * $items[$cartItemId]['quantity'];
        } else {
            $items[$cartItemId] = [
                'id' => $cartItemId,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => (int) $product->price,
                'product_image' => $product->image,
                'quantity' => $quantity,
                'modifiers' => $modifiers,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
                'notes' => $cleanNotes ?: null,
            ];
        }

        Session::put(self::SESSION_KEY, $items);
    }

    /**
     * Update item quantity in cart.
     */
    public function updateQuantity(string $cartItemId, int $quantity): void
    {
        $items = $this->get();

        if (! isset($items[$cartItemId])) {
            return;
        }

        if ($quantity <= 0) {
            unset($items[$cartItemId]);
        } else {
            $items[$cartItemId]['quantity'] = $quantity;
            $items[$cartItemId]['line_total'] = $items[$cartItemId]['unit_price'] * $quantity;
        }

        Session::put(self::SESSION_KEY, $items);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(string $cartItemId): void
    {
        $items = $this->get();

        if (isset($items[$cartItemId])) {
            unset($items[$cartItemId]);
            Session::put(self::SESSION_KEY, $items);
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
