<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Settings the payment screens need to exist before they can be edited.
     *
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'lipa_enabled' => '1',
        'lipa_number' => '',
        'lipa_business_name' => '',
        'lipa_instructions' => 'Dial *150*00#, choose "Lipa kwa M-Pesa", enter the Lipa number above, then the amount. Paste the transaction ID from the confirmation SMS into the field below.',
        'lipa_requires_verification' => '1',
        'lipa_webhook_secret' => '',
    ];

    public function up(): void
    {
        // Fresh installs receive these rows from BusinessSettingSeeder, which
        // seeds fixed ids. Only existing stores are topped up here.
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
