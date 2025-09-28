<?php

/**
 * Service provider for the SystemSettings package.
 *
 * This provider registers and bootstraps the package, including configuration,
 * migrations, and console commands. It also handles the binding for the
 * SystemSettings service.
 */

namespace Venom\SystemSettings\Providers;

use /**
 * Illuminate\Support\ServiceProvider is the base class for all service providers in Laravel.
 * It provides foundational methods and functionalities for binding services into the container
 * and bootstrapping application resources.
 *
 * Service providers are used to register bindings, listeners, middleware, event subscriptions,
 * or any other functionality that needs to be injected into the Laravel framework.
 *
 * Features:
 * - Ability to register services or bindings in the application service container.
 * - Bootstrapping of any custom functionality after all services are registered.
 * - Deferred service providers for performance optimization by loading resources on demand.
 *
 * Methods Overview:
 * - register(): Register services or components into the container.
 * - boot(): Perform any actions required after all services are registered.
 * - provides(): Specify the services provided by the provider.
 *
 * This class is typically extended by custom service providers to define their
 * own registration and bootstrap logic.
 */
    Illuminate\Support\ServiceProvider;
use /**
 * Command class that provides functionality to clear system settings.
 *
 * This class is part of the system settings module and is responsible
 * for handling the logic required to clear specific or all system
 * settings from the application.
 *
 * It typically interacts with the database or storage layer to remove
 * the stored system settings and ensures that the application continues
 * to operate without the cleared settings.
 *
 * Usage of this command is primarily through the console and it is
 * intended to assist developers or system administrators in managing
 * application settings during runtime or maintenance operations.
 */
    Venom\SystemSettings\Console\Commands\ClearSystemSettingsCommand;
use /**
 * The GetSystemSettingsCommand class is responsible for retrieving
 * and displaying the system settings in the application.
 *
 * This command interacts with the application's system settings
 * and outputs the required configuration data to the console.
 *
 * Key responsibilities include:
 * - Fetching system settings from the relevant data stores or services.
 * - Formatting and displaying the retrieved settings in a user-friendly manner.
 * - Allowing administrators or developers to view system configurations via the command line.
 *
 * It is designed to be used as part of the application's console commands
 * and is registered within the console's command kernel.
 */
    Venom\SystemSettings\Console\Commands\GetSystemSettingsCommand;
use /**
 * Trait HasSystemSettingsCommand
 *
 * Provides functionality to integrate system settings into console commands.
 * This trait is intended for use in Laravel Console Commands to facilitate
 * the retrieval and processing of application or system settings necessary for
 * command execution.
 *
 * It enables commands to interact seamlessly with the system's settings layer,
 * improving reusability and modularity of the code by centralizing system settings functionality.
 *
 * Designed to standardize and simplify the management of settings within custom
 * command implementations.
 */
    Venom\SystemSettings\Console\Commands\HasSystemSettingsCommand;
use /**
 * This class defines the console command for setting system settings.
 *
 * The SetSystemSettingsCommand is utilized to update or modify specific
 * system settings via the command line interface. It interacts with the
 * backend settings storage to persist changes as needed.
 *
 * The command is registered within the application's list of console commands
 * and can be executed using the appropriate CLI interface.
 */
    Venom\SystemSettings\Console\Commands\SetSystemSettingsCommand;
use /**
 * Config is a facade for accessing configuration values in the Laravel framework.
 *
 * This class provides a static interface to retrieve, set, or manipulate
 * configuration values from the application's configuration files.
 *
 * The configuration can be accessed using dot notation to specify the desired
 * configuration file and key. It allows runtime configuration changes and supports
 * retrieving default values if a key does not exist.
 *
 * Common use cases include:
 * - Retrieving configuration values.
 * - Modifying configuration values during runtime.
 * - Checking for the existence of specific configuration keys.
 * - All: Retrieve all configuration values as an array.
 *
 * This class acts as a proxy to the Illuminate\Config\Repository instance,
 * which serves as the underlying implementation of configuration management.
 */
    Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Venom\SystemSettings\Models\SystemSettings;
use Venom\SystemSettings\Support\SettingsRegistry;

/**
 * Service provider for the SystemSettings package.
 *
 * This service provider is responsible for:
 * - Registering and merging the package's configuration file.
 * - Dynamically binding the SystemSettingsService to the application container based on the configuration.
 * - Setting up console commands associated with managing system settings, if applicable.
 * - Loading and publishing the package's database migrations and configuration for customization.
 */
class SystemSettingsServiceProvider extends ServiceProvider
{
    /**
     * Registers the package services and configuration files with the application.
     *
     * This method integrates the package's configuration file into the application's configuration
     * and dynamically registers the SystemSettingsService in the service container.
     * Additionally, it registers specific console commands if the application is running in a console environment.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the package configuration file with the application's copy.
        $this->mergeConfigFrom(__DIR__ . '/../../config/system_settings.php', 'system_settings');

        // Bind the settings registry.
        $this->app->singleton('settings.registry', function ($app) {
            return new SettingsRegistry();
        });

        // Register the SystemSettingsService dynamically from config.
        $this->app->singleton('Venom\SystemSettings\Services\SystemSettingsService', function ($app) {
            // Get the service class from the configuration.
            $serviceClass = Config::get('system_settings.service', \Venom\SystemSettings\Services\SystemSettingsService::class);

            // Get the model class from configuration, defaulting to the package's SystemSettings model.
            $model = $app['config']->get('system_settings.model', \Venom\SystemSettings\Models\SystemSettings::class);

            if(!class_exists($model)){
                throw new \Exception("Model class {$model} does not exist.");
            }

            if(!is_subclass_of($model, SystemSettings::class)){
                throw new \Exception("Model class {$model} does not extend ".SystemSettings::class.".");
            }

            if(!class_exists($serviceClass)){
                throw new \Exception("Service class {$serviceClass} does not exist.");
            }

            if(!is_subclass_of($serviceClass, \Venom\SystemSettings\Services\SystemSettingsService::class)){
                throw new \Exception("Service class {$serviceClass} does not extend ".\Venom\SystemSettings\Services\SystemSettingsService::class.".");
            }

            // Instantiate the service class with model and encrypted keys from the config.
            return new $serviceClass(new $model);
        });

        // Provide a friendly alias 'settings' to resolve the bound service.
        $this->app->singleton('settings', function ($app) {
            return $app->make('Venom\\SystemSettings\\Services\\SystemSettingsService');
        });

        // Register console commands if the application is running in the console.
        if ($this->app->runningInConsole()) {
            $this->commands([
                GetSystemSettingsCommand::class,
                SetSystemSettingsCommand::class,
                ClearSystemSettingsCommand::class,
                HasSystemSettingsCommand::class,
            ]);
        }
    }

    /**
     * Bootstraps the package by loading its migrations and preparing customization options for the user.
     *
     * @return void
     */
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

        // Attempt to register settings from modules if present.
        $this->registerSettings();
    }

    /**
     * Discover and register settings definitions from Modules/* if present.
     */
    protected function registerSettings(): void
    {
        // Only attempt when helpers like base_path() are available (Laravel app context)
        if (!function_exists('base_path')) {
            return;
        }

        $registry = $this->app->make('settings.registry');

        // Core module settings
        $coreDefinitionsPath = \base_path('Modules/Core/app/Settings/definitions.php');
        if (File::exists($coreDefinitionsPath)) {
            $definitions = include $coreDefinitionsPath;
            if (is_array($definitions)) {
                $registry->registerModule('Core', $definitions);
            }
        }

        // Other modules
        $moduleDirectories = glob(\base_path('Modules/*'), GLOB_ONLYDIR) ?: [];
        foreach ($moduleDirectories as $moduleDir) {
            $moduleName = basename($moduleDir);
            if ($moduleName === 'Core') {
                continue;
            }

            $definitionFile = $moduleDir . '/app/Settings/definitions.php';
            if (File::exists($definitionFile)) {
                $definitions = include $definitionFile;
                if (is_array($definitions)) {
                    $registry->registerModule($moduleName, $definitions);
                }
            }
        }
    }
}
