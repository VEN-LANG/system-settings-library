<?php

namespace Venom\SystemSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Model
{
    protected $fillable = ['key', 'type', 'value'];
    public static string $cachename;

    public static $cacheDuration;
    public static function initialize()
    {
        self::$cachename = config('system_settings.cache_key_prefix', 'system_settings');
        self::$cacheDuration = config('system_settings.cache_duration', 60);
    }
    protected $table = 'venom_system_settings';

    /**
     * Accessor to format the retrieved value based on type.
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Mutator to clean and format the value before saving.
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'integer' => (string) intval($value),
            'float' => (string) floatval($value),
            'boolean' => $value ? 'true' : 'false',
            'json', 'array' => json_encode($value),
            default => trim((string) $value),
        };
    }

    /**
     * Check if a specific key exists.
     */
    public function hasKey($key)
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Check if a specific type exists.
     */
    public function hasType($type)
    {
        return self::where('type', $type)->exists();
    }

    /**
     * Retrieve settings by type.
     */
    public function withType($type)
    {
        return self::where('type', $type)->get();
    }

    /**
     * Retrieve setting value by key with caching.
     */
    public static function getValueByKey($key)
    {
        return Cache::remember(self::$cachename.".$key", self::$cacheDuration, function() use ($key) {
            return self::where('key', $key)->first()?->value;
        });
    }

    /**
     * Update or create setting by key and clear cache.
     */
    public static function setValueByKey($key, $value, $type = 'string')
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        Cache::forget(self::$cachename.".$key");
        return $setting;
    }

    /**
     * Remove setting by key and clear cache.
     */
    public static function removeByKey($key)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            Cache::forget(self::$cachename.".$key");
            return $setting->delete();
        }
        return false;
    }

    /**
     * Scope to retrieve settings by a partial key match.
     */
    public function scopeByPartialKey($query, $partialKey)
    {
        return $query->where('key', 'like', "%$partialKey%");
    }

    /**
     * Auto-clear cache after save or delete.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget(self::$cachename.".{$model->key}");
        });

        static::deleted(function ($model) {
            Cache::forget(self::$cachename.".{$model->key}");
        });
    }
}
