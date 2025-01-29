<?php

namespace Venom\SystemSettings\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    protected $model;
    protected $cachePrefix;
    protected $cacheDuration;

    public function __construct($model)
    {
        $this->model = $model;
        $this->cachePrefix = config('system_settings.cache_key_prefix', 'system_settings');
        $this->cacheDuration = config('system_settings.cache_duration', 60);
    }

    /**
     * Get the value of a setting by key, applying necessary type formatting.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $cacheKey = "{$this->cachePrefix}.$key";

        $setting = Cache::remember($cacheKey, $this->cacheDuration, function () use ($key) {
            return $this->model::where('key', $key)->first();
        });

        return $setting ? $this->formatValue($setting->value, $setting->type) : $default;
    }

    /**
     * Set or update a setting's value and type by key, applying necessary formatting.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function set($key, $value, $type = 'string')
    {
        $formattedValue = $this->cleanValue($value, $type);

        $setting = $this->model::updateOrCreate(
            ['key' => $key],
            ['value' => $formattedValue, 'type' => $type]
        );

        $cacheKey = "{$this->cachePrefix}.$key";
        Cache::forget($cacheKey);
        Cache::put($cacheKey, $formattedValue, $this->cacheDuration);

        return $setting;
    }

    /**
     * Format retrieved values based on type.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function formatValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
            case 'array':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Clean values before saving based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function cleanValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (string) intval($value);
            case 'float':
                return (string) floatval($value);
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
            case 'array':
                return json_encode($value);
            default:
                return trim((string) $value);
        }
    }

    /**
     * Checks if a key exists in the settings table.
     *
     * @param string $key
     * @return bool
     */
    public function hasKey($key)
    {
        return $this->model::where('key', $key)->exists();
    }

    /**
     * Checks if a type exists in the settings table.
     *
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return $this->model::where('type', $type)->exists();
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @return bool|null
     * @throws \Exception
     */
    public function delete($key)
    {
        $setting = $this->model::where('key', $key)->first();

        if ($setting) {
            Cache::forget("{$this->cachePrefix}.$key");
            return $setting->delete();
        }

        return false;
    }

    /**
     * Get all settings or filter by type.
     *
     * @param string|null $type
     * @return Collection
     */
    public function all($type = null)
    {
        $query = $this->model::query();
        if ($type) {
            $query->where('type', $type);
        }

        return $query->get()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $this->formatValue($setting->value, $setting->type),
                'type' => $setting->type,
            ];
        });
    }

    /**
     * Bulk set or update settings with value formatting.
     *
     * @param array $settings
     * @return array
     */
    public function bulkSet(array $settings)
    {
        $updatedSettings = [];

        foreach ($settings as $setting) {
            $updatedSettings[] = $this->set(
                $setting['key'],
                $setting['value'],
                $setting['type'] ?? 'string'
            );
        }

        return $updatedSettings;
    }

    /**
     * Bulk delete settings by keys.
     *
     * @param array $keys
     * @return int Number of deleted settings
     */
    public function bulkDelete(array $keys)
    {
        $deleted = $this->model::whereIn('key', $keys)->delete();

        foreach ($keys as $key) {
            Cache::forget("{$this->cachePrefix}.$key");
        }

        return $deleted;
    }

    /**
     * Bulk get settings by keys with value formatting.
     *
     * @param array $keys
     * @param mixed $default
     * @return array
     */
    public function bulkGet(array $keys, $default = null)
    {
        return collect($keys)->mapWithKeys(function ($key) use ($default) {
            return [$key => $this->get($key, $default)];
        })->toArray();
    }
}
