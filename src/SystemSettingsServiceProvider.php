<?php

namespace  Venom\SystemSettings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SystemSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('system-settings-package')
            ->hasConfigFile('system_settings')
            ->hasViews()
            ->hasMigration('create_system_settings_table')
            ->hasCommands([
                SyncSettingsCommand::class,
                ClearSettingsCacheCommand::class,
            ]);
    }

    public function bootingPackage()
    {
        // Register additional services, middleware, or events here.

    }
}