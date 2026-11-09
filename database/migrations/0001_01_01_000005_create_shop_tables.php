<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('user_id')->nullable();
            $table->string('temp_user_id')->nullable();
            $table->unsignedInteger('address_id')->default(0);
            $table->unsignedInteger('billing_address')->default(0);
            $table->unsignedInteger('product_id')->nullable();
            $table->text('variation')->nullable();
            $table->double('price')->nullable()->default(0);
            $table->double('tax')->nullable()->default(0);
            $table->double('shipping_cost')->default(0);
            $table->string('shipping_type', 30)->default('');
            $table->unsignedInteger('pickup_point')->nullable();
            $table->unsignedInteger('carrier_id')->nullable();
            $table->double('discount')->default(0);
            $table->string('product_referral_code')->nullable();
            $table->string('coupon_code')->nullable();
            $table->tinyInteger('coupon_applied')->default(0);
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('temp_user_id');
            $table->index('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('wishlists', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('product_id');
            $table->timestamps();

            $table->unique(['user_id', 'product_id'], 'wishlists_user_product_unique');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('address')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->double('longitude')->nullable();
            $table->double('latitude')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->integer('set_default')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('customer_name', 191);
            $table->string('customer_email', 191);
            $table->string('customer_phone', 50)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_method', 30)->default('cash_on_delivery');
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('payment_reference', 100)->nullable();
            $table->char('currency', 3);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('shipping_address');
            $table->string('shipping_country', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_postal_code', 30)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('product_stock_id')->nullable();
            $table->string('product_name', 200);
            $table->string('sku')->nullable();
            $table->string('variant')->nullable();
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_stock_id')->references('id')->on('product_stocks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('carts');
    }
};
