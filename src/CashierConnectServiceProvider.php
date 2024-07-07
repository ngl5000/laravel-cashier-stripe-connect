<?php

namespace Ngl5000\CashierConnect;

use Illuminate\Support\ServiceProvider;
use Ngl5000\CashierConnect\Console\ConnectWebhook;
use Laravel\Cashier\Cashier;

/**
 * Service provider for the package.
 *
 * @package Ngl5000\CashierConnect\Providers
 */
class CashierConnectServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->initializePublishing();
        $this->initializeCommands();
        $this->setupRoutes();
        $this->setupConfig();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cashierconnect.php', 'cashierconnect'
        );
    }

    /**
     * Register the package's publishable resources.
     */
    protected function initializePublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'cashier-connect-migrations');
            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations/tenant'),
            ], 'cashier-connect-tenancy-migrations');
        }
    }

    /**
     * Register the package's console commands.
     */
    protected function initializeCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConnectWebhook::class
            ]);
        }
    }

    /**
     * Register the package's console commands.
     */
    protected function setupRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');

    }

    /**
     * Register the package's config.
     */
    protected function setupConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/cashierconnect.php' => config_path('cashierconnect.php'),
        ]);
    }

}
