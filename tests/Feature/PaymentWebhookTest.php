<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_webhook_processes_paid_settlement_and_confirms_order(): void
    {
        config(['payment.midtrans.server_key' => 'SB-Mid-server-TESTKEY123']);

        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'order_number' => 'ORD-MIDTRANS-001',
            'table_id' => $table->id,
            'total_amount' => 45000,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'provider_ref' => 'MID-TX-998877',
            'amount' => 45000,
            'status' => PaymentStatus::Pending,
        ]);

        $serverKey = 'SB-Mid-server-TESTKEY123';
        $orderId = 'ORD-MIDTRANS-001';
        $statusCode = '200';
        $grossAmount = '45000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MID-TX-998877',
        ];

        $response = $this->postJson(route('api.webhooks.payment', ['gateway' => 'midtrans']), $payload);
        $response->assertOk()
            ->assertJson(['status' => 'success']);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);

        // Check PaymentEvent idempotency record
        $this->assertDatabaseHas('payment_events', [
            'payment_id' => $payment->id,
            'gateway' => 'midtrans',
            'signature_valid' => true,
        ]);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        config(['payment.midtrans.server_key' => 'SB-Mid-server-TESTKEY123']);

        $payload = [
            'order_id' => 'ORD-FAKE-001',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'invalid-signature-hash',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('api.webhooks.payment', ['gateway' => 'midtrans']), $payload);
        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid signature']);
    }

    public function test_midtrans_webhook_is_idempotent_on_duplicate_delivery(): void
    {
        config(['payment.midtrans.server_key' => 'SB-Mid-server-TESTKEY123']);

        $table = Table::factory()->create();
        $order = Order::factory()->create(['order_number' => 'ORD-DUP-001', 'table_id' => $table->id]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'provider_ref' => 'TX-DUP-1',
            'amount' => 20000,
            'status' => PaymentStatus::Pending,
        ]);

        $serverKey = 'SB-Mid-server-TESTKEY123';
        $orderId = 'ORD-DUP-001';
        $statusCode = '200';
        $grossAmount = '20000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'TX-DUP-1',
        ];

        // First delivery: success
        $res1 = $this->postJson(route('api.webhooks.payment', ['gateway' => 'midtrans']), $payload);
        $res1->assertOk()->assertJson(['status' => 'success']);

        // Duplicate delivery: returns already_processed without re-processing
        $res2 = $this->postJson(route('api.webhooks.payment', ['gateway' => 'midtrans']), $payload);
        $res2->assertOk()->assertJson(['status' => 'already_processed']);

        // Only 1 event record in DB
        $this->assertSame(1, PaymentEvent::count());
    }

    public function test_webhook_sets_needs_review_when_cancelled_order_receives_payment(): void
    {
        config(['payment.midtrans.server_key' => 'KEY123']);

        $table = Table::factory()->create();
        // Order was already cancelled due to timeout
        $order = Order::factory()->create([
            'order_number' => 'ORD-LATE-001',
            'table_id' => $table->id,
            'order_status' => OrderStatus::Cancelled,
            'payment_status' => PaymentStatus::Expired,
            'needs_review' => false,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'provider_ref' => 'TX-LATE-1',
            'amount' => 50000,
            'status' => PaymentStatus::Pending,
        ]);

        $signature = hash('sha512', 'ORD-LATE-001'.'200'.'50000.00'.'KEY123');

        $payload = [
            'order_id' => 'ORD-LATE-001',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'TX-LATE-1',
        ];

        $response = $this->postJson(route('api.webhooks.payment', ['gateway' => 'midtrans']), $payload);
        $response->assertOk();

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $order->order_status); // Not resurrected
        $this->assertTrue($order->needs_review); // Flagged for cashier review!
    }

    public function test_xendit_webhook_processes_paid_status(): void
    {
        config(['payment.xendit.webhook_token' => 'xendit-test-token-456']);

        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'order_number' => 'ORD-XENDIT-001',
            'table_id' => $table->id,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'xendit',
            'provider_ref' => 'qr_xendit_12345',
            'amount' => 35000,
            'status' => PaymentStatus::Pending,
        ]);

        $payload = [
            'status' => 'COMPLETED',
            'amount' => 35000,
            'qr_code' => [
                'id' => 'qr_xendit_12345',
                'reference_id' => 'ORD-XENDIT-001',
            ],
        ];

        $response = $this->withHeaders([
            'x-callback-token' => 'xendit-test-token-456',
        ])->postJson(route('api.webhooks.payment', ['gateway' => 'xendit']), $payload);

        $response->assertOk()->assertJson(['status' => 'success']);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
    }
}
