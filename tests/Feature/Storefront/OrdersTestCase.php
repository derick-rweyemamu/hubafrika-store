<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Admin\CatalogTestCase;

abstract class OrdersTestCase extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Product fixtures below reference category 1, and the products table
        // now enforces that foreign key.
        Category::query()->forceCreate(['id' => 1, 'name' => 'General', 'slug' => 'general']);

        if (! Schema::hasTable('product_stocks')) {
            Schema::create('product_stocks', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->string('variant');
                $table->string('sku')->nullable();
                $table->double('price')->default(0);
                $table->integer('qty')->default(0);
                $table->unsignedInteger('image')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table): void {
                $table->increments('id');
                $table->boolean('status')->default(true);
                $table->unsignedInteger('user_id')->nullable();
                $table->string('temp_user_id')->nullable();
                $table->unsignedInteger('address_id')->default(0);
                $table->unsignedInteger('billing_address')->default(0);
                $table->unsignedInteger('product_id')->nullable();
                $table->text('variation')->nullable();
                $table->double('price')->default(0);
                $table->double('tax')->default(0);
                $table->double('shipping_cost')->default(0);
                $table->string('shipping_type', 30)->default('');
                $table->unsignedInteger('pickup_point')->nullable();
                $table->unsignedInteger('carrier_id')->nullable();
                $table->double('discount')->default(0);
                $table->string('product_referral_code')->nullable();
                $table->string('coupon_code')->nullable();
                $table->boolean('coupon_applied')->default(false);
                $table->integer('quantity')->default(0);
                $table->timestamps();
            });
        }

        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'USD']);
        BusinessSetting::query()->create(['type' => 'cash_on_delivery', 'value' => '1']);
        BusinessSetting::query()->create(['type' => 'flat_rate_shipping_cost', 'value' => '5']);
    }

    /**
     * Enable the Vodacom Lipa method; an empty number keeps it hidden.
     */
    protected function enableLipa(string $number = '123456', string $secret = ''): void
    {
        foreach ([
            'lipa_enabled' => '1',
            'lipa_number' => $number,
            'lipa_business_name' => 'Hubafrika Store',
            'lipa_instructions' => 'Pay with M-Pesa then paste the SMS transaction ID.',
            'lipa_requires_verification' => '1',
            'lipa_webhook_secret' => $secret,
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value, 'lang' => null]);
        }
    }

    protected function product(): Product
    {
        return Product::query()->firstOrCreate(
            ['slug' => 'solar-kit'],
            [
                'name' => 'Solar Kit',
                'category_id' => 1,
                'unit_price' => 100,
                'current_stock' => 5,
                'published' => true,
            ],
        );
    }

    protected function customerWithCart(): User
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $customer->carts()->create([
            'product_id' => $this->product()->id,
            'quantity' => 1,
            'price' => 0,
            'tax' => 0,
        ]);

        return $customer;
    }

    /**
     * Build an order that is waiting for a Lipa transaction ID to be verified.
     */
    protected function placedLipaOrder(User $customer, string $reference, ?Product $product = null): Order
    {
        $product ??= $this->product();

        $order = $customer->orders()->create([
            'order_number' => 'ORD-LIPA-'.$reference,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '0754000000',
            'status' => 'pending',
            'payment_method' => Payment::METHOD_VODACOM_LIPA,
            'payment_status' => 'awaiting_verification',
            'payment_reference' => $reference,
            'currency' => 'TZS',
            'subtotal' => 100,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'total' => 100,
            'shipping_address' => '1 Example Road',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 100,
        ]);

        $order->payments()->create([
            'method' => Payment::METHOD_VODACOM_LIPA,
            'amount' => 100,
            'currency' => 'TZS',
            'reference' => $reference,
            'payer_phone' => '0754000000',
            'status' => Payment::STATUS_AWAITING_VERIFICATION,
            'submitted_at' => now(),
        ]);

        return $order;
    }
}
