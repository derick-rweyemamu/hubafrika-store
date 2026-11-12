<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Starting roles. The administrator role is special: its members bypass
     * every permission check, so it is seeded with the whole catalog only so
     * the Roles screen can show what it stands for.
     *
     * @var array<string, array<int, string>|string>
     */
    private array $roles = [
        'admin' => '*',
        'manager' => [
            'products.view', 'products.manage',
            'categories.view', 'categories.manage',
            'brands.view', 'brands.manage',
            'inventory.view', 'inventory.manage',
            'units.view', 'units.manage',
            'orders.view', 'orders.manage',
            'customers.view', 'customers.manage',
            'subscribers.view', 'subscribers.manage',
            'reports.view',
            'cms.manage',
            'media.view',
        ],
        'content editor' => [
            'cms.manage',
            'media.view',
        ],
        'customer' => [],
    ];

    /**
     * Seed the starting roles and their permissions. Permissions are only
     * attached while a role has none, so re-seeding an existing database never
     * throws away changes made on the Roles screen.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $catalog = collect(config('permissions.groups'))
            ->flatMap(static fn (array $group): array => array_keys($group))
            ->values()
            ->all();

        foreach ($this->roles as $name => $permissions) {
            $role = Role::findOrCreate($name, 'web');

            if ($permissions === '*') {
                $role->syncPermissions($catalog);

                continue;
            }

            if ($role->permissions()->exists()) {
                continue;
            }

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
