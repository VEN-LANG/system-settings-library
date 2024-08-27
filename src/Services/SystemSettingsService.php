<?php

namespace Venom\SystemSettings\Services;

use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Get the value of a setting by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Cache::remember($this->model::$cachename.".$key", config('system_settings.cache_duration'), function () use ($key, $default) {
            return $this->hasKey($key) ? $this->model::where('key', $key)->value('value') : $default;
        });
    }

    /**
     * Set or update a setting's value and type by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function set($key, $value, $type = 'string')
    {
        $setting = $this->model::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        Cache::forget($this->model::$cachename.".$key");
        Cache::put($this->model::$cachename.".$key", $value, config('system_settings.cache_duration'));

        return $setting;
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
            Cache::forget($this->model::$cachename.".$key");
            return $setting->delete();
        }

        return false;
    }

    /**
     * Get all settings or filter by type.
     *
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($type = null)
    {
        $query = $this->model::query();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Bulk set or update settings.
     *
     * @param array $settings
     * @return array
     */
    public function bulkSet(array $settings)
    {
        $updatedSettings = [];

        foreach ($settings as $setting) {
            $key = $setting['key'];
            $value = $setting['value'];
            $type = $setting['type'] ?? 'string';

            $updatedSettings[] = $this->set($key, $value, $type);
        }

        return $updatedSettings;
    }

    /**
     * Bulk delete settings by keys.
     *
     * @param array $keys
     * @return int
     */
    public function bulkDelete(array $keys)
    {
        $deletedCount = 0;

        foreach ($keys as $key) {
            if ($this->delete($key)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Bulk get settings by keys.
     *
     * @param array $keys
     * @param mixed $default
     * @return array
     */
    public function bulkGet(array $keys, $default = null)
    {
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = $this->get($key, $default);
        }

        return $settings;
    }
}
