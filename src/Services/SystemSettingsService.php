<?php

namespace Venom\SystemSettings\Services;

use Venom\SystemSettings\Models\SystemSettings;

class SystemSettingsService
{
    public function get(string $key, $default = null)
    {
        return SystemSettings::getValueByKey($key, $default);
    }

    public function set(string $key, $value, string $type = 'string')
    {
        return SystemSettings::setValueByKey($key, $value, $type);
    }

    public function remove(string $key)
    {
        return SystemSettings::removeByKey($key);
    }

    public function bulkSet(array $settings)
    {
        return array_map(fn ($s) => $this->set($s['key'], $s['value'], $s['type'] ?? 'string'), $settings);
    }

    public function bulkGet(array $keys, $default = null)
    {
        return array_map(fn ($key) => $this->get($key, $default), $keys);
    }
}
