<?php

namespace Tests\Feature\Storefront;

use App\Models\Payment;
use App\Models\User;

class AccountHubTest extends OrdersTestCase
{
    public function test_every_account_section_shares_one_tab_bar(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        foreach ([route('profile.edit'), route('orders.index'), route('payments.index')] as $url) {
            $response = $this->actingAs($customer)->get($url);

            $response->assertOk()
                ->assertSee('aria-label="Account sections"', false)
                ->assertSee('Profile info')
                ->assertSee('Orders')
                ->assertSee('Payments')
                ->assertSee('Profile update')
                ->assertSee('Account update');
        }
    }

    public function test_the_tab_bar_marks_only_the_open_section_as_active(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        foreach ([route('profile.edit'), route('orders.index'), route('payments.index')] as $url) {
            $content = $this->actingAs($customer)->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($content, 'is-active'), $url.' should mark one tab active.');
        }
    }

    public function test_the_payment_history_lists_the_customers_payments_only(): void
    {
        $this->enableLipa();

        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'MYREF0001');

        $order->payments()->create([
            'method' => Payment::METHOD_VODACOM_LIPA,
            'amount' => 100,
            'currency' => 'TZS',
            'reference' => 'VERIFIED9',
            'payer_phone' => '0754000000',
            'status' => Payment::STATUS_VERIFIED,
            'submitted_at' => now(),
            'verified_at' => now(),
        ]);

        $otherOrder = $this->placedLipaOrder(User::factory()->create(['is_admin' => false]), 'NOTMINE01');

        $this->actingAs($customer)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment history')
            ->assertSee($order->order_number)
            ->assertSee('MYREF0001')
            ->assertSee('VERIFIED9')
            ->assertSee('Awaiting verification')
            ->assertSee('Verified')
            ->assertSee('TZS 100')
            ->assertSee('Vodacom Lipa (M-Pesa)')
            ->assertDontSee('NOTMINE01')
            ->assertDontSee($otherOrder->order_number);
    }

    public function test_the_payment_history_is_empty_for_a_new_account(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('No payments have been recorded on this account yet.');
    }

    public function test_guests_are_redirected_away_from_the_payment_history(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }
}
