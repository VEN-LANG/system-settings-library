<?php

namespace Venom\SystemSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSettings extends Model
{
    protected $fillable = ['key', 'type', 'value'];
    protected $table = 'venom_system_settings';

    protected static array $encryptedKeys = [];
    protected static string $cachename;

    protected static function boot()
    {
        parent::boot();
        self::$cachename = config('system_settings.cache_key_prefix', 'system_settings');

        static::saved(fn ($model) => self::refreshCache($model));
        static::deleted(fn ($model) => Cache::forget(self::$cachename.".{$model->key}"));
    }

    public function getValueAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->encryptValue($value);
    }

    public static function getValueByKey($key, $default = null)
    {
        return Cache::remember(self::$cachename.".$key", config('system_settings.cache_duration', 60), function () use ($key, $default) {
            return optional(self::where('key', $key)->first())->value ?? $default;
        });
    }

    public static function setValueByKey($key, $value, $type = 'string')
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        self::refreshCache($setting);
        return $setting;
    }

    public static function removeByKey($key)
    {
        return optional(self::where('key', $key)->first()?->delete());
    }

    public function scopeByPartialKey($query, $partialKey)
    {
        return $query->where('key', 'like', "%$partialKey%");
    }

    protected function decryptValue($value)
    {
        return in_array($this->key, self::$encryptedKeys) ? Crypt::decryptString($value) : $this->formatValue($value, $this->type);
    }

    protected function encryptValue($value)
    {
        return in_array($this->key, self::$encryptedKeys) ? Crypt::encryptString($value) : $this->sanitizeValue($value, $this->type);
    }

    protected function formatValue($value, $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            'float' => (float) $value,
            default => (string) $value,
        };
    }

    protected function sanitizeValue($value, $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json', 'array' => json_encode($value, JSON_THROW_ON_ERROR),
            'float' => (float) $value,
            default => (string) $value,
        };
    }

    protected static function refreshCache($model)
    {
        Cache::forget(self::$cachename.".{$model->key}");
        Cache::put(self::$cachename.".{$model->key}", $model->value, config('system_settings.cache_duration', 60));
    }
}
