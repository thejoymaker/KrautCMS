<?php
// System/Service/ConfigService.php

declare(strict_types=1);

namespace Kraut\Service;

class ConfigurationService
{
    private array $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $this->config = [
            'theme' => require __DIR__ . '/../../User/Config/Theme.php',
            // Add other configurations here
        ];
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
?>