<?php

/**
 * Service class that handles operations related to system settings.
 * Provides methods to retrieve, set, remove, and manage settings, both individually and in bulk.
 */

namespace Venom\SystemSettings\Services;

use Venom\SystemSettings\Models\SystemSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

/**
 * Service class for managing system settings.
 * This class provides an interface for interacting with system settings, allowing for retrieval, storage,
 * and deletion of settings both individually and in bulk.
 */
class SystemSettingsService
{
    /**
     * The underlying Eloquent model instance/class used by the service.
     */
    protected $model;

    /**
     * Cache key for storing the whole settings map.
     */
    protected const SETTINGS_CACHE_KEY = 'system_settings';

    /**
     * Cache TTL in seconds (defaults to 1 hour if not configured).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Constructor method for initializing the class with a model.
     *
     * @param mixed $model The model to be used. Defaults to SystemSettings model from config.
     * @return void
     */
    public function __construct($model){
        $this->model = $model ?? new (\config('system_settings.model', SystemSettings::class))();
    }

    /**
     * Retrieves the value associated with the specified key from the system settings.
     *
     * @param string $key The key used to retrieve the value.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value associated with the key, or the default value if the key does not exist.
     */
    public function get(string $key, $default = null): mixed
    {
        return $this->model::getValueByKey($key, $default);
    }

    /**
     * Sets a value in the system settings for a given key and type.
     * Automatically clears the aggregate cache after update.
     *
     * @param string $key The key identifying the setting to be set.
     * @param mixed $value The value to be set for the given key.
     * @param string $type The type of the value to be stored (default is 'string').
     */
    public function set(string $key, $value, string $type = 'string')
    {
        // If file type, handle upload/storage, allowing string paths to pass-through
        if ($type === 'file' && $value !== null) {
            $value = $this->handleFileUpload($key, $value);
        }

        // Use upsertWithMeta to ensure type is applied before value mutator runs
        $this->upsertWithMeta($key, $value, $type);

        // Clear aggregate cache map
        $this->clearCache();

        return true;
    }

    /**
     * Removes the value associated with the specified key from the system settings.
     *
     * @param string $key The key identifying the value to be removed.
     * @return bool True if the value was successfully removed, false otherwise.
     */
    public function remove(string $key): bool
    {
        $deleted = (bool) $this->model::removeByKey($key);
        if ($deleted) {
            $this->clearCache();
        }
        return $deleted;
    }

    /**
     * Sets multiple settings in bulk, using the provided key-value pairs and optional types.
     * Accepts two shapes:
     *  - [ ['key' => 'k', 'value' => v, 'type' => 'string'], ... ]
     *  - [ 'k' => [ 'value' => v, 'type' => 'string', 'category' => '...', 'subcategory' => '...' ], ... ]
     *
     * @param array $settings
     * @return array|bool Returns array of results when list-of-arrays given; true when associative map given.
     */
    public function bulkSet(array $settings)
    {
        // If associative map: key => [value, type, category, subcategory]
        $isAssoc = array_keys($settings) !== range(0, count($settings) - 1);
        if ($isAssoc) {
            foreach ($settings as $key => $data) {
                $value = $data['value'] ?? null;
                $type = $data['type'] ?? 'string';
                $category = $data['category'] ?? null;
                $subcategory = $data['subcategory'] ?? null;
                $this->upsertWithMeta($key, $value, $type, $category, $subcategory);
            }
            $this->clearCache();
            return true;
        }

        // Fallback to legacy shape processing
        return array_map(fn ($s) => $this->set($s['key'], $s['value'], $s['type'] ?? 'string'), $settings);
    }

    /**
     * Alias maintained for backward compatibility.
     */
    public function bulkRemoveByKey(array $keys){
        return $this->bulkRemove($keys);
    }

    /**
     * Retrieves all records using the model's static getAll method.
     * Note: this returns Eloquent collection of model instances.
     *
     * @return mixed All records fetched by the model.
     */
    public function getAll(): mixed
    {
        return $this->model::getAllAttribute();
    }

    /**
     * Convenience: get all settings as a key => value array with type-casting applied.
     */
    public function all(): array
    {
        $cacheEnabled = (bool) \config('system_settings.cache_enabled', true);
        $cacheKey = \config('system_settings.cache_key_prefix', 'system_settings') . '.map';
        $ttl = $this->cacheTtlSeconds();

        $fetch = function () {
            $table = \config('system_settings.table_name', 'system_settings');
            return DB::table($table)
                ->get()
                ->keyBy('key')
                ->map(function ($item) {
                    return $this->castValue($item->value, $item->type);
                })
                ->toArray();
        };

        if (!$cacheEnabled) {
            return $fetch();
        }

        return Cache::remember($cacheKey, $ttl, $fetch);
    }

    /**
     * Bulk get convenience.
     *
     * @param array $keys
     * @param mixed $default
     */
    public function bulkGet(array $keys, $default = null)
    {
        return array_map(fn ($key) => $this->get($key, $default), $keys);
    }

    /**
     * Removes multiple items based on the provided keys.
     *
     * @param array $keys
     * @return array
     */
    public function bulkRemove(array $keys){
        $results = array_map(fn ($key) => $this->remove($key), $keys);
        $this->clearCache();
        return $results;
    }

    /**
     * Set multiple settings using an associative map. Wrapper matching module API name.
     */
    public function setMany(array $settings): bool
    {
        // Delegate to bulkSet which already handles both shapes and clears cache.
        $result = $this->bulkSet($settings);
        return $result === true || $result !== false;
    }

    /**
     * Delete a setting by key. Wrapper matching module API name.
     */
    public function delete(string $key): bool
    {
        return $this->remove($key);
    }

    /**
     * Clear the aggregate settings cache map.
     */
    public function clearCache(): void
    {
        $mapKey = (\config('system_settings.cache_key_prefix', 'system_settings')) . '.map';
        Cache::forget($mapKey);
        // Also clear the model-level aggregated cache if used
        Cache::forget((\config('system_settings.cache_key_prefix', 'system_settings')).'.all');
    }

    /**
     * Get settings by category (key => casted value array).
     */
    public function getByCategory(string $category): array
    {
        $table = \config('system_settings.table_name', 'system_settings');
        return DB::table($table)
            ->where('category', $category)
            ->get()
            ->keyBy('key')
            ->map(function ($item) {
                return $this->castValue($item->value, $item->type);
            })
            ->toArray();
    }

    /**
     * Sets the model property.
     *
     * @param mixed $model The model value to be set.
     * @return void
     */
    public function setModel(mixed $model): void
    {
        $this->model = $model;
    }

    /**
     * Retrieves the current model instance.
     *
     * @return mixed The model instance.
     */
    public function getModel(): mixed
    {
        return $this->model;
    }

    // -------------------------
    // Internal helpers
    // -------------------------

    /**
     * Upsert with category/subcategory support and proper value handling.
     */
    protected function upsertWithMeta(string $key, $value, string $type = 'string', ?string $category = null, ?string $subcategory = null): void
    {
        if ($type === 'file' && $value !== null) {
            $value = $this->handleFileUpload($key, $value);
        }

        // Use Eloquent to ensure mutators (encrypt/sanitize) are applied in correct order
        $modelClass = get_class($this->model);
        /** @var \Illuminate\Database\Eloquent\Model $setting */
        $setting = $modelClass::firstOrNew(['key' => $key]);
        $setting->type = $type;
        if ($category !== null) {
            $setting->category = $category;
        }
        if ($subcategory !== null) {
            $setting->subcategory = $subcategory;
        }
        $setting->value = $value; // triggers mutator using $type
        $setting->save();
    }

    /**
     * Handle file upload; allow strings to pass through unchanged; delete replaced files if present.
     */
    protected function handleFileUpload(string $key, $file): string
    {
        // If file is already a path string, return as-is
        if (is_string($file)) {
            return $file;
        }

        if (!($file instanceof UploadedFile)) {
            // Fallback: cast to string
            return (string) $file;
        }

        $path = \config('system_settings.uploads_path', 'uploads/settings');

        // Get old file if it exists
        $table = \config('system_settings.table_name', 'system_settings');
        $oldFile = DB::table($table)->where('key', $key)->value('value');

        // Delete old file if it exists
        if ($oldFile && File::exists(\public_path($oldFile))) {
            File::delete(\public_path($oldFile));
        }

        // Ensure directory exists
        if (!File::exists(\public_path($path))) {
            File::makeDirectory(\public_path($path), 0755, true);
        }

        // Store new file
        $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(\public_path($path), $filename);

        return '/' . trim($path, '/') . '/' . $filename;
    }

    /**
     * Prepare value for storage when bypassing model mutators.
     */
    protected function prepareForStorage($value, string $type): string
    {
        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }
        if ($type === 'array' || $type === 'json') {
            return json_encode($value);
        }
        return (string) $value;
    }

    /**
     * Cast raw DB value to appropriate PHP type.
     */
    protected function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return (float) $value;
            case 'integer':
                return (int) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Return cache TTL in seconds, preferring seconds config if provided.
     */
    protected function cacheTtlSeconds(): int
    {
        $seconds = \config('system_settings.cache_duration_seconds');
        if ($seconds !== null) {
            return (int) $seconds;
        }
        // Fallback: minutes to seconds
        return (int) (\config('system_settings.cache_duration', 60) * 60);
    }
}
