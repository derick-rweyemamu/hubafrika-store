<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRoleSeeder extends Seeder
{
    /**
     * Grant the administrator role to the seeded store owner. The ids are
     * looked up instead of assumed so this keeps working after a re-seed.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'admin')->where('guard_name', 'web')->value('id');
        $userId = DB::table('users')->where('email', 'rweyemamuderick@gmail.com')->value('id');

        if ($roleId === null || $userId === null) {
            return;
        }

        DB::table('model_has_roles')->insertOrIgnore([
            [
                'role_id' => $roleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
            ],
        ]);
    }
}
