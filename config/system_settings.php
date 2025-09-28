<?php

return [
    'table_name' => env('SYSTEM_SETTINGS_TABLE_NAME', 'system_settings'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | You can configure cache TTL using seconds or minutes. If
    | SYSTEM_SETTINGS_CACHE_DURATION_SECONDS is set, it takes precedence.
    | Otherwise, SYSTEM_SETTINGS_CACHE_DURATION is treated as minutes.
    |
    */
    'cache_enabled' => env('SYSTEM_SETTINGS_CACHE_ENABLED', true),
    'cache_duration' => env('SYSTEM_SETTINGS_CACHE_DURATION', 60), // minutes (fallback)
    'cache_duration_seconds' => env('SYSTEM_SETTINGS_CACHE_DURATION_SECONDS', 3600), // seconds (preferred)
    'cache_key_prefix' => env('SYSTEM_SETTINGS_CACHE_KEY_PREFIX', 'system_settings'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Data Types
    |--------------------------------------------------------------------------
    */
    'allowed_types' => ['string', 'integer', 'boolean', 'json', 'array', 'float', 'number'],

    /*
    |--------------------------------------------------------------------------
    | System Settings Model/Service
    |--------------------------------------------------------------------------
    */
    'model' => env('SYSTEM_SETTINGS_MODEL', \Venom\SystemSettings\Models\SystemSettings::class),
    'service' => env('SYSTEM_SETTINGS_SERVICE', \Venom\SystemSettings\Services\SystemSettingsService::class),

    /*
    |--------------------------------------------------------------------------
    | Default Type
    |--------------------------------------------------------------------------
    */
    'default_type' => env('SYSTEM_SETTINGS_DEFAULT_TYPE', 'string'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    */
    'enable_encryption' => env('SYSTEM_SETTINGS_ENCRYPTION_ENABLED', true),
    'encrypted_keys' => explode(',', env('SYSTEM_SETTINGS_ENCRYPTED_KEYS', 'api_key,smtp_password,db_password')),

    /*
    |--------------------------------------------------------------------------
    | Validation Rules (optional defaults)
    |--------------------------------------------------------------------------
    */
    'validation_rules' => [
        'integer' => 'numeric',
        'string' => 'string|max:255',
        'boolean' => 'boolean',
        'json' => 'json',
        'array' => 'array',
        'float' => 'numeric',
        'number' => 'numeric',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging (placeholder toggle)
    |--------------------------------------------------------------------------
    */
    'enable_audit_logging' => env('SYSTEM_SETTINGS_AUDIT_LOGGING', true),

    /*
    |--------------------------------------------------------------------------
    | Module Definitions Discovery
    |--------------------------------------------------------------------------
    | Where to look for modules and what file to include per-module for
    | settings definitions. Paths accept glob patterns. Defaults scan
    | base_path('Modules/*').
    */
    'include_core' => env('SYSTEM_SETTINGS_INCLUDE_CORE', true),
    'core_module_name' => env('SYSTEM_SETTINGS_CORE_MODULE_NAME', 'Core'),
    'module_scan_paths' => [
        // e.g. '/var/www/app/Modules/*'
        // base_path() will be applied dynamically; keep relative here for clarity
        'Modules/*',
    ],
    'definitions_relative_path' => env('SYSTEM_SETTINGS_DEFINITIONS_RELATIVE', 'app/Settings/definitions.php'),

    /*
    |--------------------------------------------------------------------------
    | Uploads Path
    |--------------------------------------------------------------------------
    */
    'uploads_path' => env('SYSTEM_SETTINGS_UPLOADS_PATH', 'uploads/settings'),
];
