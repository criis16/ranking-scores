<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static bool $testDatabaseEnsured = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureTestDatabaseExists();
    }

    private function ensureTestDatabaseExists(): void
    {
        if (self::$testDatabaseEnsured || config('database.default') !== 'pgsql') {
            return;
        }

        $config = config('database.connections.pgsql');

        $maintenance = new \PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=postgres', $config['host'], $config['port']),
            $config['username'],
            $config['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $statement = $maintenance->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
        $statement->execute(['name' => $config['database']]);

        if ($statement->fetchColumn() === false) {
            $maintenance->exec(
                sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $config['database']))
            );
        }

        self::$testDatabaseEnsured = true;
    }
}
