<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Settings;
use App\Livewire\Storefront\Checkout;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

class LipaPaymentTest extends OrdersTestCase
{
    public function test_lipa_checkout_creates_a_payment_awaiting_verification(): void
    {
        $this->enableLipa();
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        $component = Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->set('paymentMethod', Payment::METHOD_VODACOM_LIPA)
            ->set('paymentPayerPhone', '0754 000 000')
            ->set('paymentReference', 'QGR7X8Y2Z1')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $component->assertRedirect(route('orders.show', $order));

        $this->assertSame(Payment::METHOD_VODACOM_LIPA, $order->payment_method);
        $this->assertSame('awaiting_verification', $order->payment_status);
        $this->assertSame('QGR7X8Y2Z1', $order->payment_reference);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => Payment::METHOD_VODACOM_LIPA,
            'reference' => 'QGR7X8Y2Z1',
            'payer_phone' => '0754000000',
            'status' => Payment::STATUS_AWAITING_VERIFICATION,
        ]);
    }

    public function test_lipa_checkout_requires_a_mobile_number_and_transaction_id(): void
    {
        $this->enableLipa();
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->set('paymentMethod', Payment::METHOD_VODACOM_LIPA)
            ->call('placeOrder')
            ->assertHasErrors('paymentPayerPhone');

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->set('paymentMethod', Payment::METHOD_VODACOM_LIPA)
            ->set('paymentPayerPhone', '0754000000')
            ->call('placeOrder')
            ->assertHasErrors('paymentReference');

        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
    }

    public function test_a_transaction_id_cannot_be_reused_for_another_order(): void
    {
        $this->enableLipa();
        $customer = $this->customerWithCart();

        $existingOrder = $this->placedLipaOrder($customer, 'QGR7X8Y2Z1');

        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->set('paymentMethod', Payment::METHOD_VODACOM_LIPA)
            ->set('paymentPayerPhone', '0754000000')
            ->set('paymentReference', 'QGR7X8Y2Z1')
            ->call('placeOrder')
            ->assertHasErrors('paymentReference');

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame($existingOrder->id, Order::query()->sole()->id);
    }

    public function test_lipa_is_not_offered_until_a_lipa_number_is_configured(): void
    {
        $this->enableLipa(number: '');

        $this->assertSame(
            [Payment::METHOD_CASH_ON_DELIVERY => 'Cash on delivery'],
            Payment::availableMethods(),
        );

        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)->assertDontSee('Vodacom Lipa (M-Pesa)');
    }

    public function test_admin_must_verify_a_lipa_payment_before_processing_the_order(): void
    {
        $this->enableLipa();
        $product = $this->product();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'ABC123XYZ', $product);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(AdminOrders::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertSee('Verify the Vodacom Lipa payment');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);

        Livewire::test(AdminOrders::class)
            ->call('verifyPayment', $order->id)
            ->assertSee('Payment verified');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'payment_reference' => 'ABC123XYZ',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'reference' => 'ABC123XYZ',
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $admin->id,
        ]);

        Livewire::test(AdminOrders::class)->call('updateStatus', $order->id, 'processing');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
    }

    public function test_admin_can_reject_a_lipa_transaction_id(): void
    {
        $this->enableLipa();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'WRONG999');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(AdminOrders::class)
            ->call('rejectPayment', $order->id)
            ->assertSee('Payment rejected');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'failed']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'reference' => 'WRONG999',
            'status' => Payment::STATUS_REJECTED,
        ]);
    }

    public function test_vodacom_callback_verifies_the_payment_when_the_signature_matches(): void
    {
        $this->enableLipa(secret: 'callback-secret');
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'HOOK12345');

        $payload = json_encode([
            'reference' => 'HOOK12345',
            'amount' => 24.8,
            'status' => 'success',
            'payer_phone' => '0754000000',
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('payments.vodacom.callback'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VODACOM_SIGNATURE' => hash_hmac('sha256', $payload, 'callback-secret'),
        ], $payload)
            ->assertOk()
            ->assertJson(['status' => 'verified']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'reference' => 'HOOK12345',
            'status' => Payment::STATUS_VERIFIED,
        ]);
    }

    public function test_vodacom_callback_rejects_a_bad_signature_and_stays_disabled_without_a_secret(): void
    {
        $this->enableLipa(secret: '');
        $customer = User::factory()->create(['is_admin' => false]);
        $this->placedLipaOrder($customer, 'HOOK99999');

        $payload = json_encode(['reference' => 'HOOK99999', 'status' => 'success'], JSON_THROW_ON_ERROR);

        $this->call('POST', route('payments.vodacom.callback'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(503);

        BusinessSetting::query()
            ->where('type', 'lipa_webhook_secret')
            ->update(['value' => 'callback-secret']);

        $this->call('POST', route('payments.vodacom.callback'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VODACOM_SIGNATURE' => 'not-the-right-signature',
        ], $payload)->assertStatus(403);

        $this->assertDatabaseHas('payments', [
            'reference' => 'HOOK99999',
            'status' => Payment::STATUS_AWAITING_VERIFICATION,
        ]);
    }

    public function test_admin_can_configure_the_lipa_account_from_the_settings_page(): void
    {
        $this->enableLipa(number: '');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->call('selectTab', 'shipping-checkout')
            ->assertSee('Lipa number')
            ->assertSee('Vodacom Lipa');

        Livewire::test(Settings::class)
            ->call('edit', 'lipa_number')
            ->set('value', 'not-a-number')
            ->call('save')
            ->assertHasErrors('value');

        Livewire::test(Settings::class)
            ->call('edit', 'lipa_number')
            ->set('value', '5551234')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_settings', ['type' => 'lipa_number', 'value' => '5551234']);
        $this->assertArrayHasKey(Payment::METHOD_VODACOM_LIPA, Payment::availableMethods());
    }
}
