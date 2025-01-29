<?php

/**
 * Service class that handles operations related to system settings.
 * Provides methods to retrieve, set, remove, and manage settings, both individually and in bulk.
 */

namespace Venom\SystemSettings\Services;

use /**
 * The SystemSettings class is a model representation of the system settings
 * used within the application. It is designed to manage the storage,
 * retrieval, and updating of configuration settings for the system.
 *
 * Responsibilities:
 * - Provides access to system-wide configuration settings.
 * - Handles interaction with the database or other persistent storage mechanisms
 *   for storing system settings.
 * - Ensures settings are retrieved and saved in a structured and consistent manner.
 *
 * Typical Use Case:
 * - This class would be utilized to load or update settings that are
 *   global to the application, such as API keys, feature flags, or application configurations.
 */
    Venom\SystemSettings\Models\SystemSettings;

/**
 * Service class for managing system settings.
 * This class provides an interface for interacting with system settings, allowing for retrieval, storage,
 * and deletion of settings both individually and in bulk.
 */
class SystemSettingsService
{
    /**
     *
     */
    protected $model;

    /**
     * Constructor method for initializing the class with a model.
     *
     * @param mixed $model The model to be used. Defaults to SystemSettings::class if null.
     * @return void
     */
    public function __construct($model){

        $this->model = $model ?? new (config('system_settings.model', SystemSettings::class))();
    }

    /**
     * Retrieves the value associated with the specified key from the system settings.
     *
     * @param string $key The key used to retrieve the value.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function get(string $key, $default = null)
    {
        return $this->model::getValueByKey($key, $default);
    }

    /**
     * Sets a value in the system settings for a given key and type.
     *
     * @param string $key The key identifying the setting to be set.
     * @param mixed $value The value to be set for the given key.
     * @param string $type The type of the value to be stored (default is 'string').
     */
    public function set(string $key, $value, string $type = 'string')
    {
        return $this->model::setValueByKey($key, $value, $type);
    }

    /**
     * Removes the value associated with the specified key from the system settings.
     *
     * @param string $key The key identifying the value to be removed.
     * @return bool True if the value was successfully removed, false otherwise.
     */
    public function remove(string $key)
    {
        return $this->model::removeByKey($key);
    }

    /**
     * Sets multiple settings in bulk, using the provided key-value pairs and optional types.
     *
     * @param array $settings An array of settings where each element is an associative array containing
     *                        'key' (string), 'value' (mixed), and optionally 'type' (string, defaults to 'string').
     * @return array An array of results from the set operation for each setting.
     */
    public function bulkSet(array $settings)
    {
        return array_map(fn ($s) => $this->set($s['key'], $s['value'], $s['type'] ?? 'string'), $settings);
    }

    /**
     * Retrieves the values associated with the specified keys from the system settings.
     *
     * @param array $keys An array of keys to retrieve values for.
     * @param mixed $default The default value to return for keys that do not exist.
     * @return array An array of values corresponding to the provided keys, or the default value for non-existent keys.
     */
    public function bulkGet(array $keys, $default = null)
    {
        return array_map(fn ($key) => $this->get($key, $default), $keys);
    }
}
