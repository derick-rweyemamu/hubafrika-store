<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30)->index();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('reference', 100)->nullable()->index();
            $table->string('payer_phone', 50)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->json('payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
