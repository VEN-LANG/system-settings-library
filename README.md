# Venom System Settings Package

[![Latest Stable Version](https://poser.pugx.org/venom/system-settings/v/stable)](https://packagist.org/packages/venom/system-settings)
[![Total Downloads](https://poser.pugx.org/venom/system-settings/downloads)](https://packagist.org/packages/venom/system-settings)
[![License](https://poser.pugx.org/venom/system-settings/license)](https://packagist.org/packages/venom/system-settings)

The Venom System Settings package allows you to manage application settings dynamically in a Laravel application. This package provides an easy way to store, retrieve, and manage key-value pairs, with support for various data types.

## Features

- **Dynamic Settings Management**: Store and retrieve application settings dynamically.
- **Caching**: Improve performance by caching settings.
- **Type Management**: Support for different data types such as strings, integers, booleans, and JSON.
- **Extendable**: Easily extend the base model to add custom logic or relationships.
- **Commands**: Manage settings through Artisan commands.

## Installation

You can install the package via Composer:

```bash
composer require venom/system-settings
```

## Publish Configuration & Migrations

After installation, publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="Venom\SystemSettings\Providers\SystemSettingsServiceProvider" --tag="config"
php artisan vendor:publish --provider="Venom\SystemSettings\Providers\SystemSettingsServiceProvider" --tag="migrations"
```

Run the migrations to create the necessary tables:

```bash
php artisan migrate
```

## Configuration

The configuration file (config/system_settings.php) includes various options:

- **cache_duration**: The duration in minutes for caching settings. 
- **allowed_types**: The allowed data types for settings. 
- **model**: The model class used for storing settings. You can extend or replace the default model. 
- **cache_key_prefix**: A prefix for the cache keys used in storing settings. 
- **default_type**: The default type used when none is provided.

## Usage

### Retrieving a Setting

You can retrieve a setting using the get method:

```php
use Venom\SystemSettings\Facades\SystemSettings;

$value = SystemSettings::get('your_key', 'default_value');
```
### Setting a Value

To set or update a setting's value:
```php
SystemSettings::set('your_key', 'your_value', 'string');
```

### Deleting a Setting

To delete a setting by its key

```php
SystemSettings::delete('your_key');
```
### Bulk Actions

Retrieve all settings or filter by type:
```php
$settings = SystemSettings::all(); // Get all settings
$jsonSettings = SystemSettings::all('json'); // Get settings of type 'json'
```

## Artisan Commands

The package provides several Artisan commands:

- **Set a Setting**: Set or update a setting using the command line.
```bash
php artisan settings:set key value [type]
```
- **Get a Setting**: Retrieve a setting value by key.
```bash
php artisan settings:get key
```
- **Check if a Setting Exists**: Check if a setting exists by key.
```bash
php artisan settings:has key
```
- **Clear Cached Settings**: Clear the cached settings.

```bash
php artisan settings:clear
```
## Extending the Package

You can extend the package by creating your own model that inherits from the base model:

```php

namespace App\Models;

use Venom\SystemSettings\Models\SystemSettings as BaseSystemSettings;

class CustomSystemSettings extends BaseSystemSettings
{
// Custom logic here
}
```
Update the configuration file to use your custom model:

```php
// config/system_settings.php
'model' => \App\Models\CustomSystemSettings::class,
```
## Contributing

Contributions are welcome! Please submit pull requests or open issues to discuss potential improvements.

## License

The Venom System Settings package is open-source software licensed under the MIT license.

## Key Sections:

1. **Introduction**: Overview of the package and its features.
2. **Installation**: Step-by-step guide to installing and setting up the package.
3. **Configuration**: Details on configuring the package according to your needs.
4. **Usage**: Examples of how to use the package to manage settings.
5. **Extending the Package**: Instructions for extending the package with custom models.
6. **Contributing**: Information on how to contribute to the project.
7. **License**: License information for the package.

This `README.md` provides clear and comprehensive documentation for users of your package.