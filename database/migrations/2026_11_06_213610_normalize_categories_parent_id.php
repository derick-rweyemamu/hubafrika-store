<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Legacy data stored "no parent" as 0, while the application writes NULL.
        DB::table('categories')->where('parent_id', 0)->update(['parent_id' => null]);

        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('parent_id')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * The data normalisation is not reversed: both 0 and NULL always meant
     * "top level category", so there is nothing meaningful to restore.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('parent_id')->nullable()->default(0)->change();
        });
    }
};
