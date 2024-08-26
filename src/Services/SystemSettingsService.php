<?php

namespace Venom\SystemSettings\Services;
use Illuminate\Support\Facades\Cache;
use Venom\SystemSettings\Models\SystemSettings;

class SystemSettingsService
{
    public function get($key, $default = null)
    {
        return Cache::remember("system_settings.$key", config('system_settings.cache_duration'), function () use ($key, $default) {
            return $this->keyExists($key) ? SystemSettings::where('key', $key)->value('value') : $default;
        });
    }

    public function set($key, $value)
    {
        $setting = SystemSettings::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings.$key");
        return $setting;
    }

    /**
     * Checks if key exists in the database table
     *
     * @param $key
     * @return bool
     */

    public function keyExists($key)
    {
        return (new SystemSettings())->valueExists($key);
    }
}