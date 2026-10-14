<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Payment;
use App\Services\Cart\CartService;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderPricingService $pricingService,
        protected PaymentManager $paymentManager,
        protected CartService $cartService,
    ) {}

    /**
     * Create an order from active cart items, initialize payment, and clear cart.
     */
    public function createOrderFromCart(int $tableId, ?string $orderNotes = null, ?string $gateway = null): Order
    {
        $cartItems = $this->cartService->get();
        $pricing = $this->pricingService->calculate($cartItems);

        return DB::transaction(function () use ($tableId, $orderNotes, $gateway, $pricing): Order {
            $expiryMinutes = (int) config('payment.expiry_minutes', 15);
            $expiresAt = now()->addMinutes($expiryMinutes);

            // Generate order number max 25 chars (e.g. ORD-261011-AB12)
            $orderNumber = 'ORD-'.date('ymd').'-'.strtoupper(Str::random(5));

            $order = Order::create([
                'public_token' => Str::random(32),
                'order_number' => $orderNumber,
                'table_id' => $tableId,
                'notes' => $orderNotes ?: null,
                'total_amount' => $pricing['total_amount'],
                'payment_status' => PaymentStatus::Pending,
                'order_status' => OrderStatus::PendingPayment,
                'needs_review' => false,
                'expires_at' => $expiresAt,
            ]);

            // Save order items & modifiers snapshot
            foreach ($pricing['items'] as $itemData) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'],
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'line_total' => $itemData['line_total'],
                    'notes' => $itemData['notes'],
                ]);

                foreach ($itemData['modifiers'] as $modData) {
                    OrderItemModifier::create([
                        'order_item_id' => $orderItem->id,
                        'group_name' => $modData['group_name'],
                        'option_name' => $modData['option_name'],
                        'price_delta' => $modData['price_delta'],
                    ]);
                }
            }

            // Create initial payment intent with selected gateway
            $selectedGateway = $gateway ?: config('payment.default', 'manual');
            $paymentDriver = $this->paymentManager->gateway($selectedGateway);
            $intent = $paymentDriver->createPayment($order);

            Payment::create([
                'order_id' => $order->id,
                'gateway' => $intent->gateway,
                'provider_ref' => $intent->providerRef,
                'amount' => $intent->amount,
                'status' => $intent->status,
                'qr_string' => $intent->qrString,
                'qr_url' => $intent->qrUrl,
                'expires_at' => $intent->expiresAt,
            ]);

            // Clear the cart
            $this->cartService->clear();

            return $order->load(['items.modifiers', 'table', 'latestPayment']);
        });
    }
}
