<?php

namespace Venom\SystemSettings\Services;

use Illuminate\Support\Facades\Cache;
use Venom\SystemSettings\Models\SystemSettings;

class SystemSettingsService
{
    /**
     * Get the value of a setting by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Cache::remember(SystemSettings::$cachename.".$key", config('system_settings.cache_duration'), function () use ($key, $default) {
            return $this->hasKey($key) ? SystemSettings::where('key', $key)->value('value') : $default;
        });
    }

    /**
     * Set or update a setting's value and type by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return \Venom\SystemSettings\Models\SystemSettings
     */
    public function set($key, $value, $type)
    {
        $setting = SystemSettings::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        Cache::forget(SystemSettings::$cachename.".$key");
        Cache::put(SystemSettings::$cachename.".$key", $value, config('system_settings.cache_duration'));

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
        return SystemSettings::where('key', $key)->exists();
    }

    /**
     * Checks if a type exists in the settings table.
     *
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        return SystemSettings::where('type', $type)->exists();
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
        $setting = SystemSettings::where('key', $key)->first();

        if ($setting) {
            Cache::forget(SystemSettings::$cachename.".$key");
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
        $query = SystemSettings::query();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }
}
