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
        Schema::create('permissions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('guard_name', 191);
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'permissions_name_guard_unique');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('guard_name', 191);
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'roles_name_guard_unique');
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type', 191);
            $table->unsignedBigInteger('model_id');

            $table->primary(['permission_id', 'model_id', 'model_type']);
            $table->index(['model_type', 'model_id'], 'model_has_permissions_model_index');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type', 191);
            $table->unsignedBigInteger('model_id');

            $table->primary(['role_id', 'model_id', 'model_type']);
            $table->index(['model_type', 'model_id'], 'model_has_roles_model_index');
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['permission_id', 'role_id']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
