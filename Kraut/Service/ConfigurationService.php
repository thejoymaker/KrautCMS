<?php
// System/Service/ConfigurationService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Util\ArrayUtil;

use function DI\get;

/**
 * Class ConfigurationService
 *
 * Service responsible for loading and providing configuration values.
 * This service loads configuration from various sources and provides methods
 * to retrieve configuration values with optional default values.
 */
class ConfigurationService
{
    // public const DEBUG = 'kraut.debug';
    public const THEME_NAME = 'kraut.theme.name';
    // public const CACHE_ENABLED = 'kraut.cache.enabled';
    // public const CACHE_MAX_AGE = 'kraut.cache.maxAge';
    public const LOGGING_ENABLED = 'kraut.log.enabled';
    public const LOGGING_LEVEL = 'kraut.log.level';
    public const PAGE_NAME = 'kraut.page.name';
    public const PAGE_DESCRIPTION = 'kraut.page.description';
    public const PAGE_AUTHOR = 'kraut.page.author';
    public const PAGE_TIMEZONE = 'kraut.page.timezone';
    public const PAGE_LANGUAGE = 'kraut.page.language';
    public const PAGE_ADMIN_MAIL = 'kraut.page.admin-mail';
    private array $keySetCore = [
        self::PAGE_NAME,
        self::PAGE_DESCRIPTION,
        self::PAGE_AUTHOR,
        self::PAGE_TIMEZONE,
        self::PAGE_LANGUAGE,
        self::PAGE_ADMIN_MAIL,
        self::LOGGING_ENABLED,
        self::LOGGING_LEVEL
    ];
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

    public function listSettings(): array
    {
        $settings = [];
        foreach ($this->keySetCore as $key) {
            $settings[$key] = $this->get($key, '');
        }
        return $settings;
    }

    public function saveSettings(array $settings): void
    {
        $mutated = false;
        foreach ($this->keySetCore as $key) {
            $current = $this->get($key, '');
            $new = $settings[$key] ?? '';
            if ($current !== $new) {
                $this->set($key, $new);
                if (!$mutated) {
                    $mutated = true;
                }
            }
        }

        if ($mutated) {
            $this->persistConfig("{$this->SYSTEM_CONFIG_DIR}/Kraut.json", 'kraut');
        }
    }

    public function persistConfig(string $file, string $topLevel): void
    {
        $config = [];
        foreach ($this->config as $key => $value) {
            if (strpos($key, $topLevel) === 0) {
                $config[$key] = $value;
                break;
            }
        }
        file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT));
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

    /**
     * Retrieves a list of dot notated keys for a given namespace.
     *
     * This method retrieves a list of dot notated keys for a given namespace.
     * The namespace is used to filter the configuration values.
     *
     * @param string $nameSpace The namespace to filter the configuration values.
     * @return array A list of dot notated keys.
     */
    private function getDotNotatedKeySet(string $nameSpace): array
    {
        $dotNotatedKeys = [];
        ArrayUtil::parseKeys($this->config[$nameSpace] ?? [], $nameSpace, $dotNotatedKeys);
        return $dotNotatedKeys;
    }

    /**
     * Retrieves the configuration values for a given plugin.
     *
     * This method retrieves the configuration values for a given plugin.
     * The plugin name is used to filter the configuration values.
     *
     * @param string $pluginName The name of the plugin to retrieve the configuration values for.
     * @return array The configuration values for the plugin.
     */
    public function getPluginConfig(string $pluginName): array
    {
        $nameSpace = strtolower($pluginName);
        $pluginConfigKeys = $this->getDotNotatedKeySet($nameSpace);
        $data = [];
        foreach ($pluginConfigKeys as $key){
            if("{$nameSpace}.active" === $key) {
                continue;
            }
            $data[$key] = $this->get($key, '');
        }
        return $data;
    }
    
    /**
     * Saves the configuration values for a given plugin.
     *
     * This method saves the configuration values for a given plugin.
     * The plugin name is used to filter the configuration values.
     *
     * @param string $plugin The name of the plugin to save the configuration values for.
     * @param array $config The configuration values to save.
     */
    public function savePluginConfig(string $plugin, array $config): void
    {
        $nameSpace = strtolower($plugin);
        $pluginConfigKeys = $this->getDotNotatedKeySet($nameSpace);
        $mutated = false;
        foreach ($pluginConfigKeys as $key) {
            if(!isset($config[$key])) {
                continue;
            }
            $current = $this->get($key, '');
            $new = $config[$key] ?? '';
            if ($current !== $new) {
                $this->set($key, $new);
                if (!$mutated) {
                    $mutated = true;
                }
            }
        }

        if ($mutated) {
            $this->persistConfig("{$this->SYSTEM_CONFIG_DIR}/{$plugin}.json", $nameSpace);
        }
    }

    /**
     * Installs the configuration file for a given plugin.
     *
     * This method installs the configuration file for a given plugin upon discovery.
     * The plugin name is used to create the configuration file.
     *
     * @param string $pluginName The name of the plugin to install the configuration file for.
     * @param string|null $defaultFile The default configuration file to use.
     * @return string The path to the installed configuration file.
     */
    public function installPluginConfig(string $pluginName, ?string $defaultFile): string
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
        return $pluginConfigFile;
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

    /**
     * Sets a configuration value by key IN MEMORY!
     * 
     * This method does not persist the configuration value to disk.
     *
     * This method sets the configuration value associated with the specified key.
     *
     * @param string $key The configuration key.
     * @param mixed $value The configuration value.
     */
    public function set(string $key, $value): void
    {
        ArrayUtil::pack($key, $value, $this->config);
    }
}
?>