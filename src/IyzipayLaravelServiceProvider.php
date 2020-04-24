<?php

namespace Afbora\IyzipayLaravel;

use Afbora\IyzipayLaravel\Commands\SubscriptionChargeCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class IyzipayLaravelServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/iyzipay.php' => config_path('iyzipay.php')
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('iyzipay:subscription_charge')->daily();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SubscriptionChargeCommand::class
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/iyzipay.php',
            'iyzipay'
        );

        $this->app->bind('iyzipay-laravel', function () {
            return new IyzipayLaravel();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['iyzipay-laravel'];
    }
}
