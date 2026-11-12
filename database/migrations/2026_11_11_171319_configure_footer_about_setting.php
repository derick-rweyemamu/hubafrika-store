<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SETTING_TYPE = 'footer_about';

    private const DEFAULT_VALUE = 'Delivering reliable solar power, backup batteries, water pumping, and clean energy solutions tailored for homes and businesses across East Africa.';

    public function up(): void
    {
        if (DB::table('business_settings')->count() === 0) {
            return;
        }

        if (DB::table('business_settings')->where('type', self::SETTING_TYPE)->exists()) {
            return;
        }

        $now = now();

        DB::table('business_settings')->insert([
            'type' => self::SETTING_TYPE,
            'value' => self::DEFAULT_VALUE,
            'lang' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('business_settings')->where('type', self::SETTING_TYPE)->delete();
    }
};
