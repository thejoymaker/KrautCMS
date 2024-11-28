<?php
// System/Service/ConfigurationService.php

declare(strict_types=1);

namespace Kraut\Service;

/**
 * Class ConfigurationService
 *
 * Service responsible for loading and providing configuration values.
 * This service loads configuration from various sources and provides methods
 * to retrieve configuration values with optional default values.
 */
class ConfigurationService
{
    public const THEME = 'kraut.theme';
    public const CACHE = 'kraut.cache';
    public const DEBUG = 'kraut.debug';
    /**
     * @var array The configuration array holding all configuration values.
     */
    private array $config;

    /**
     * ConfigurationService constructor.
     *
     * Initializes the configuration service by loading the configuration values.
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Loads the configuration values.
     *
     * This method loads configuration values from various sources and stores them
     * in the $config property. Additional configuration sources can be added here.
     *
     * @return void
     */
    private function loadConfig(): void
    {
        $this->config = [
            'theme' => require __DIR__ . '/../../User/Config/Theme.php',
            // Add other configurations here
        ];
    }

    /**
     * Retrieves a configuration value by key.
     *
     * This method returns the configuration value associated with the specified key.
     * If the key does not exist, the provided default value is returned.
     *
     * @param string $key The configuration key.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The configuration value or the default value if the key does not exist.
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
?>