<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\OrderDetail;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

class OrderDetailTest extends OrdersTestCase
{
    public function test_a_customer_can_open_the_order_page_and_print_a_receipt(): void
    {
        $this->enableLipa();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'ABC123XYZ');
        $this->actingAs($customer);

        Livewire::test(OrderDetail::class, ['order' => $order])
            ->assertSee($order->order_number)
            ->assertSee('Progress')
            ->assertSee('Print receipt')
            ->assertSee('TZS 100')
            ->assertSee('ABC123XYZ');
    }

    public function test_a_customer_cannot_open_someone_elses_order(): void
    {
        $this->enableLipa();
        $order = $this->placedLipaOrder(User::factory()->create(['is_admin' => false]), 'ABC123XYZ');
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_a_customer_can_submit_a_transaction_id_from_the_order_page(): void
    {
        $this->enableLipa();
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->placedLipaOrder($customer, 'OLDPHONE1');
        $this->actingAs($customer);

        Livewire::test(OrderDetail::class, ['order' => $order])
            ->set('reference', 'NEWPHONE2')
            ->call('submitReference')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_reference' => 'NEWPHONE2']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'reference' => 'NEWPHONE2',
            'status' => Payment::STATUS_AWAITING_VERIFICATION,
        ]);
    }

    public function test_the_order_page_rejects_a_transaction_id_that_is_already_in_use(): void
    {
        $this->enableLipa();
        $customer = User::factory()->create(['is_admin' => false]);
        $this->placedLipaOrder($customer, 'TAKEN0001');
        $order = $this->placedLipaOrder($customer, 'MINE00001');
        $this->actingAs($customer);

        Livewire::test(OrderDetail::class, ['order' => $order])
            ->set('reference', 'TAKEN0001')
            ->call('submitReference')
            ->assertHasErrors('reference');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_reference' => 'MINE00001']);
    }
}
