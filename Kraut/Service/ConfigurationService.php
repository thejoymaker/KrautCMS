<?php
// System/Service/ConfigurationService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Util\ArrayUtil;

/**
 * Class ConfigurationService
 *
 * Service responsible for loading and providing configuration values.
 * This service loads configuration from various sources and provides methods
 * to retrieve configuration values with optional default values.
 */
class ConfigurationService
{
    public const DEBUG = 'kraut.debug';
    public const THEME_NAME = 'kraut.theme.name';
    public const CACHE_ENABLED = 'kraut.cache.enabled';
    public const CACHE_MAX_AGE = 'kraut.cache.maxAge';
    public const LOGGING_ENABLED = 'log.enabled';
    public const LOGGING_LEVEL = 'log.level';
    private string $SYSTEM_CONFIG_DIR;
    /**
     * @var CacheService The cache service instance.
     */
    private CacheService $cacheService;
    /**
     * @var array The configuration array holding all configuration values.
     */
    private array $config;
    private array $theme;

    /**
     * ConfigurationService constructor.
     *
     * Initializes the configuration service by loading the configuration values.
     */
    public function __construct(CacheService $cacheService)
    {
        $this->SYSTEM_CONFIG_DIR = realpath(__DIR__ . '/../../User/Config');
        $this->cacheService = $cacheService;
        // $this->loadConfig();
        $this->config = $this->cacheService->loadCachedConfig([$this, 'loadSystemConfig'],
             $this->SYSTEM_CONFIG_DIR);
    }

    private function registerAutoloader(): void
    {
        spl_autoload_register(function (string $class): void {

            // foreach ($this->namespaceMap as $prefix => $baseDir) {
            //     if (strpos($class, $prefix) === 0) {
            //         $relativeClass = substr($class, strlen($prefix));
            //         $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            //         if (file_exists($file)) {
            //             require $file;
            //         }
            //         return;
            //     }
            // }
        });
    }

    public function loadSystemConfig(): array
    {
        $totalConfig = [];
        $allConfigFiles = glob("{$this->SYSTEM_CONFIG_DIR}/*.json");
        foreach ($allConfigFiles as $configFile) {
            $config = $this->loadConfig($configFile);
            $totalConfig = array_merge($totalConfig, $config);
        }
        return $totalConfig;
    }

    /**
     * Loads the configuration values.
     *
     * This method loads configuration values from various sources and stores them
     * in the $config property. Additional configuration sources can be added here.
     *
     * @return array
     */
    private function loadConfig(string $jsonFile): array
    {
        $config = null;
        if (file_exists($jsonFile)) {
            $config = json_decode(file_get_contents($jsonFile), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON in configuration file.');
            }
        } else {
            $config = [];
        }
        return $config;
    }

    public function installPluginConfig(string $pluginName, ?string $defaultFile): void
    {
        if (null === $defaultFile || !file_exists($defaultFile)) {
            $defaults = [];
        } else {
            $defaults = json_decode(file_get_contents($defaultFile), true);
        }
        if(!isset($defaults[strtolower($pluginName)]['active'])) {
            $defaults[strtolower($pluginName)]['active'] = true;
        }
        $pluginConfigFile = "{$this->SYSTEM_CONFIG_DIR}/{$pluginName}.json";
        // $pluginConfig = $this->loadConfig($pluginConfigFile);
        $this->config = array_merge($this->config, $defaults);
        file_put_contents($pluginConfigFile, json_encode($defaults, JSON_PRETTY_PRINT));
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
        try {
            $tmp = ArrayUtil::unpack($key, $this->config);
            return $tmp;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
?>