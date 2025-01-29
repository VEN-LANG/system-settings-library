<?php

/**
 * Class ClearSystemSettingsCommand
 *
 * This console command is responsible for clearing the system settings cache.
 * When executed, it flushes the cache and outputs a confirmation message
 * that the settings cache has been cleared.
 *
 * Command signature: settings:clear-cache
 */

namespace Venom\SystemSettings\Console\Commands;

use /**
 * Illuminate\Console\Command is an abstract base class utilized for defining
 * console commands within Laravel applications.
 *
 * It provides functionality for:
 * - Defining the signature of the command (its name and arguments/options).
 * - Configuring the behavior of the command.
 * - Executing the desired logic when the command is invoked.
 * - Providing structured output to the console.
 *
 * Key functionality includes:
 * - The `signature` or `name` property for identifying the command.
 * - The `description` property for describing the command's purpose.
 * - Integration with Laravel's service container and dependency injection.
 * - Rich output functionality using helper methods such as `info`, `error`,
 *   `warn`, `table`, and `line`.
 * - Handling command arguments and options dynamically through the use of
 *   signature definitions.
 *
 * To implement a custom command, you must extend this class and define the
 * `handle` method, which contains the logic to be executed when the command
 * is run.
 *
 * This class also provides integration with scheduled tasks and event
 * handling within the Laravel framework.
 */
    Illuminate\Console\Command;
use /**
 * This class serves as a facade for the Cache component in the Laravel framework.
 * It provides a static interface to the caching system, allowing developers to interact
 * with the cache without needing to retrieve an instance of the cache manager directly.
 *
 * The Cache facade supports a variety of caching stores such as file, database,
 * Redis, Memcached, and more, depending on the application's configuration.
 *
 * Common uses include storing, retrieving, and deleting cached data, as well as
 * checking for the existence of cached items.
 *
 * Key functionalities include:
 * - Retrieving cached values.
 * - Storing data into the cache for a specified expiration time.
 * - Removing items from the cache.
 * - Checking if a cache entry exists.
 * - Providing atomic operations and cache locking mechanisms.
 */
    Illuminate\Support\Facades\Cache;

/**
 * ClearSystemSettingsCommand is a console command to clear the cache
 * of system settings.
 *
 * This command interacts with the application's caching system to
 * completely flush all cached settings and outputs a confirmation message.
 */
class ClearSystemSettingsCommand extends Command
{
    /**
     * Command signature for clearing the settings cache.
     */
    protected $signature = 'settings:clear-cache';
    /**
     * Description of the command that clears the settings cache.
     */
    protected $description = 'Clear the settings cache';

    /**
     * Handles the process of clearing the application settings cache.
     *
     * @return void
     */
    public function handle()
    {
        Cache::flush();
        $this->info('Settings cache cleared.');
    }
}