<?php

namespace Dystcz\LunarPaypal\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Currency;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        activity()->disableLogging();

        Currency::factory()->create([
            'default' => true,
            'code' => 'USD',
        ]);
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Dystcz\LunarPaypal\LunarPaypalServiceProvider::class,

            \Srmklive\PayPal\Providers\PayPalServiceProvider::class,
            \Spatie\LaravelData\LaravelDataServiceProvider::class,

            // Lunar core
            \Lunar\LunarServiceProvider::class,
            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            \Cartalyst\Converter\Laravel\ConverterServiceProvider::class,
            \Spatie\LaravelBlink\BlinkServiceProvider::class,
            \Kalnoy\Nestedset\NestedSetServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        Config::set('lunar.paypal', require __DIR__.'/../config/paypal.php');

        /**
         * App configuration
         */
        Config::set('database.default', 'sqlite');

        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
