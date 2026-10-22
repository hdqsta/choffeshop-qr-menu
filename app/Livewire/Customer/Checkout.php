<?php

namespace App\Livewire\Customer;

use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Checkout extends Component
{
    public string $orderNotes = '';

    public string $gateway = 'manual';

    public function mount(CartService $cartService): void
    {
        if ($cartService->count() === 0) {
            $this->redirect(route('customer.menu'), navigate: true);
        }
    }

    public function placeOrder(OrderService $orderService, CartService $cartService): void
    {
        if ($cartService->count() === 0) {
            $this->addError('cart', 'Keranjang Anda kosong.');

            return;
        }

        $tableId = session('table_id');
        if (! $tableId) {
            $this->redirect(route('customer.menu'));

            return;
        }

        try {
            $order = $orderService->createOrderFromCart(
                (int) $tableId,
                $this->orderNotes,
                $this->gateway
            );

            $this->redirect(route('customer.order.status', ['publicToken' => $order->public_token]), navigate: true);
        } catch (DomainException $e) {
            $this->addError('order', $e->getMessage());
        }
    }

    public function render(CartService $cartService): View
    {
        return view('livewire.customer.checkout', [
            'cartItems' => $cartService->get(),
            'totalQuantity' => $cartService->count(),
            'grandTotal' => $cartService->total(),
            'tableNumber' => session('table_number', '-'),
        ]);
    }
}
