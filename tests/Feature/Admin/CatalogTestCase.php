<?php

namespace Tests\Feature\Admin;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class CatalogTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->integer('verification_status')->default(1);
                $table->string('password');
                $table->rememberToken();
                $table->boolean('is_admin')->default(false);
                $table->boolean('banned')->default(false);
                $table->string('phone', 20)->nullable();
                $table->string('address', 300)->nullable();
                $table->string('country', 30)->nullable();
                $table->string('state', 30)->nullable();
                $table->string('city', 30)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->timestamps();
            });
        } else {
            foreach ([
                'is_admin' => fn (Blueprint $table) => $table->boolean('is_admin')->default(false),
                'banned' => fn (Blueprint $table) => $table->boolean('banned')->default(false),
                'verification_status' => fn (Blueprint $table) => $table->integer('verification_status')->default(1),
                'phone' => fn (Blueprint $table) => $table->string('phone', 20)->nullable(),
                'address' => fn (Blueprint $table) => $table->string('address', 300)->nullable(),
                'country' => fn (Blueprint $table) => $table->string('country', 30)->nullable(),
                'state' => fn (Blueprint $table) => $table->string('state', 30)->nullable(),
                'city' => fn (Blueprint $table) => $table->string('city', 30)->nullable(),
                'postal_code' => fn (Blueprint $table) => $table->string('postal_code', 20)->nullable(),
            ] as $column => $addColumn) {
                if (! Schema::hasColumn('users', $column)) {
                    Schema::table('users', $addColumn);
                }
            }
        }

        if (! Schema::hasTable('addresses')) {
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
                $table->boolean('set_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('type')->unique();
                $table->longText('value')->nullable();
                $table->string('lang')->nullable();
                $table->timestamps();
            });
        }

        // The catalog, permission and CMS tables are now created by the real
        // migrations, so there is nothing left to build by hand.
        if (Schema::hasTable('product_categories')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('permission_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('level')->default(0);
            $table->string('name', 50);
            $table->integer('order_level')->default(0);
            $table->double('discount')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('top')->default(false);
            $table->boolean('digital')->default(false);
            $table->string('banner', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('cover_image', 100)->nullable();
            $table->string('slug')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('logo', 100)->nullable();
            $table->integer('top')->default(0);
            $table->string('slug')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 191);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 200);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('brand_id')->nullable();
            $table->unsignedInteger('unit')->nullable();
            $table->string('photos', 2000)->nullable();
            $table->string('thumbnail_img', 100)->nullable();
            $table->longText('description')->nullable();
            $table->double('unit_price');
            $table->double('purchase_price')->nullable();
            $table->integer('published')->default(1);
            $table->boolean('approved')->default(true);
            $table->integer('featured')->default(0);
            $table->integer('digital')->default(0);
            $table->integer('variant_product')->default(0);
            $table->boolean('cash_on_delivery')->default(false);
            $table->integer('current_stock')->default(0);
            $table->integer('num_of_sale')->default(0);
            $table->integer('min_qty')->default(1);
            $table->double('discount')->default(0);
            $table->string('discount_type', 10)->default('amount');
            $table->double('tax')->nullable();
            $table->string('tax_type', 10)->nullable();
            $table->text('variations')->nullable();
            $table->string('barcode')->nullable();
            $table->string('tags', 500)->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('category_id');
            $table->primary(['product_id', 'category_id']);
        });
    }
}
