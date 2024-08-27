<?php

namespace Venom\SystemSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Model
{
    protected $fillable = ['key', 'type', 'value'];

    public static string $cachename = 'system_settings';

    protected $table = 'venom_system_settings';

    // Accessor for converting JSON strings to arrays
    public function getValueAttribute($value)
    {
        return $this->type === 'json' ? json_decode($value, true) : $value;
    }

    // Mutator for converting arrays to JSON strings
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->type === 'json' ? json_encode($value) : $value;
    }

    // Check if a specific key exists
    public function hasKey($key)
    {
        return self::where('key', $key)->exists();
    }

    // Check if a specific type exists
    public function hasType($type)
    {
        return self::where('type', $type)->exists();
    }

    // Retrieve settings by type
    public function withType($type)
    {
        return self::where('type', $type)->get();
    }

    // Retrieve setting value by key
    public static function getValueByKey($key)
    {
        $setting = Cache::remember(self::$cachename.".$key", 60, function() use ($key) {
            return self::where('key', $key)->first();
        });

        return $setting ? $setting->value : null;
    }

    // Update setting by key
    public static function setValueByKey($key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::$cachename.".$key");

        return $setting;
    }

    // Remove setting by key
    public static function removeByKey($key)
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            Cache::forget(self::$cachename.".$key");
            return $setting->delete();
        }

        return false;
    }

    // Scope to retrieve settings by a partial key match
    public function scopeByPartialKey($query, $partialKey)
    {
        return $query->where('key', 'like', "%$partialKey%");
    }

    // Refresh cache after saving
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
