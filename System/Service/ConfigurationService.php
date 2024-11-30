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
    public const DEBUG = 'kraut.debug';
    public const THEME_NAME = 'kraut.theme.name';
    public const CACHE_ENABLED = 'kraut.cache.enabled';
    public const CACHE_MAX_AGE = 'kraut.cache.maxAge';
    public const LOGGING_ENABLED = 'logging.enabled';
    public const LOGGING_LEVEL = 'logging.level';
    
    /**
     * @var array The configuration array holding all configuration values.
     */
    private array $config;
    private array $theme;
    private CacheService $cacheService;

    /**
     * ConfigurationService constructor.
     *
     * Initializes the configuration service by loading the configuration values.
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        // $this->loadConfig();
        $this->config = $this->cacheService->loadCachedConfig([$this, 'loadSystemConfig'], "../../User/Config");
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

    private function loadSystemConfig(): array
    {
        return $this->loadConfig("../../User/Config/Kraut.json");
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
        $keyList = explode('.', $key);
        $tmp = $this->config;
        for($i = 0; $i < count($keyList); $i++) {
            $key = $keyList[$i];
            if (isset($tmp[$key])) {
                $tmp = $tmp[$key];
            } else {
                return $default;
            }
        }
        return $tmp;
    }
}
?>