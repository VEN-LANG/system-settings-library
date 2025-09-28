<?php

namespace Venom\SystemSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array all()
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string $key, mixed $value, string $type = 'string')
 * @method static bool setMany(array $settings)
 * @method static bool delete(string $key)
 * @method static void clearCache()
 * @method static array getByCategory(string $category)
 *
 * @see \Venom\SystemSettings\Services\SystemSettingsService
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}

