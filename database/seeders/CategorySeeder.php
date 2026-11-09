<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seed the storefront product categories.
     */
    public function run(): void
    {
        DB::table('categories')->insertOrIgnore([
            [
                'id' => 1,
                'parent_id' => null,
                'level' => 0,
                'name' => 'Solar Accessory',
                'order_level' => 0,
                'discount' => 0.0,
                'discount_start_date' => null,
                'discount_end_date' => null,
                'banner' => null,
                'icon' => null,
                'cover_image' => null,
                'featured' => 0,
                'hot_category' => '0',
                'top' => 0,
                'digital' => 0,
                'slug' => 'solar-accessory',
                'refund_request_time' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
                'created_at' => '2026-11-05 21:19:37',
                'updated_at' => '2026-11-05 21:19:37',
            ],
            [
                'id' => 2,
                'parent_id' => null,
                'level' => 0,
                'name' => 'Power Storage',
                'order_level' => 0,
                'discount' => 0.0,
                'discount_start_date' => null,
                'discount_end_date' => null,
                'banner' => null,
                'icon' => null,
                'cover_image' => null,
                'featured' => 0,
                'hot_category' => '0',
                'top' => 0,
                'digital' => 0,
                'slug' => 'power-storage',
                'refund_request_time' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
                'created_at' => '2026-11-05 21:20:45',
                'updated_at' => '2026-11-05 21:20:45',
            ],
            [
                'id' => 3,
                'parent_id' => null,
                'level' => 0,
                'name' => 'conversion',
                'order_level' => 0,
                'discount' => 0.0,
                'discount_start_date' => null,
                'discount_end_date' => null,
                'banner' => null,
                'icon' => null,
                'cover_image' => null,
                'featured' => 0,
                'hot_category' => '0',
                'top' => 0,
                'digital' => 0,
                'slug' => 'conversion',
                'refund_request_time' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
                'created_at' => '2026-11-05 21:21:03',
                'updated_at' => '2026-11-05 21:21:03',
            ],
        ]);
    }
}
