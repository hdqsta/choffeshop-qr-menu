<?php

namespace App\Livewire\Customer;

use App\Enums\PaymentStatus as PaymentStatusEnum;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class OrderStatus extends Component
{
    public string $publicToken = '';

    public function mount(string $publicToken): void
    {
        $this->publicToken = $publicToken;
        $this->getOrder();
    }

    public function getOrder(): Order
    {
        return Order::with(['items.modifiers', 'table', 'latestPayment'])
            ->where('public_token', $this->publicToken)
            ->firstOrFail();
    }

    public function checkPaymentStatus(): void
    {
        // Polling hook to re-render component state in real-time
    }

    /**
     * Demo-only helper that self-confirms a pending order. Disabled outside local
     * and testing environments so a guest can never mark their own order as paid.
     */
    public function simulatePayment(OrderStateMachine $stateMachine): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $order = $this->getOrder();

        if ($order->payment_status === PaymentStatusEnum::Pending) {
            $stateMachine->markAsPaid($order);

            if ($order->latestPayment) {
                $order->latestPayment->update([
                    'status' => PaymentStatusEnum::Paid,
                    'paid_at' => now(),
                ]);
            }

            session()->flash('success', 'Pembayaran berhasil dikonfirmasi!');
        }
    }

    public function render(): View
    {
        $order = $this->getOrder();

        return view('livewire.customer.order-status', [
            'order' => $order,
        ]);
    }
}
