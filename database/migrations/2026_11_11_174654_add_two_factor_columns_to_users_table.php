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
        Schema::table('users', function (Blueprint $table): void {
            $table->text('two_factor_secret')->nullable()->after('remember_token');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->unsignedBigInteger('two_factor_last_totp_step')->nullable()->after('two_factor_confirmed_at');
            $table->string('two_factor_email_code_hash', 191)->nullable()->after('two_factor_last_totp_step');
            $table->timestamp('two_factor_email_code_expires_at')->nullable()->after('two_factor_email_code_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_confirmed_at',
                'two_factor_last_totp_step',
                'two_factor_email_code_hash',
                'two_factor_email_code_expires_at',
            ]);
        });
    }
};
