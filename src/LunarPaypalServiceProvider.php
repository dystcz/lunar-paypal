<?php

namespace Dystcz\LunarPaypal;

use Dystcz\LunarPaypal\Managers\PaypalManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;

class LunarPaypalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Register our payment type.
        Payments::extend(
            'paypal',
            fn ($app) => $app->make(PaypalPaymentType::class)
        );

        $this->app->singleton(
            'gc:paypal',
            fn ($app) => $app->make(PaypalManager::class)
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/paypal.php' => config_path('lunar/paypal.php'),
            ], 'lunar.paypal.config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/paypal.php', 'lunar.paypal');
    }
}
