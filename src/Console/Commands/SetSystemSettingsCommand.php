<?php

namespace Venom\SystemSettings\Console\Commands;

use Illuminate\Console\Command;
use Venom\SystemSettings\Services\SystemSettingsService;

class SetSystemSettingsCommand extends Command
{
    protected $signature = 'settings:set {key} {value}';
    protected $description = 'Set or update a setting value by key';

    protected $settingsService;

    public function __construct(SystemSettingsService $settingsService)
    {
        parent::__construct();
        $this->settingsService = $settingsService;
    }

    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');

        $this->settingsService->set($key, $value);
        $this->info("Setting '{$key}' has been updated to '{$value}'.");
    }
}