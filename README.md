# Venom System Settings Library

This package provides a robust service and model for application settings, plus helpers, a facade, and a registry for module definitions. It’s designed to plug into multi-module apps.

## What’s included
- Service: `Venom\SystemSettings\Services\SystemSettingsService`
- Model: `Venom\SystemSettings\Models\SystemSettings`
- Facade: `Venom\SystemSettings\Facades\Settings`
- Helpers: `settings()` and `setting()`
- Registry: `Venom\SystemSettings\Support\SettingsRegistry`
- Provider auto-registered via Composer

## Integration in your module (Ignite)

1. Use the model subclass (example):
   - See `src/Stubs/Ignite/Settings/Models/GeneralSettings/Settings.php`:
     - Extends the library model.
     - Uses `config('system_settings.table_name', 'system_settings')`.

2. Use the service subclass (example):
   - See `src/Stubs/Ignite/Settings/Services/GeneralSettings/SettingsService.php`:
     - Extends the library service and only injects the model.
     - No overrides needed; you get methods: `all`, `get`, `set`, `setMany`, `delete`, `clearCache`, `getByCategory`.

3. Configure (optional):
   - In `config/system_settings.php`, you may set:
     - `'model' => Ignite\Settings\Models\GeneralSettings\Settings::class`
     - `'service' => Ignite\Settings\Services\GeneralSettings\SettingsService::class`

4. Helpers and facade:
   - `setting('organization_name')` to fetch a single value.
   - `settings()->setMany([...])` for batch updates.
   - Facade `Venom\SystemSettings\Facades\Settings` resolves `settings` binding.

5. Definitions format
   - See `src/Stubs/Modules/Settings/definitions.php` for corrected examples.
   - For file fields with a custom rule, use:
     - `'validation' => ['nullable', new ImageOrString(), 'max:2048']`

6. Registry
   - The provider scans `Modules/*/app/Settings/definitions.php` and registers them in `settings.registry`. You can query it via `app('settings.registry')`.

## Dev notes
- After updating composer.json, run `composer dump-autoload`.
- The service handles file uploads and deletes old files in `public/uploads/settings` by default.
- The model supports types: `string`, `integer`, `boolean`, `float`, `number` (as float), `array`, `json`.

