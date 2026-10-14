<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use DomainException;

class OrderStateMachine
{
    /**
     * Mark an order as paid. If order was already cancelled or expired, set needs_review.
     */
    public function markAsPaid(Order $order, ?User $confirmedBy = null): void
    {
        // If order is already cancelled, don't resurrect it. Flag for review!
        if ($order->order_status === OrderStatus::Cancelled) {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'needs_review' => true,
            ]);

            return;
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return; // Idempotent
        }

        $order->update([
            'payment_status' => PaymentStatus::Paid,
            'order_status' => OrderStatus::Confirmed,
            'paid_at' => now(),
        ]);
    }

    /**
     * Transition the order workflow status.
     */
    public function transitionOrderStatus(Order $order, OrderStatus $targetStatus): void
    {
        $current = $order->order_status;

        $allowedTransitions = [
            OrderStatus::PendingPayment->value => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
            OrderStatus::Confirmed->value => [OrderStatus::Preparing->value, OrderStatus::Cancelled->value],
            OrderStatus::Preparing->value => [OrderStatus::Ready->value],
            OrderStatus::Ready->value => [OrderStatus::Completed->value],
            OrderStatus::Completed->value => [],
            OrderStatus::Cancelled->value => [],
        ];

        $allowed = $allowedTransitions[$current->value] ?? [];

        if (! in_array($targetStatus->value, $allowed, true)) {
            throw new DomainException("Transisi status dari '{$current->label()}' ke '{$targetStatus->label()}' tidak diizinkan.");
        }

        $order->update([
            'order_status' => $targetStatus,
        ]);
    }

    /**
     * Cancel an unpaid order.
     */
    public function cancelOrder(Order $order, string $reason = ''): void
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            throw new DomainException('Pesanan yang sudah dibayar tidak dapat dibatalkan otomatis.');
        }

        $order->update([
            'order_status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Expired,
        ]);
    }

    /**
     * Mark order as refunded.
     */
    public function markAsRefunded(Order $order): void
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            throw new DomainException('Hanya pesanan yang sudah lunas yang dapat dikembalikan dananya (refund).');
        }

        $order->update([
            'payment_status' => PaymentStatus::Refunded,
            'order_status' => OrderStatus::Cancelled,
        ]);
    }
}
