<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Customer\OrderStatus as OrderStatusComponent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\Payment\ManualQrisGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\TestCase;

class ManualQrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_gateway_returns_the_configured_static_merchant_qris_image(): void
    {
        config([
            'payment.manual.qris_image' => '/images/qris-merchant.png',
            'payment.manual.merchant_name' => 'Kopi Uji',
        ]);

        $order = Order::factory()->create(['total_amount' => 25000]);

        $intent = (new ManualQrisGateway)->createPayment($order);

        $this->assertSame('manual', $intent->gateway);
        $this->assertSame(25000, $intent->amount);
        $this->assertSame('/images/qris-merchant.png', $intent->qrUrl);
        $this->assertNull($intent->qrString);
    }

    public function test_manual_gateway_never_encodes_the_order_status_page_as_qris_payload(): void
    {
        config([
            'payment.manual.qris_image' => '/images/qris-merchant.png',
            'payment.manual.merchant_name' => 'Kopi Uji',
        ]);

        $order = Order::factory()->create();

        $intent = (new ManualQrisGateway)->createPayment($order);

        $this->assertStringNotContainsString($order->public_token, (string) $intent->qrUrl);
        $this->assertStringNotContainsString('qrserver', (string) $intent->qrUrl);
    }

    public function test_manual_webhook_endpoint_is_not_available(): void
    {
        $table = Table::factory()->create();
        $order = Order::factory()->create([
            'order_number' => 'ORD-MANUAL-001',
            'table_id' => $table->id,
            'total_amount' => 30000,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'manual',
            'provider_ref' => 'MAN-ORD-MANUAL-001',
            'amount' => 30000,
            'status' => PaymentStatus::Pending,
        ]);

        $response = $this->postJson(route('api.webhooks.payment', ['gateway' => 'manual']), [
            'order_id' => 'ORD-MANUAL-001',
            'status' => 'paid',
            'amount' => 30000,
        ]);

        $response->assertNotFound();

        $order->refresh();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_manual_gateway_marks_incoming_webhook_payloads_as_untrusted(): void
    {
        $notification = (new ManualQrisGateway)->parseWebhook(Request::create('/webhooks/payment/manual', 'POST', [
            'order_id' => 'ORD-MANUAL-001',
            'status' => 'paid',
            'amount' => 30000,
        ]));

        $this->assertFalse($notification->signatureValid);
        $this->assertSame(PaymentStatus::Pending, $notification->status);
    }

    public function test_customer_order_status_page_shows_static_qris_with_amount_to_enter(): void
    {
        config([
            'payment.manual.qris_image' => '/images/qris-merchant.png',
            'payment.manual.merchant_name' => 'Kopi Uji',
        ]);

        $table = Table::factory()->create(['number' => '07']);
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'total_amount' => 45000,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'manual',
            'provider_ref' => 'MAN-'.$order->order_number,
            'amount' => 45000,
            'status' => PaymentStatus::Pending,
            'qr_url' => '/images/qris-merchant.png',
        ]);

        Livewire::test(OrderStatusComponent::class, ['publicToken' => $order->public_token])
            ->assertSee('qris-merchant.png')
            ->assertSee('Kopi Uji')
            ->assertSee('45.000')
            ->assertSee('QRIS statis tidak memuat nominal');
    }

    public function test_customer_order_status_page_warns_when_static_qris_is_unconfigured(): void
    {
        config(['payment.manual.qris_image' => '']);

        $order = Order::factory()->create([
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'manual',
            'provider_ref' => 'MAN-'.$order->order_number,
            'amount' => $order->total_amount,
            'status' => PaymentStatus::Pending,
            'qr_url' => '',
        ]);

        Livewire::test(OrderStatusComponent::class, ['publicToken' => $order->public_token])
            ->assertSee('QRIS statis merchant belum dikonfigurasi');
    }
}
