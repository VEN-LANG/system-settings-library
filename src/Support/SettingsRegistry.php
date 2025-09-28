<?php

namespace Venom\SystemSettings\Support;

class SettingsRegistry
{
    /**
     * Registered settings grouped by module name.
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected array $settings = [];

    /**
     * Register settings for a module.
     *
     * @param string $module
     * @param array<int, array<string, mixed>> $definitions
     */
    public function registerModule(string $module, array $definitions): void
    {
        $this->settings[$module] = $definitions;
    }

    /**
     * Get all registered settings as key => definition map.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $result = [];
        foreach ($this->settings as $definitions) {
            foreach ($definitions as $definition) {
                if (!isset($definition['key'])) {
                    continue;
                }
                $result[$definition['key']] = $definition;
            }
        }
        return $result;
    }

    /**
     * Get settings for a specific module.
     *
     * @param string $module
     * @return array<int, array<string, mixed>>
     */
    public function getModuleSettings(string $module): array
    {
        return $this->settings[$module] ?? [];
    }

    /**
     * Get list of modules that have registered settings.
     *
     * @return array<int, string>
     */
    public function getModules(): array
    {
        return array_keys($this->settings);
    }
}

