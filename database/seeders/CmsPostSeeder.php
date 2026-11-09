<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsPostSeeder extends Seeder
{
    /**
     * Seed the CMS posts.
     */
    public function run(): void
    {
        DB::table('cms_posts')->insertOrIgnore([
            [
                'id' => 1,
                'category_id' => 2,
                'title' => 'test',
                'slug' => 'test',
                'content' => '{"version":2,"published":true,"project_data":[],"html":"","css":""}',
                'created_by' => 1,
                'created_at' => '2026-11-05 20:42:19',
                'updated_at' => '2026-11-06 00:40:00',
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'title' => 'test2',
                'slug' => 'test2',
                'content' => '{"version":2,"published":true,"project_data":[],"html":"","css":""}',
                'created_by' => 1,
                'created_at' => '2026-11-05 20:42:50',
                'updated_at' => '2026-11-06 00:48:25',
            ],
        ]);
    }
}
