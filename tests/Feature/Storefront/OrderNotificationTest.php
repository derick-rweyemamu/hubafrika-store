<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Storefront\Checkout;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class OrderNotificationTest extends OrdersTestCase
{
    public function test_placing_a_cash_on_delivery_order_emails_the_customer(): void
    {
        Mail::fake();
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->call('placeOrder')
            ->assertHasNoErrors();

        Mail::assertSent(
            OrderPlacedMail::class,
            fn (OrderPlacedMail $mail): bool => $mail->hasTo($customer->email),
        );
    }

    public function test_the_confirmation_email_tells_lipa_customers_where_to_pay(): void
    {
        Mail::fake();
        $this->enableLipa(number: '5551234');
        $customer = $this->customerWithCart();
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '10 River Road')
            ->set('paymentMethod', 'vodacom_lipa')
            ->set('paymentPayerPhone', '0754000000')
            ->set('paymentReference', 'ABC123XYZ')
            ->call('placeOrder')
            ->assertHasNoErrors();

        Mail::assertSent(OrderPlacedMail::class, function (OrderPlacedMail $mail): bool {
            $html = $mail->render();

            return str_contains($html, '5551234') && str_contains($html, 'Lipa');
        });
    }

    public function test_verifying_a_payment_emails_the_customer(): void
    {
        Mail::fake();
        $this->enableLipa();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'ABC123XYZ');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(AdminOrders::class)->call('verifyPayment', $order->id);

        Mail::assertSent(PaymentVerifiedMail::class, function (PaymentVerifiedMail $mail) use ($customer, $order): bool {
            return $mail->hasTo($customer->email)
                && str_contains($mail->render(), $order->order_number);
        });
    }

    public function test_shipping_an_order_emails_the_customer(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'ABC123XYZ');
        $order->update(['payment_status' => 'paid']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(AdminOrders::class)->call('updateStatus', $order->id, 'processing');
        Livewire::test(AdminOrders::class)->call('updateStatus', $order->id, 'shipped');

        Mail::assertSent(OrderShippedMail::class, function (OrderShippedMail $mail) use ($customer, $order): bool {
            return $mail->hasTo($customer->email)
                && str_contains($mail->render(), $order->order_number);
        });
    }

    public function test_a_customer_without_an_email_address_is_skipped_quietly(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'NOEMAIL01');
        $order->forceFill(['customer_email' => ''])->save();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(AdminOrders::class)->call('verifyPayment', $order->id);

        Mail::assertNothingSent();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }
}
