<?php

namespace Venom\SystemSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSettings extends Model
{
    /**
     *
     */
    protected $table;
    /**
     * An array that defines the attributes that are mass assignable.
     * This is used to specify which attributes can be set using mass-assignment techniques.
     */
    protected $fillable = ['key', 'type', 'value'];
    /**
     * Stores encrypted keys for sensitive data or encryption processes.
     */
    protected static array $encryptedKeys;
    /**
     * The name used for caching purposes.
     */
    protected static string $cachename;

    /**
     * Constructor method to initialize the model with the given attributes
     * and configure the table name from the system settings configuration.
     *
     * @param array $attributes An array of attributes to initialize the model with.
     *
     * @return void
     */
    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = config('system_settings.table_name', 'system_settings');
    }

    /**
     * Boots the model and initializes necessary properties and event listeners.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        self::$cachename = config('system_settings.cache_key_prefix', 'system_settings');
        self::$encryptedKeys = config('system_settings.encrypted_keys', []);

        static::saved(fn ($model) => self::refreshCache($model));
        static::deleted(fn ($model) => Cache::forget(self::$cachename.".{$model->key}"));
    }

    /**
     * Retrieves the decrypted value of the given attribute.
     *
     * @param mixed $value The original encrypted value of the attribute.
     *
     * @return mixed The decrypted value of the attribute.
     */
    public function getValueAttribute($value)
    {
        return $this->decryptValue($value);
    }

    /**
     * Sets the value attribute after encrypting the given value.
     *
     * @param mixed $value The value to be encrypted and set.
     * @return void
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->encryptValue($value);
    }

    /**
     * Retrieves a value associated with the given key from the cache or database.
     *
     * @param string $key The key to look for in the settings.
     * @param mixed $default The default value to return if the key is not found. Defaults to null.
     *
     * @return mixed The value associated with the given key or the default value if the key is not found.
     */
    public static function getValueByKey($key, $default = null)
    {
        return Cache::remember(self::$cachename.".$key", config('system_settings.cache_duration', 60), function () use ($key, $default) {
            return optional(self::where('key', $key)->first())->value ?? $default;
        });
    }

    /**
     * Updates or creates a setting with the specified key, value, and type, and refreshes the cache.
     *
     * @param string $key The unique key identifying the setting.
     * @param mixed $value The value to be associated with the specified key.
     * @param string $type Optional. The data type of the value. Defaults to 'string'.
     * @return mixed The created or updated setting instance.
     */
    public static function setValueByKey($key, $value, $type = 'string')
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        self::refreshCache($setting);
        return $setting;
    }

    /**
     *
     * @param string $key The key used to identify the record to be removed.
     */
    public static function removeByKey($key)
    {
        return optional(self::where('key', $key)->first()?->delete());
    }

    /**
     * Filters the query by matching a partial key.
     *
     * @param \Illuminate\Database\Query\Builder $query The query builder instance.
     * @param string $partialKey The partial key to match against the 'key' column.
     */
    public function scopeByPartialKey($query, $partialKey)
    {
        return $query->where('key', 'like', "%$partialKey%");
    }

    /**
     * Decrypts the given value based on the key and encryption status.
     *
     * @param mixed $value The value to be decrypted or formatted depending on the encryption status.
     * @return mixed The decrypted or formatted value.
     */
    protected function decryptValue($value)
    {
        return in_array($this->key, self::$encryptedKeys) ? Crypt::decryptString($value) : $this->formatValue($value, $this->type);
    }

    /**
     * Encrypts the given value if the associated key is marked as encrypted.
     * Otherwise, sanitizes the value based on its type.
     *
     * @param mixed $value The value to be encrypted or sanitized.
     * @return mixed The encrypted value or the sanitized value.
     */
    protected function encryptValue($value)
    {
        return in_array($this->key, self::$encryptedKeys) ? Crypt::encryptString($value) : $this->sanitizeValue($value, $this->type);
    }

    /**
     * Formats a given value based on the specified type.
     *
     * @param mixed $value The value to be formatted.
     * @param string $type The type to format the value as. Supported types: 'integer', 'boolean', 'json', 'array', 'float', or default to 'string'.
     * @return mixed The formatted value based on the given type.
     */
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

    /**
     * Sanitizes the given value based on the specified type.
     *
     * @param mixed $value The value to be sanitized.
     * @param string $type The type to sanitize the value as. Supported types are 'integer', 'boolean', 'json', 'array', 'float', or defaults to 'string'.
     * @return mixed The sanitized value cast to the specified type.
     */
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

    /**
     * Refreshes the cache for the given model by removing the existing cache entry and creating a new one.
     *
     * @param mixed $model The model instance containing the key and value used for cache operations.
     * @return void
     */
    protected static function refreshCache($model)
    {
        Cache::forget(self::$cachename.".{$model->key}");
        Cache::put(self::$cachename.".{$model->key}", $model->value, config('system_settings.cache_duration', 60));
    }


    /**
     * Retrieves all system settings from the database or cache.
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of all system settings.
     */
    public static function getAllAttribute()
    {
        return Cache::remember(self::$cachename . '.all', config('system_settings.cache_duration', 60), fn() => self::all());
    }
}
