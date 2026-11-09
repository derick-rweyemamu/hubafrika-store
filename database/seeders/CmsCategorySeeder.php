<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsCategorySeeder extends Seeder
{
    /**
     * Seed the CMS post categories.
     */
    public function run(): void
    {
        DB::table('cms_categories')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'News',
                'slug' => 'news',
                'created_at' => '2026-11-06 00:37:58',
                'updated_at' => '2026-11-06 00:37:58',
            ],
            [
                'id' => 2,
                'name' => 'Events',
                'slug' => 'events',
                'created_at' => '2026-11-06 00:38:26',
                'updated_at' => '2026-11-06 00:38:26',
            ],
            [
                'id' => 3,
                'name' => 'test',
                'slug' => 'test',
                'created_at' => '2026-11-06 00:47:50',
                'updated_at' => '2026-11-06 00:47:50',
            ],
        ]);
    }
}
