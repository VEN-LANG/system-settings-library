<?php

namespace Venom\SystemSettings\Console\Commands;

use Illuminate\Console\Command;
use Venom\SystemSettings\Services\SystemSettingsService;

class SetSystemSettingsCommand extends Command
{
    protected $signature = 'settings:set {key} {value} {type?}';
    protected $description = 'Set or update a system setting value by key';

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
        $type = $this->argument('type') ?? 'string';

        $this->settingsService->set($key, $value, $type);
        $this->info("System setting '{$key}' of type '{$type}' has been updated to '{$value}'.");
    }
}
