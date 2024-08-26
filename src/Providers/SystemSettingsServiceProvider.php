<?php

namespace  Venom\SystemSettings\Providers;

use Illuminate\Support\ServiceProvider;
use Venom\SystemSettings\Console\Commands\ClearSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\GetSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\SetSystemSettingsCommand;
use Venom\SystemSettings\Services\SystemSettingsService;

class SystemSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/system_settings.php', 'system_settings');
        $this->app->singleton('system_settings', function () {
            return new SystemSettingsService();
        });
        if ($this->app->runningInConsole()) {
            $this->commands([
                GetSystemSettingsCommand::class,
                SetSystemSettingsCommand::class,
                ClearSystemSettingsCommand::class,
            ]);
        }
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../config/system_settings.php' => config_path('system_settings.php'),
        ], 'config');


    }
}