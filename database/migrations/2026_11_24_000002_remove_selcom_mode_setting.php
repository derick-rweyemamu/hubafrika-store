<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('business_settings')->where('type', 'selcom_mode')->delete();
    }

    public function down(): void
    {
        if (DB::table('business_settings')->count() === 0) {
            return;
        }

        if (! DB::table('business_settings')->where('type', 'selcom_mode')->exists()) {
            DB::table('business_settings')->insert([
                'type' => 'selcom_mode',
                'value' => 'live',
                'lang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
