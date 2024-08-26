<?php

namespace Venom\SystemSettings\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSystemSettingsCommand extends Command
{
    protected $signature = 'settings:clear-cache';
    protected $description = 'Clear the settings cache';

    public function handle()
    {
        Cache::flush();
        $this->info('Settings cache cleared.');
    }
}