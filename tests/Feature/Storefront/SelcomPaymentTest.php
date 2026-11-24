<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Checkout;
use App\Mail\PaymentVerifiedMail;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Selcom\SelcomClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class SelcomPaymentTest extends OrdersTestCase
{
    protected function enableSelcom(
        string $apiKey = 'test-key',
        string $apiSecret = 'test-secret',
        string $vendorId = 'VND123',
        string $paymentMethods = 'MOBILEMONEY',
    ): void {
        foreach ([
            'selcom_enabled' => '1',
            'selcom_payment_methods' => $paymentMethods,
            'selcom_api_key' => $apiKey,
            'selcom_api_secret' => $apiSecret,
            'selcom_vendor_id' => $vendorId,
            'selcom_instructions' => 'Pay via Selcom mobile money.',
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value, 'lang' => null]);
        }
    }

    public function test_selcom_is_hidden_until_credentials_are_configured_and_available_when_configured(): void
    {
        $this->enableSelcom(apiKey: '', apiSecret: '', vendorId: '');
        $this->assertArrayNotHasKey(Payment::METHOD_SELCOM, Payment::availableMethods());

        $this->enableSelcom();
        $this->assertArrayHasKey(Payment::METHOD_SELCOM, Payment::availableMethods());
    }

    public function test_selcom_name_lookup_calls_production_gateway(): void
    {
        $this->enableSelcom();

        Http::fake([
            SelcomClient::PRODUCTION_URL.'/v1/walletcashin/namelookup*' => Http::response([
                'result' => 'SUCCESS',
                'data' => [['name' => 'AMINA BAKARI']],
            ]),
        ]);

        $client = new SelcomClient;
        $result = $client->nameLookup('0754123456', 'VODACOM');

        $this->assertSame('SUCCESS', $result['result']);
        $this->assertSame('AMINA BAKARI', $result['name']);
        $this->assertSame('255754123456', $result['phone']);
    }

    public function test_selcom_checkout_places_order_and_redirects_to_gateway(): void
    {
        $this->enableSelcom();
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Http::fake([
            SelcomClient::PRODUCTION_URL.'/v1/checkout/create-order-minimal' => Http::response([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'message' => 'Order created successfully',
                'data' => [[
                    'reference' => 'SELCOM-REF-1001',
                    'payment_gateway_url' => 'https://checkout.selcommobile.com/?id=1001',
                ]],
            ]),
        ]);

        $component = Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('shippingPhone', '0754123456')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->set('selcomNetwork', 'YAS')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $component->assertRedirect('https://checkout.selcommobile.com/?id=1001');

        $order = Order::query()->firstOrFail();

        $this->assertSame(Payment::METHOD_SELCOM, $order->payment_method);
        $this->assertSame('SELCOM-REF-1001', $order->payment_reference);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => Payment::METHOD_SELCOM,
            'payer_phone' => '0754123456',
            'reference' => 'SELCOM-REF-1001',
        ]);
    }

    public function test_selcom_webhook_verifies_signature_and_marks_order_paid(): void
    {
        Mail::fake();

        $apiKey = 'my-api-key';
        $apiSecret = 'secret123456';
        $this->enableSelcom(apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123');

        Http::fake([
            SelcomClient::PRODUCTION_URL.'/v1/checkout/create-order-minimal' => Http::response([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'data' => [['reference' => 'SELCOM-INIT-1']],
            ]),
        ]);

        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        // Invalid signature must be rejected with 403
        $this->postJson(route('payments.selcom.callback'), [
            'order_id' => $order->order_number,
            'payment_status' => 'COMPLETED',
        ], [
            'Digest' => 'invalid-digest',
            'Timestamp' => now()->toIso8601String(),
            'Signed-Fields' => 'order_id,payment_status',
        ])->assertStatus(403);

        // Valid computed signature must be accepted
        $client = new SelcomClient(apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123');
        $payload = [
            'order_id' => $order->order_number,
            'payment_status' => 'COMPLETED',
            'transid' => 'SELCOM-LIVE-SUCCESS',
        ];
        $timestamp = now()->toIso8601String();
        $headers = $client->computeHeaders($payload, $timestamp);

        $this->postJson(route('payments.selcom.callback'), $payload, [
            'Digest' => $headers['Digest'],
            'Timestamp' => $timestamp,
            'Signed-Fields' => $headers['Signed-Fields'],
        ])->assertOk()->assertJson(['result' => 'SUCCESS']);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('SELCOM-LIVE-SUCCESS', $order->payment_reference);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertNotNull($payment->verified_at);

        Mail::assertSent(PaymentVerifiedMail::class);
    }

    public function test_selcom_webhook_handles_failure_with_valid_signature(): void
    {
        $apiKey = 'my-api-key';
        $apiSecret = 'secret123456';
        $this->enableSelcom(apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123');

        Http::fake([
            SelcomClient::PRODUCTION_URL.'/v1/checkout/create-order-minimal' => Http::response([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'data' => [['reference' => 'SELCOM-INIT-2']],
            ]),
        ]);

        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Plot 42 Arusha')
            ->set('paymentMethod', Payment::METHOD_SELCOM)
            ->set('selcomPhone', '0754123456')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $client = new SelcomClient(apiKey: $apiKey, apiSecret: $apiSecret, vendorId: 'VND123');
        $payload = [
            'order_id' => $order->order_number,
            'transid' => 'SELCOM-TRANS-000000',
            'payment_status' => 'FAILED',
        ];
        $timestamp = now()->toIso8601String();
        $headers = $client->computeHeaders($payload, $timestamp);

        $this->postJson(route('payments.selcom.callback'), $payload, [
            'Digest' => $headers['Digest'],
            'Timestamp' => $timestamp,
            'Signed-Fields' => $headers['Signed-Fields'],
        ])->assertOk();

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame(Payment::STATUS_REJECTED, $payment->status);
    }
}
