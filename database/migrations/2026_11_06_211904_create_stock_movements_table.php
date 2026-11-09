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
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_stock_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->integer('quantity');
            $table->integer('balance');
            $table->string('reason', 30)->index();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('product_stock_id')->references('id')->on('product_stocks')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
