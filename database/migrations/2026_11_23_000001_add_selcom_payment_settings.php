<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULTS = [
        'selcom_enabled' => '1',
        'selcom_mode' => 'sandbox',
        'selcom_vendor_id' => 'TEST-VENDOR',
        'selcom_api_key' => '',
        'selcom_api_secret' => '',
        'selcom_instructions' => 'Pay securely via Selcom using M-Pesa, Tigo Pesa, Airtel Money, Halopesa, or Card.',
    ];

    public function up(): void
    {
        if (DB::table('business_settings')->count() === 0) {
            return;
        }

        $now = now();

        foreach (self::DEFAULTS as $type => $value) {
            if (DB::table('business_settings')->where('type', $type)->exists()) {
                continue;
            }

            DB::table('business_settings')->insert([
                'type' => $type,
                'value' => $value,
                'lang' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('business_settings')->whereIn('type', array_keys(self::DEFAULTS))->delete();
    }
};
