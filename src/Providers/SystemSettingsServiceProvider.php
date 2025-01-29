<?php
namespace Venom\SystemSettings\Providers;

use Illuminate\Support\ServiceProvider;
use Venom\SystemSettings\Console\Commands\ClearSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\GetSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\HasSystemSettingsCommand;
use Venom\SystemSettings\Console\Commands\SetSystemSettingsCommand;
use Venom\SystemSettings\Services\SystemSettingsService;
use Illuminate\Support\Facades\Cache;

class SystemSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge the package configuration file with the application's copy.
        $this->mergeConfigFrom(__DIR__ . '/../../config/system_settings.php', 'system_settings');

        // Register the SystemSettingsService with dynamic model binding using Laravel’s service container.
        $this->app->singleton(SystemSettingsService::class, function ($app) {
            $model = $app['config']->get('system_settings.model', \Venom\SystemSettings\Models\SystemSettings::class);
            return $app->makeWith(SystemSettingsService::class, ['model' => new $model]);
        });

        // Configurable command registration
        if ($this->app->runningInConsole() && config('system_settings.enable_commands', true)) {
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

        // Publish configuration and migrations for user customization.
        $this->publishes([
            __DIR__ . '/../../config/system_settings.php' => config_path('system_settings.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Cache invalidation on boot when running migrations
        if ($this->app->runningInConsole()) {
            Cache::forget(config('system_settings.cache_key_prefix') . '_all');
        }
    }
}
