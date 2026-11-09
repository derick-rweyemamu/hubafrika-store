<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the store administrator.
     *
     * The password below is a development credential: it is public in this
     * repository, so change it from the profile screen after the first login.
     */
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Store Admin',
                'email' => 'rweyemamuderick@gmail.com',
                'email_verified_at' => '2026-11-05 19:31:51',
                'password' => Hash::make('password'),
                'is_admin' => 1,
                'remember_token' => null,
                'device_token' => null,
                'verification_code' => null,
                'verification_status' => 1,
                'provider' => null,
                'provider_id' => null,
                'access_token' => null,
                'refresh_token' => null,
                'avatar' => null,
                'avatar_original' => null,
                'address' => null,
                'country' => null,
                'state' => null,
                'city' => null,
                'postal_code' => null,
                'phone' => null,
                'otp_activation_purchase_cod_wallet' => 0,
                'otp_alert_seen' => 0,
                'balance' => 0.0,
                'banned' => 0,
                'is_suspicious' => 0,
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 16:46:21',
            ],
        ]);
    }
}
