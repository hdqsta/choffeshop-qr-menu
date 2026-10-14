<?php

namespace App\Livewire\Staff;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BarBoard extends Component
{
    public string $filter = 'active'; // 'active', 'confirmed', 'preparing', 'ready', 'done'

    /**
     * Polling hook to refresh bar queue every few seconds.
     */
    public function pollBarQueue(): void
    {
        // Polling hook
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function startPreparing(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        // Security check: only paid orders can be prepared by bar
        if (! $order->isPaid()) {
            session()->flash('error', 'Pesanan belum lunas!');

            return;
        }

        if ($order->order_status === OrderStatus::Confirmed) {
            $stateMachine->transitionOrderStatus($order, OrderStatus::Preparing);
            session()->flash('success', "Pesanan {$order->order_number} mulai diracik.");
        }
    }

    public function markReady(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        if (! $order->isPaid()) {
            session()->flash('error', 'Pesanan belum lunas!');

            return;
        }

        if ($order->order_status === OrderStatus::Preparing) {
            $stateMachine->transitionOrderStatus($order, OrderStatus::Ready);
            session()->flash('success', "Pesanan {$order->order_number} siap disajikan ke Meja {$order->table->number}.");
        }
    }

    public function markCompleted(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->order_status === OrderStatus::Ready) {
            $stateMachine->transitionOrderStatus($order, OrderStatus::Completed);
            session()->flash('success', "Pesanan {$order->order_number} selesai.");
        }
    }

    public function render(): View
    {
        // Strictly ONLY PAID orders
        $baseQuery = Order::with(['table', 'items.modifiers'])
            ->where('payment_status', PaymentStatus::Paid);

        $query = clone $baseQuery;

        match ($this->filter) {
            'confirmed' => $query->where('order_status', OrderStatus::Confirmed)->oldest(),
            'preparing' => $query->where('order_status', OrderStatus::Preparing)->oldest(),
            'ready' => $query->where('order_status', OrderStatus::Ready)->oldest(),
            'done' => $query->where('order_status', OrderStatus::Completed)->whereDate('updated_at', today())->latest()->limit(20),
            default => $query->whereIn('order_status', [OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready])->oldest(),
        };

        $orders = $query->get();

        // Queue counts for tabs
        $counts = [
            'confirmed' => (clone $baseQuery)->where('order_status', OrderStatus::Confirmed)->count(),
            'preparing' => (clone $baseQuery)->where('order_status', OrderStatus::Preparing)->count(),
            'ready' => (clone $baseQuery)->where('order_status', OrderStatus::Ready)->count(),
            'done' => (clone $baseQuery)->where('order_status', OrderStatus::Completed)->whereDate('updated_at', today())->count(),
        ];

        return view('livewire.staff.bar-board', [
            'orders' => $orders,
            'counts' => $counts,
        ]);
    }
}
