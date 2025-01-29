<?php
namespace Venom\SystemSettings\Providers;

use Illuminate\Support\ServiceProvider;
use Venom\SystemSettings\Console\Commands\ClearSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\GetSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\HasSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\SetSystemSettingsCommand;
use Venom\SystemSettings\Services\SystemSettingsService;

class SystemSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge the package configuration file with the application's copy.
        $this->mergeConfigFrom(__DIR__ . '/../../config/system_settings.php', 'system_settings');

        // Register the SystemSettingsService with dynamic model binding.
        $this->app->singleton(SystemSettingsService::class, function ($app) {
            // Get the model class from configuration, defaulting to the package's SystemSettings model.
            $model = $app['config']->get('system_settings.model', \Venom\SystemSettings\Models\SystemSettings::class);
            return new SystemSettingsService(new $model);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GetSystemSettingsCommand::class,
                SetSystemSettingsCommand::class,
                ClearSystemSettingsCommand::class,
                HasSystemSettingsCommand::class,
            ]);
        }
    }

    public function boot()
    {
        // Load the package's migrations.
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Publish configuration and migrations to allow user customization.
        $this->publishes([
            __DIR__ . '/../../config/system_settings.php' => config_path('system_settings.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }
}
