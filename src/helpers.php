<?php

use Venom\SystemSettings\Services\SystemSettingsService;

if (!function_exists('settings')) {
    /**
     * Get the system settings service from the container.
     *
     * @return SystemSettingsService
     */
    function settings(): SystemSettingsService
    {
        /** @var SystemSettingsService $service */
        $service = app('settings');
        return $service;
    }
}

if (!function_exists('setting')) {
    /**
     * Get a single setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null): mixed
    {
        return settings()->get($key, $default);
    }
}