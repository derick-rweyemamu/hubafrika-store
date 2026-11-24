<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('business_settings')->count() === 0) {
            return;
        }

        $now = now();

        if (! DB::table('business_settings')->where('type', 'selcom_payment_methods')->exists()) {
            DB::table('business_settings')->insert([
                'type' => 'selcom_payment_methods',
                'value' => 'MOBILEMONEY',
                'lang' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Selcom does not provide a Mobile Money sandbox; default unconfigured stores to simulation.
        $mode = DB::table('business_settings')->where('type', 'selcom_mode')->value('value');
        $apiKey = DB::table('business_settings')->where('type', 'selcom_api_key')->value('value');

        if ($mode === 'sandbox' && blank($apiKey)) {
            DB::table('business_settings')
                ->where('type', 'selcom_mode')
                ->update(['value' => 'simulation', 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('business_settings')->where('type', 'selcom_payment_methods')->delete();
    }
};
