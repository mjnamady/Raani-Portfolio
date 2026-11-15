<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Boot the application, then refuse to run if it is not pointed at the test database.
     *
     * RefreshDatabase wipes the database before each test. If the config is cached
     * (e.g. after `php artisan optimize`) or a DB_URL is set in the environment,
     * phpunit.xml's test settings can be ignored and the tests would wipe the
     * development database instead. This check runs before any trait, so it stops
     * the run first. It inspects the resolved connection, so a DB_URL cannot hide
     * the real driver behind a "sqlite" config value.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $name = config('database.default');
        $url = config("database.connections.{$name}.url");
        $connection = DB::connection();

        $safe = $this->app->environment('testing')
            && blank($url)
            && $connection->getDriverName() === 'sqlite'
            && $connection->getDatabaseName() === ':memory:';

        if (! $safe) {
            throw new RuntimeException(sprintf(
                'Refusing to run tests against [%s] database [%s] in [%s] environment%s. '
                .'Run `php artisan config:clear` (cached config ignores phpunit.xml) and unset any DB_URL, then try again.',
                $connection->getDriverName(),
                $connection->getDatabaseName(),
                $this->app->environment(),
                blank($url) ? '' : ' (DB_URL is set)',
            ));
        }
    }
}
