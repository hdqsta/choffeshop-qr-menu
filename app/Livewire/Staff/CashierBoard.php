<?php

namespace App\Livewire\Staff;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Order\OrderStateMachine;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CashierBoard extends Component
{
    use WithPagination;

    public string $tab = 'active';

    public string $search = '';

    /**
     * Polling hook to refresh orders view in real-time.
     */
    public function pollOrders(): void
    {
        // Polling hook
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function confirmPayment(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->payment_status !== PaymentStatus::Paid) {
            $stateMachine->markAsPaid($order, auth()->user());

            if ($order->latestPayment) {
                $order->latestPayment->update([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                ]);
            }

            session()->flash('success', "Pembayaran untuk {$order->order_number} berhasil dikonfirmasi LUNAS.");
        }
    }

    public function cancelOrder(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->isPaid()) {
            session()->flash('error', 'Pesanan yang sudah lunas tidak dapat dibatalkan langsung.');

            return;
        }

        $stateMachine->cancelOrder($order, 'Dibatalkan oleh kasir');
        session()->flash('success', "Pesanan {$order->order_number} telah dibatalkan.");
    }

    public function resolveReview(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'needs_review' => false,
            'order_status' => OrderStatus::Confirmed,
        ]);

        session()->flash('success', "Review pesanan {$order->order_number} selesai. Pesanan diteruskan ke Bar.");
    }

    public function markCompleted(int $orderId, OrderStateMachine $stateMachine): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->order_status === OrderStatus::Ready) {
            $stateMachine->transitionOrderStatus($order, OrderStatus::Completed);
            session()->flash('success', "Pesanan {$order->order_number} ditandai selesai.");
        }
    }

    public function render(): View
    {
        $query = Order::with(['table', 'items.modifiers', 'latestPayment'])
            ->latest();

        // Search filter
        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('order_number', 'like', "%{$this->search}%")
                    ->orWhereHas('table', function ($t): void {
                        $t->where('number', 'like', "%{$this->search}%");
                    });
            });
        }

        // Tab filter
        match ($this->tab) {
            'needs_review' => $query->where('needs_review', true),
            'pending' => $query->where('payment_status', PaymentStatus::Pending)
                ->where('order_status', '!=', OrderStatus::Cancelled),
            'paid' => $query->where('payment_status', PaymentStatus::Paid)
                ->whereIn('order_status', [OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready]),
            'history' => $query->whereIn('order_status', [OrderStatus::Completed, OrderStatus::Cancelled]),
            default => $query->whereNotIn('order_status', [OrderStatus::Completed, OrderStatus::Cancelled]),
        };

        $orders = $query->paginate(15);

        // Daily stats
        $stats = [
            'pending_count' => Order::where('payment_status', PaymentStatus::Pending)
                ->where('order_status', '!=', OrderStatus::Cancelled)
                ->whereDate('created_at', today())
                ->count(),
            'review_count' => Order::where('needs_review', true)->count(),
            'paid_today_count' => Order::where('payment_status', PaymentStatus::Paid)
                ->whereDate('created_at', today())
                ->count(),
            'revenue_today' => Order::where('payment_status', PaymentStatus::Paid)
                ->whereDate('created_at', today())
                ->sum('total_amount'),
        ];

        return view('livewire.staff.cashier-board', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
