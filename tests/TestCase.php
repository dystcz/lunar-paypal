<?php

namespace Dystcz\LunarApi\Tests;

use Dystcz\LunarPaypal\LunarPaypalServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LunarPaypalServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        /**
         * App configuration
         */
        Config::set('database.default', 'sqlite');

        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Config::set('services.paypal', [
        //     'public_key' => env('STRIPE_PUBLIC_KEY'),
        //     'key' => env('STRIPE_SECRET_KEY'),
        //     'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // ]);
    }
}
