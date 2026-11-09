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
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedInteger('country_id');
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->double('cost')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('state_id');
            $table->index('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
