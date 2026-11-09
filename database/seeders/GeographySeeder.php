<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeographySeeder extends Seeder
{
    /**
     * Seed countries, states and cities from the dataset in database/seeders/data.
     *
     * The rows are stored as compact JSON lists so the seeder itself stays
     * readable:
     *   countries: [id, name, code]
     *   states:    [id, country_id, name]
     *   cities:    [id, state_id, country_id, name]
     */
    public function run(): void
    {
        $this->seedTable('countries', 'geo-countries.json', fn (array $row): array => [
            'id' => $row[0],
            'name' => $row[1],
            'code' => $row[2],
            'status' => 1,
        ]);

        $this->seedTable('states', 'geo-states.json', fn (array $row): array => [
            'id' => $row[0],
            'country_id' => $row[1],
            'name' => $row[2],
            'status' => 1,
        ]);

        $this->seedTable('cities', 'geo-cities.json', fn (array $row): array => [
            'id' => $row[0],
            'state_id' => $row[1],
            'country_id' => $row[2],
            'name' => $row[3],
            'cost' => 0,
            'status' => 1,
        ]);
    }

    /**
     * @param  callable(array<int, mixed>): array<string, mixed>  $map
     */
    private function seedTable(string $table, string $file, callable $map): void
    {
        $path = database_path('seeders/data/'.$file);

        if (! is_file($path)) {
            $this->command?->warn("Skipping {$table}: {$file} is missing.");

            return;
        }

        /** @var array<int, array<int, mixed>> $rows */
        $rows = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insertOrIgnore(array_map($map, $chunk));
        }

        $this->command?->info(sprintf('Seeded %s %s.', number_format(count($rows)), $table));
    }
}
