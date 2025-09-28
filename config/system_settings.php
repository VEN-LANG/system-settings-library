<?php

return [

    'table_name' => env('SYSTEM_SETTINGS_TABLE_NAME','default_system_settings'),
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Specifies the cache settings for system settings. You can enable or
    | disable caching and set the duration dynamically using environment
    | variables.
    |
    */
    'cache_enabled' => env('SYSTEM_SETTINGS_CACHE_ENABLED', true),
    'cache_duration' => env('SYSTEM_SETTINGS_CACHE_DURATION', 60), // in minutes
    'cache_duration_seconds' => env('SYSTEM_SETTINGS_CACHE_DURATION_SECONDS', 3600), // in seconds
    'cache_key_prefix' => env('SYSTEM_SETTINGS_CACHE_KEY_PREFIX', 'system_settings'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Data Types
    |--------------------------------------------------------------------------
    |
    | Defines the valid data types for system settings. This prevents invalid
    | data from being stored and ensures consistency in stored values.
    |
    */
    'allowed_types' => ['string', 'integer', 'boolean', 'json', 'array', 'float'],

    /*
    |--------------------------------------------------------------------------
    | System Settings Model
    |--------------------------------------------------------------------------
    |
    | Defines the model that represents system settings. You can replace this
    | with a custom model to extend functionality.
    |
    */
    'model' => env('SYSTEM_SETTINGS_MODEL', \Venom\SystemSettings\Models\SystemSettings::class),
    'service' => env('SYSTEM_SETTINGS_SERVICE', \Venom\SystemSettings\Services\SystemSettingsService::class),
    /*
    |--------------------------------------------------------------------------
    | Default Type
    |--------------------------------------------------------------------------
    |
    | Specifies the default type to be used when a type is not provided.
    | Ensures consistency and minimizes potential errors.
    |
    */
    'default_type' => env('SYSTEM_SETTINGS_DEFAULT_TYPE', 'string'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    |
    | Enables encryption for sensitive system settings. This ensures
    | security when storing sensitive data like API keys or passwords.
    |
    */
    'enable_encryption' => env('SYSTEM_SETTINGS_ENCRYPTION_ENABLED', true),
    'encrypted_keys' => explode(',', env('SYSTEM_SETTINGS_ENCRYPTED_KEYS', 'api_key,smtp_password,db_password')),

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Defines validation rules for system settings to ensure correct data
    | formats before storage.
    |
    */
    'validation_rules' => [
        'integer' => 'numeric',
        'string' => 'string|max:255',
        'boolean' => 'boolean',
        'json' => 'json',
        'array' => 'array',
        'float' => 'numeric',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enables logging of system setting changes for auditing purposes.
    |
    */
    'enable_audit_logging' => env('SYSTEM_SETTINGS_AUDIT_LOGGING', true),
];
