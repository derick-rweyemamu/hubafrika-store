<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Contact and social settings seeded for storefront topbar, footer, and WhatsApp widget.
     *
     * @var array<int, string>
     */
    private const TYPES = [
        'contact_email',
        'contact_phone',
        'whatsapp_number',
        'social_facebook',
        'social_instagram',
        'social_x',
        'social_youtube',
        'social_linkedin',
        'social_tiktok',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('business_settings')->count() === 0) {
            return;
        }

        $now = now();

        foreach (self::TYPES as $type) {
            if (! DB::table('business_settings')->where('type', $type)->exists()) {
                DB::table('business_settings')->insert([
                    'type' => $type,
                    'value' => '',
                    'lang' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('business_settings')->whereIn('type', self::TYPES)->delete();
    }
};
