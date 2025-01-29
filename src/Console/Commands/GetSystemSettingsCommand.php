<?php

/**
 * Command for retrieving a system setting value by its key.
 */

namespace Venom\SystemSettings\Console\Commands;

use /**
 * This class serves as a base command class provided by Laravel's console component.
 * It allows you to define the logic for an artisan command by extending this class.
 * The command can then be registered and executed in a Laravel application.
 *
 * Key Responsibilities:
 * - Define artisan command behavior.
 * - Parse and handle input and output for the command.
 * - Provide methods to assist with user interaction and output formatting.
 *
 * Properties:
 * - `$signature`: The name and signature of the command.
 * - `$description`: A short description of what the command does.
 * - `$hidden`: Option to hide the command from being listed.
 *
 * Usage:
 * Extend this class in your application, define the `$signature` and `$description`,
 * and implement the `handle` method to specify the functionality of the command.
 */
    Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Venom\SystemSettings\Models\SystemSettings;
use /**
 * The SystemSettingsService class is responsible for managing and accessing
 * system-wide settings in a centralized manner. It provides functionalities
 * to retrieve, update, and manage key-value-based configuration settings
 * required for the application.
 *
 * This service ensures that system settings are consistently accessed and
 * updated across different parts of the application.
 */
    Venom\SystemSettings\Services\SystemSettingsService;

/**
 * This command retrieves the value of a system setting based on the provided key.
 * It uses the SystemSettingsService to fetch the setting value.
 *
 * The `settings:get` signature expects a single argument, `key`, which represents
 * the key of the setting to be retrieved.
 *
 * If the setting is found, the value will be displayed in the console. If the setting
 * is not found, an error message will be shown.
 *
 * @property string $signature The signature of the console command.
 * @property string $description A short description of what the command does.
 * @property SystemSettingsService $settingsService The service used to manage system settings.
 */
class GetSystemSettingsCommand extends Command
{
    /**
     * Console command designed to obtain the value of a specific system setting using its associated key.
     * The command expects a key as an input parameter, which corresponds to the setting to be retrieved.
     *
     * If a corresponding value for the provided key exists, it will be outputted. Otherwise, an appropriate
     * notification will be displayed indicating that the setting is undefined.
     *
     * @property string $signature Indicates the structure of the command, including required arguments.
     * @property string $description Briefly outlines the purpose of this command.
     */
    protected $signature = 'settings:get {key}';
    /**
     * A short description of the console command's purpose.
     *
     * This property holds a concise explanation of the command's functionality.
     * It is typically displayed in the list of available commands to help users
     * understand what the command does.
     *
     * @property string $description A brief description of the command.
     */
    protected $description = 'Get a setting value by key';

    /**
     * Provides functionality for managing and retrieving system settings.
     * This service is used to interact with the storage or configuration
     * system where application settings are kept.
     *
     * It allows fetching, updating, and validating various application settings
     * necessary for system operations.
     *
     * @property SystemSettingsService $settingsService The service responsible for handling system settings operations.
     */
    protected $settingsService;

    /**
     * Constructor method to initialize the class with SystemSettingsService.
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
     * Handles the retrieval of a setting by its key and outputs the value if found.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->settingsService->get($key);

        if ($value !== null) {
            // Check if the value is an array, and if so, convert it to a JSON string
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            $this->info("Value for '{$key}': {$value}");
        } else {
            $this->error("Setting with key '{$key}' not found.");
        }
    }
}