<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsMenuSeeder extends Seeder
{
    /**
     * Seed the CMS menus and their items.
     */
    public function run(): void
    {
        DB::table('cms_menus')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Header',
                'description' => null,
                'status' => 1,
                'slug' => 'header',
                'location' => 'header',
                'css_class' => null,
                'created_at' => '2026-11-05 19:04:40',
                'updated_at' => '2026-11-05 19:04:40',
            ],
            [
                'id' => 2,
                'name' => 'Footer',
                'description' => null,
                'status' => 1,
                'slug' => 'footer',
                'location' => 'header',
                'css_class' => null,
                'created_at' => '2026-11-05 19:04:40',
                'updated_at' => '2026-11-05 19:04:40',
            ],
            [
                'id' => 3,
                'name' => 'Store Menu',
                'description' => 'Navigation for logged-in store shoppers',
                'status' => 1,
                'slug' => 'store',
                'location' => 'store',
                'css_class' => null,
                'created_at' => '2026-11-06 11:38:08',
                'updated_at' => '2026-11-06 18:01:15',
            ],
        ]);
    }
}
