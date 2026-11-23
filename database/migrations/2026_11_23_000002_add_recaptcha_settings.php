<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULTS = [
        'recaptcha_enabled' => '0',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
        'recaptcha_min_score' => '0.5',
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
