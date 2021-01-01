<?php

namespace DigitalCreative\Jaqen\Tests;

use DigitalCreative\Jaqen\CoreJaqenServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrations();

    }

    protected function getPackageProviders($app): array
    {
        return [
            CoreJaqenServiceProvider::class,
            TestServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app[ 'config' ]->set('database.default', 'sqlite');
        $app[ 'config' ]->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Load the migrations for the test environment.
     *
     * @return void
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--path' => realpath(__DIR__ . '/Migrations'),
        ]);
    }
}
