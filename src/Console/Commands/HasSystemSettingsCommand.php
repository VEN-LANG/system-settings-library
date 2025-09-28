<?php

/**
 * Command to retrieve all settings based on the provided type.
 * This command interacts with the SystemSettingsService to fetch the settings and display them.
 */

namespace Venom\SystemSettings\Console\Commands;

use /**
 * Class Command
 *
 * The Command class is a base class provided by the Laravel framework,
 * specifically for defining and handling console commands. It allows
 * developers to create custom commands that can be executed from the
 * command line using the Artisan CLI.
 *
 * Key properties and methods of this class provide functionality
 * for defining the command signature, description, and handling
 * the execution logic of the command itself.
 *
 * Extend this class to implement custom Artisan commands in a Laravel application.
 *
 * Usage involves defining the `$signature` property for the command's name and
 * any options or arguments, as well as the `$description` for describing its
 * purpose. The `handle` method contains the logic that is executed when
 * the command runs.
 */
    Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Venom\SystemSettings\Models\SystemSettings;
use /**
 * SystemSettingsService class provides functionality to manage
 * system settings across the application. It allows the retrieval,
 * storage, and update of system configuration settings necessary
 * for application behavior.
 *
 * Responsibilities:
 * - Fetch settings by key or category.
 * - Save or update system settings.
 * - Validate system settings before saving them.
 * - Cache or optimize settings for performance.
 *
 * This service acts as a centralized mechanism for accessing
 * globally configurable system parameters and ensures consistent
 * settings management.
 */
    Venom\SystemSettings\Services\SystemSettingsService;

/**
 * Class HasSystemSettingsCommand
 *
 * This class represents a console command for managing system settings via the command line.
 * It allows retrieving and updating setting values based on the provided key, value, and type.
 * Extends the base Command class from the Illuminate\Console namespace.
 */
class HasSystemSettingsCommand extends Command
{
    /**
     * Command to retrieve all settings of a specified type.
     *
     * @param string $type The type of settings to retrieve.
     */
    protected $signature = 'settings:get-all {type}';
    /**
     *
     */
    protected $description = 'Get setting value by key';

    /**
     *
     */
    protected $settingsService;

    /**
     * Constructor method for initializing the class with required dependencies.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->settingsService = new (Config::get('system_settings.service', SystemSettingsService::class))(
            new (Config::get('system_settings.model', SystemSettings::class))()
        );
    }

    /**
     * Handles the updating of a setting with a specified key, value, and type.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        $type = $this->argument('type')?? 'string';

        $this->settingsService->set($key, $value, $type);
        $this->info("Setting '{$key}' of type '{$type}' has been updated to '{$value}'.");
    }
}