<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Seed the product units of measure.
     */
    public function run(): void
    {
        DB::table('units')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Piece',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
            [
                'id' => 2,
                'name' => 'Kilogram',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
            [
                'id' => 3,
                'name' => 'Gram',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
            [
                'id' => 4,
                'name' => 'Litre',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
            [
                'id' => 5,
                'name' => 'Box',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
            [
                'id' => 6,
                'name' => 'Pack',
                'created_at' => '2026-11-05 19:31:51',
                'updated_at' => '2026-11-05 19:31:51',
            ],
        ]);
    }
}
