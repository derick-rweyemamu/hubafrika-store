<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Write the permission catalog from config/permissions.php into the
     * database. Existing rows are left untouched, so re-seeding never renames
     * or removes an ability a role is already using.
     */
    public function run(): void
    {
        $now = now();

        $permissions = collect(config('permissions.groups'))
            ->flatMap(static fn (array $group): array => array_keys($group))
            ->values()
            ->map(static fn (string $name): array => [
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('permissions')->insertOrIgnore($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
