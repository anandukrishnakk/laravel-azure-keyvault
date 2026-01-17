<?php

namespace AnanduKrishna\AzureKeyVault;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AzureKeyVaultServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/azure-keyvault.php', 'azure-keyvault');

        $this->app->singleton(AzureKeyVaultService::class, function ($app) {
            return new AzureKeyVaultService();
        });

        // Skip CLI if needed
        if (app()->runningInConsole() && !config('azure-keyvault.key_vault.cli_enabled', false)) {
            return;
        }

        $vault = $this->app->make(AzureKeyVaultService::class);

        $dbOverrides = [];

        $userSecret = config('azure-keyvault.database_secrets.username');
        if ($userSecret) {
            $user = $vault->get($userSecret);
            if ($user) {
                $dbOverrides['database.connections.pgsql.username'] = $user;
            }
        }

        if (config('database.connections.pgsql.password') === 'MANAGED_IDENTITY') {
            $token = AzureTokenService::getAccessToken(
                'https://ossrdbms-aad.database.windows.net'
            );

            if ($token) {
                $dbOverrides['database.connections.pgsql.password'] = $token;
            }
        } else {
            $passSecret = config('azure-keyvault.database_secrets.password');
            if ($passSecret) {
                $pass = $vault->get($passSecret);
                if ($pass) {
                    $dbOverrides['database.connections.pgsql.password'] = $pass;
                }
            }
        }

        if ($dbOverrides) {
            config($dbOverrides);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/azure-keyvault.php' => config_path('azure-keyvault.php'),
            ], 'config');

            $this->commands([
                Console\TestAzureKeyVault::class,
            ]);
        }

        if (config('azure-keyvault.key_vault.auto_reconnect', true)) {
            try {
                DB::purge('pgsql');
            } catch (\Throwable $e) {
                // Ignore if DB not configured yet
            }
        }
    }
}
