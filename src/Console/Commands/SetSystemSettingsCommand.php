<?php

/**
 * Class SetSystemSettingsCommand
 *
 * Command class for updating or setting system settings.
 * This command allows adding or modifying a system setting
 * using a specified key, value, and optional type parameter.
 *
 * Usage: settings:set {key} {value} {type?}
 * - key: The unique key for the system setting.
 * - value: The value to associate with the key.
 * - type: Optional parameter to define the data type of the value (default: 'string').
 *
 * This command uses the SystemSettingsService to perform the operation.
 */

namespace Venom\SystemSettings\Console\Commands;

use /**
 * Illuminate\Console\Command is the base class for defining and executing console commands in a Laravel application.
 *
 * Commands derived from this class are registered within the Laravel application's console kernel and can be executed
 * via the command-line interface.
 *
 * This abstract class streamlines the creation of custom CLI commands, providing predefined methods and properties
 * for specifying command names, descriptions, arguments, options, and command execution logic.
 *
 * Key features include:
 * - Definition of the command signature for specifying required inputs (arguments) and optional parameters (options).
 * - Validation and parsing of command-line input based on the defined signature.
 * - Access to robust helper methods for writing output, interacting with the user, and formatting terminal responses.
 * - Seamless integration with other Laravel application components, allowing commands to interact with the service container, models, and other facilities.
 *
 * Classes extending Illuminate\Console\Command must implement the handle() method, which contains the logic executed when
 * the command is called.
 *
 * Example use cases include building commands for scheduling tasks, running data transformations, or integrating with third-party APIs.
 */
    Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use /**
 * Class SystemSettingsService
 *
 * This service provides functionality for managing system settings.
 * It offers methods to retrieve, update, and handle persistent
 * configuration options for the application.
 *
 * The SystemSettingsService is typically used to interact with
 * application-wide settings stored in a backend storage, such as
 * a database, file, or other configuration medium.
 *
 * Responsibilities:
 * - Retrieve system setting values by key.
 * - Update or store system setting values.
 * - Manage and validate data consistency for system settings.
 *
 * This class is essential for ensuring the application operates
 * with customizable and configurable parameters that can vary
 * without changing the codebase.
 */
    Venom\SystemSettings\Services\SystemSettingsService;

/**
 * This command sets or updates system setting values by their specified key.
 *
 * The command accepts a setting key, value, and an optional type to determine the data type of the value.
 * It interacts with the SystemSettingsService to persist the changes.
 *
 * Command signature:
 * settings:set {key} {value} {type?}
 */
class SetSystemSettingsCommand extends Command
{
    /**
     * Defines the signature for a command that sets a configuration setting.
     *
     * @param string $key The key of the configuration setting to be updated.
     * @param string $value The value to assign to the configuration setting.
     * @param string|null $type Optional. Specifies the type of the configuration setting.
     */
    protected $signature = 'settings:set {key} {value} {type?}';
    /**
     * Sets or updates a system setting value associated with a specific key.
     */
    protected $description = 'Set or update a system setting value by key';

    /**
     * Service responsible for managing application settings,
     * including retrieval, creation, and updates of configuration values.
     */
    protected $settingsService;

    /**
     * Constructor for the class.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->settingsService = new (Config::get('system_settings.service', SystemSettingsService::class))();
    }

    /**
     * Handles the update of a system setting by modifying its key, value, and type.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        $type = $this->argument('type') ?? 'string';

        $this->settingsService->set($key, $value, $type);
        $this->info("System setting '{$key}' of type '{$type}' has been updated to '{$value}'.");
    }
}
