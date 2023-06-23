<?php

namespace Dystcz\LunarPaypal\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Dystcz\LunarPaypal\LunarPaypalServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Models\Currency;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Srmklive\PayPal\Providers\PayPalServiceProvider;

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
            LunarPaypalServiceProvider::class,
            PayPalServiceProvider::class,
            LaravelDataServiceProvider::class,

            // Lunar core
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            BlinkServiceProvider::class,
            NestedSetServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        Config::set('lunar.paypal', [
            'sandbox' => [
                'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
                'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
                'app_id' => 'APP-80W284485P519543T',
            ],

            'return_url' => env('PAYPAL_RETURN_URL', ''),
            'cancel_url' => env('PAYPAL_CANCEL_URL', ''),
        ]);

        // Set our config for the srmklive/paypal package.
        Config::set('paypal', [
            ...Config::get('paypal'),
            ...Config::get('lunar.paypal'),
        ]);

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
