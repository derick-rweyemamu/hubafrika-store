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
        Schema::create('brands', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('logo', 100)->nullable();
            $table->integer('top')->default(0);
            $table->string('slug')->nullable()->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 191);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('level')->default(0);
            $table->string('name', 50);
            $table->integer('order_level')->default(0);
            $table->double('discount')->default(0);
            $table->integer('discount_start_date')->nullable();
            $table->integer('discount_end_date')->nullable();
            $table->string('banner', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('cover_image', 100)->nullable();
            $table->integer('featured')->default(0);
            $table->enum('hot_category', ['0', '1'])->default('0');
            $table->integer('top')->default(0);
            $table->integer('digital')->default(0);
            $table->string('slug')->nullable()->unique();
            $table->unsignedInteger('refund_request_time')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();

            $table->index('parent_id');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 200);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('brand_id')->nullable();
            $table->string('photos', 2000)->nullable();
            $table->string('thumbnail_img', 100)->nullable();
            $table->string('short_video')->nullable();
            $table->string('short_video_thumbnail')->nullable();
            $table->string('video_provider', 20)->nullable();
            $table->longText('video_link')->nullable();
            $table->string('tags', 500)->nullable();
            $table->longText('description')->nullable();
            $table->double('unit_price');
            $table->double('purchase_price')->nullable();
            $table->integer('variant_product')->default(0);
            $table->string('attributes', 1000)->default('[]');
            $table->mediumText('choice_options')->nullable();
            $table->mediumText('colors')->nullable();
            $table->text('variations')->nullable();
            $table->string('custom_label_id', 1000)->nullable();
            $table->integer('todays_deal')->default(0);
            $table->integer('published')->default(1);
            $table->boolean('draft')->default(false);
            $table->boolean('promotional')->default(false);
            $table->boolean('approved')->default(true);
            $table->string('stock_visibility_state', 10)->default('quantity');
            $table->boolean('cash_on_delivery')->default(false);
            $table->integer('featured')->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('old_unit', 191)->nullable();
            $table->unsignedInteger('unit')->nullable();
            $table->double('weight')->default(0);
            $table->integer('min_qty')->default(1);
            $table->integer('low_stock_quantity')->nullable();
            $table->double('discount')->default(0);
            $table->string('discount_type', 10)->default('amount');
            $table->integer('discount_start_date')->nullable();
            $table->integer('discount_end_date')->nullable();
            $table->double('tax')->nullable();
            $table->string('tax_type', 10)->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->double('gst_rate')->nullable()->default(0);
            $table->string('shipping_type', 20)->nullable()->default('flat_rate');
            $table->double('shipping_cost')->default(0);
            $table->boolean('is_quantity_multiplied')->default(false);
            $table->string('est_shipping_days', 20)->nullable();
            $table->boolean('show_estimated_shipping_time')->nullable()->default(false);
            $table->unsignedBigInteger('shipping_note_id')->nullable();
            $table->boolean('show_shipping_note')->nullable()->default(false);
            $table->integer('num_of_sale')->default(0);
            $table->mediumText('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('meta_img')->nullable();
            $table->string('pdf')->nullable();
            $table->string('slug')->unique();
            $table->double('rating')->default(0);
            $table->string('barcode')->nullable();
            $table->integer('digital')->default(0);
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_link', 500)->nullable();
            $table->string('external_link_btn')->nullable()->default('Buy Now');
            $table->string('frequently_bought_selection_type', 19)->nullable()->default('product');
            $table->tinyInteger('has_warranty')->default(0);
            $table->unsignedInteger('warranty_id')->nullable();
            $table->unsignedInteger('warranty_note_id')->nullable();
            $table->boolean('show_warranty_note')->nullable()->default(true);
            $table->unsignedBigInteger('delivery_note_id')->nullable();
            $table->boolean('show_delivery_notes')->nullable()->default(false);
            $table->timestamps();

            $table->index('category_id', 'idx_products_category_id');
            $table->index('brand_id', 'idx_products_brand_id');
            $table->index(['published', 'approved'], 'idx_products_published');
            $table->index('featured', 'idx_products_featured');
            $table->index('name', 'idx_products_name');
            $table->index('barcode', 'idx_products_barcode');
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('unit', 'products_unit_id_foreign')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('product_stocks', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('variant');
            $table->string('sku')->nullable()->index();
            $table->double('price')->default(0);
            $table->integer('qty')->default(0);
            $table->unsignedInteger('image')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('category_id');

            $table->primary(['product_id', 'category_id']);
            $table->index('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('type', 10)->default('real');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('custom_reviewer_name', 100)->nullable();
            $table->string('custom_reviewer_image', 100)->nullable();
            $table->integer('rating')->default(0);
            $table->mediumText('comment');
            $table->string('photos', 191)->nullable();
            $table->integer('status')->default(1);
            $table->integer('viewed')->default(0);
            $table->boolean('created_at_is_custom')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index('user_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
    }
};
