<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Cart as CartPage;
use App\Livewire\Storefront\CartButton;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\Shop;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Support\Storefront\CartSession;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

class GuestCartTest extends OrdersTestCase
{
    public function test_guests_can_add_a_product_to_the_cart(): void
    {
        $product = $this->product();

        Livewire::test(Shop::class)
            ->call('addToCart', $product->id)
            ->assertHasNoErrors()
            ->assertDispatched('cart-updated')
            ->assertNoRedirect();

        $cartItem = Cart::query()->firstOrFail();

        $this->assertGuest();
        $this->assertNull($cartItem->user_id);
        $this->assertNotNull($cartItem->temp_user_id);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertSame(1, $cartItem->quantity);
        $this->assertSame(100.0, $cartItem->price);
    }

    public function test_a_featured_product_button_adds_the_product_and_opens_the_cart(): void
    {
        $product = $this->product();

        $this->get(route('cart.add', $product))
            ->assertRedirect(route('cart'))
            ->assertSessionHas('message', "{$product->name} added to your cart.");

        $cartItem = Cart::query()->sole();

        $this->assertGuest();
        $this->assertNotNull($cartItem->temp_user_id);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertSame(1, $cartItem->quantity);
        $this->assertSame(100.0, $cartItem->price);
    }

    public function test_the_add_link_sends_a_product_with_options_to_the_shop(): void
    {
        $product = $this->product();
        $product->update(['variant_product' => true]);

        $this->get(route('cart.add', $product))
            ->assertRedirect(route('shop'))
            ->assertSessionHas('message', "Choose an option for {$product->name} on the shop page.");

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_the_add_link_hides_products_that_are_not_published(): void
    {
        $product = $this->product();
        $product->update(['published' => false]);

        $this->get(route('cart.add', $product))->assertNotFound();

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_the_cart_button_shows_only_the_item_figure(): void
    {
        $this->withSession([CartSession::SESSION_KEY => 'guest-badge']);
        $product = $this->product();

        Livewire::test(CartButton::class)
            ->assertSet('count', 0)
            ->assertDontSeeHtml('sf-cart-badge');

        Cart::query()->create([
            'temp_user_id' => 'guest-badge',
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        Livewire::test(CartButton::class)
            ->assertSet('count', 2)
            ->assertSeeHtml('sf-cart-badge');

        Cart::query()->create([
            'temp_user_id' => 'guest-badge',
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100,
        ]);

        // What the `cart-updated` listener does after an add or a quantity change.
        Livewire::test(CartButton::class)
            ->call('refreshCount')
            ->assertSet('count', 5);
    }

    public function test_a_guest_sees_and_edits_the_cart_from_the_same_browser(): void
    {
        $product = $this->product();
        $cartItem = Cart::query()->create([
            'temp_user_id' => 'guest-browser-1',
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
        ]);
        $this->withSession([CartSession::SESSION_KEY => 'guest-browser-1']);

        $this->get(route('cart'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Continue to checkout');

        Livewire::test(CartPage::class)
            ->call('updateQuantity', $cartItem->id, 3)
            ->assertHasNoErrors();

        $this->assertSame(3, $cartItem->fresh()->quantity);

        Livewire::test(CartPage::class)->call('remove', $cartItem->id);

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_guest_checkout_creates_the_account_and_places_the_order(): void
    {
        $product = $this->product();
        Cart::query()->create([
            'temp_user_id' => 'guest-checkout',
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);
        $this->withSession([CartSession::SESSION_KEY => 'guest-checkout']);

        $component = Livewire::test(Checkout::class)
            ->set('name', 'Asha Mwinyi')
            ->set('email', 'asha@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('shippingAddress', '10 River Road')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $customer = User::query()->where('email', 'asha@example.com')->firstOrFail();
        $order = Order::query()->firstOrFail();

        $this->assertAuthenticatedAs($customer);
        $this->assertTrue(Hash::check('password123', $customer->password));
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame(200.0, (float) $order->subtotal);
        $this->assertSame(205.0, (float) $order->total);
        $this->assertSame('10 River Road', $order->shipping_address);
        $this->assertDatabaseCount('carts', 0);
        $component->assertRedirect(route('orders.show', $order));
    }

    public function test_guest_checkout_rejects_an_email_that_already_has_an_account(): void
    {
        $existingCustomer = User::factory()->create(['email' => 'asha@example.com']);
        $product = $this->product();
        Cart::query()->create([
            'temp_user_id' => 'guest-duplicate',
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
        ]);
        $this->withSession([CartSession::SESSION_KEY => 'guest-duplicate']);

        Livewire::test(Checkout::class)
            ->set('name', 'Asha Mwinyi')
            ->set('email', 'asha@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('shippingAddress', '10 River Road')
            ->call('placeOrder')
            ->assertHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('carts', ['temp_user_id' => 'guest-duplicate', 'user_id' => null]);
        $this->assertSame($existingCustomer->id, User::query()->sole()->id);
    }

    public function test_signing_in_folds_the_guest_cart_into_the_account(): void
    {
        $customer = User::factory()->create(['email' => 'asha@example.com']);
        $product = $this->product();
        $customer->carts()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
        ]);
        Cart::query()->create([
            'temp_user_id' => 'guest-login',
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->withSession([CartSession::SESSION_KEY => 'guest-login'])
            ->post(route('login'), [
                'email' => 'asha@example.com',
                'password' => 'password',
            ]);

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'temp_user_id' => null,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }
}
