<?php

namespace App\Livewire\Customer;

use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Cart extends Component
{
    public function increment(string $cartItemId, CartService $cartService): void
    {
        $items = $cartService->get();
        if (isset($items[$cartItemId])) {
            $cartService->updateQuantity($cartItemId, $items[$cartItemId]['quantity'] + 1);
        }
    }

    public function decrement(string $cartItemId, CartService $cartService): void
    {
        $items = $cartService->get();
        if (isset($items[$cartItemId])) {
            $cartService->updateQuantity($cartItemId, $items[$cartItemId]['quantity'] - 1);
        }
    }

    public function removeItem(string $cartItemId, CartService $cartService): void
    {
        $cartService->remove($cartItemId);
    }

    public function clearCart(CartService $cartService): void
    {
        $cartService->clear();
    }

    public function render(CartService $cartService): View
    {
        return view('livewire.customer.cart', [
            'cartItems' => $cartService->get(),
            'totalQuantity' => $cartService->count(),
            'grandTotal' => $cartService->total(),
            'tableNumber' => session('table_number', '-'),
        ]);
    }
}
