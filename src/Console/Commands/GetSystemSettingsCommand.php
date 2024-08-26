<?php

namespace Venom\SystemSettings\Console\Commands;

use Illuminate\Console\Command;
use Venom\SystemSettings\Services\SystemSettingsService;

class GetSystemSettingsCommand extends Command
{
    protected $signature = 'settings:get {key}';
    protected $description = 'Get a setting value by key';

    protected $settingsService;

    public function __construct(SystemSettingsService $settingsService)
    {
        parent::__construct();
        $this->settingsService = $settingsService;
    }

    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->settingsService->get($key);

        if ($value !== null) {
            $this->info("Value for '{$key}': {$value}");
        } else {
            $this->error("Setting with key '{$key}' not found.");
        }
    }
}