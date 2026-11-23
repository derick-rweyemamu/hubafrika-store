<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Checkout;
use App\Mail\PaymentVerifiedMail;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Selcom\SelcomClient;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class SelcomPaymentTest extends OrdersTestCase
{
    protected function enableSelcom(
        string $mode = 'simulation',
        string $apiKey = 'test-key',
        string $apiSecret = 'test-secret',
        string $vendorId = 'VND123'
    ): void {
        foreach ([
            'selcom_enabled' => '1',
            'selcom_mode' => $mode,
            'selcom_api_key' => $apiKey,
            'selcom_api_secret' => $apiSecret,
            'selcom_vendor_id' => $vendorId,
            'selcom_instructions' => 'Pay via Selcom mobile money or card.',
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value, 'lang' => null]);
        }
    }

    public function test_selcom_is_available_when_enabled(): void
    {
        $this->enableSelcom();

        $methods = Payment::availableMethods();
        $this->assertArrayHasKey(Payment::METHOD_SELCOM, $methods);
    }

    public function test_selcom_name_lookup_in_simulation_mode(): void
    {
        $this->enableSelcom('simulation');

        $client = new SelcomClient(mode: 'simulation');
        $result = $client->nameLookup('0754123456', 'VODACOM');

        $this->assertSame('SUCCESS', $result['result']);
        $this->assertNotEmpty($result['name']);
        $this->assertSame('255754123456', $result['phone']);
    }

    public function test_selcom_checkout_places_order_and_creates_payment(): void
    {
        $this->enableSelcom('simulation');
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        $component = Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('shippingPhone', '0754123456')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->set('selcomNetwork', 'VODACOM')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $this->assertSame(Payment::METHOD_SELCOM, $order->payment_method);
        $this->assertNotNull($order->payment_reference);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => Payment::METHOD_SELCOM,
            'payer_phone' => '0754123456',
        ]);
    }

    public function test_selcom_webhook_callback_verifies_payment(): void
    {
        Mail::fake();

        $this->enableSelcom('simulation');
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('shippingPhone', '0754123456')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $response = $this->postJson(route('payments.selcom.callback'), [
            'order_id' => $order->order_number,
            'transid' => 'SELCOM-TRANS-998877',
            'payment_status' => 'COMPLETED',
            'amount' => (string) $order->total,
            'phone' => '255754123456',
        ]);

        $response->assertOk()
            ->assertJson([
                'result' => 'SUCCESS',
                'resultcode' => '000',
            ]);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('SELCOM-TRANS-998877', $order->payment_reference);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertNotNull($payment->verified_at);

        Mail::assertSent(PaymentVerifiedMail::class);
    }

    public function test_selcom_webhook_callback_handles_failure(): void
    {
        $this->enableSelcom('simulation');
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $response = $this->postJson(route('payments.selcom.callback'), [
            'order_id' => $order->order_number,
            'transid' => 'SELCOM-TRANS-000000',
            'payment_status' => 'FAILED',
        ]);

        $response->assertOk();

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame(Payment::STATUS_REJECTED, $payment->status);
    }

    public function test_selcom_webhook_verifies_signature_in_live_mode(): void
    {
        $apiKey = 'my-api-key';
        $apiSecret = 'secret123456';
        $this->enableSelcom(mode: 'live', apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123');

        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        // 1. Invalid signature should be rejected with 403
        $badResponse = $this->postJson(route('payments.selcom.callback'), [
            'order_id' => $order->order_number,
            'payment_status' => 'COMPLETED',
        ], [
            'Digest' => 'invalid-digest',
            'Timestamp' => now()->toIso8601String(),
            'Signed-Fields' => 'order_id,payment_status',
        ]);

        $badResponse->assertStatus(403);

        // 2. Valid computed signature should be accepted
        $client = new SelcomClient(apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123', mode: 'live');
        $payload = [
            'order_id' => $order->order_number,
            'payment_status' => 'COMPLETED',
            'transid' => 'SELCOM-LIVE-SUCCESS',
        ];
        $timestamp = now()->toIso8601String();
        $headers = $client->computeHeaders($payload, $timestamp);

        $goodResponse = $this->postJson(route('payments.selcom.callback'), $payload, [
            'Digest' => $headers['Digest'],
            'Timestamp' => $timestamp,
            'Signed-Fields' => $headers['Signed-Fields'],
        ]);

        $goodResponse->assertOk()->assertJson(['result' => 'SUCCESS']);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_selcom_simulation_endpoint_marks_order_paid(): void
    {
        Mail::fake();

        $this->enableSelcom('simulation');
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $response = $this->actingAs($customer)
            ->post(route('payments.selcom.simulate', ['order' => $order]));

        $response->assertRedirect(route('orders.show', ['order' => $order]));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertStringStartsWith('SIM-TRANS-', $order->payment_reference);

        Mail::assertSent(PaymentVerifiedMail::class);
    }
}
