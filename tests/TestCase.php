<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Must happen before the framework boots: RefreshDatabase runs
        // `migrate:fresh` inside parent::setUp().
        $this->useInMemoryDatabase();

        parent::setUp();

        $this->assertTestingDatabase();
    }

    /**
     * Pin the suite to the in-memory SQLite connection. Reading the values from
     * .env or from an exported shell variable would let a leaked DB_CONNECTION
     * or DB_DATABASE point `migrate:fresh` at the local MySQL schema and erase
     * every table.
     */
    private function useInMemoryDatabase(): void
    {
        $values = [
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
        ];

        foreach ($values as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Confirm the application resolved what the suite pinned above.
     */
    private function assertTestingDatabase(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
            throw new RuntimeException(sprintf(
                'Tests resolved to [%s] on [%s]; expected sqlite on :memory:.',
                $connection->getDriverName(),
                $connection->getDatabaseName(),
            ));
        }
    }
}
