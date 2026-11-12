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
        if (Schema::hasTable('cms_menus') && Schema::hasColumn('cms_menus', 'description')) {
            Schema::table('cms_menus', function (Blueprint $table): void {
                $table->text('description')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cms_menus') && Schema::hasColumn('cms_menus', 'description')) {
            Schema::table('cms_menus', function (Blueprint $table): void {
                $table->string('description', 255)->nullable()->change();
            });
        }
    }
};
