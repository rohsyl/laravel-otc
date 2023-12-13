<?php

namespace rohsyl\LaravelOtc\Tests;

use Orchestra\Testbench\TestCase;
use rohsyl\LaravelOtc\LaravelOtcServiceProvider;
use rohsyl\LaravelOtc\Tests\Models\User;

class LaravelOtcTestCase extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('cache.default', 'array');
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('otc.authenticatables.user', [
            'model'   => User::class,
            'identifier' => 'email',
        ]);
        $app['config']->set('otc.notifier_class', null);
        $app['config']->set('otc.notification_class', null);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelOtcServiceProvider::class,
        ];
    }
}
