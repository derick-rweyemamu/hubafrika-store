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
        Schema::create('business_settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('type')->unique();
            $table->longText('value')->nullable();
            $table->string('lang', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('cms_categories', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('cms_pages', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('cms_posts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('cms_categories')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('cms_menus', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('status')->default(true);
            $table->string('slug')->unique();
            $table->string('location', 50)->default('header');
            $table->string('css_class')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_menu_items', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('page_id')->nullable();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->longText('mega_menu_data')->nullable();
            $table->string('type', 30)->default('custom');
            $table->string('label');
            $table->string('badge', 50)->nullable();
            $table->string('badge_text', 50)->nullable();
            $table->string('badge_color', 30)->nullable()->default('#ef4444');
            $table->string('url')->nullable();
            $table->string('target', 10)->default('_self');
            $table->string('icon', 100)->nullable();
            $table->string('image')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->boolean('is_mega_menu')->default(false);
            $table->unsignedTinyInteger('mega_menu_columns')->default(4);
            $table->unsignedTinyInteger('mega_column')->default(1);
            $table->unsignedTinyInteger('mega_menu_column')->nullable();
            $table->string('mega_menu_type', 20)->default('columns');
            $table->string('mega_menu_image')->nullable();
            $table->string('mega_menu_image_position', 10)->default('left');
            $table->string('mega_menu_image_title')->nullable();
            $table->string('mega_menu_image_link')->nullable();

            $table->foreign('menu_id')->references('id')->on('cms_menus')->cascadeOnDelete();
            $table->foreign('page_id')->references('id')->on('cms_pages')->nullOnDelete();
            $table->foreign('post_id')->references('id')->on('cms_posts')->nullOnDelete();
            $table->foreign('parent_id')->references('id')->on('cms_menu_items')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_menu_items');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('cms_categories');
        Schema::dropIfExists('business_settings');
    }
};
