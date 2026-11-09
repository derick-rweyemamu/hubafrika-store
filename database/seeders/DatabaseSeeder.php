<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: the role assignment and the CMS menu items reference the
     * rows seeded just before them.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ModelHasRoleSeeder::class,
            GeographySeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            BusinessSettingSeeder::class,
            CmsCategorySeeder::class,
            CmsPageSeeder::class,
            CmsPostSeeder::class,
            CmsMenuSeeder::class,
            CmsMenuItemSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
