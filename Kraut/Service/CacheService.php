<?php
// System/Service/CacheService.php

declare(strict_types=1);

namespace Kraut\Service;

use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class CacheService
 *
 * Service responsible for caching data.
 * This service provides methods to store, retrieve, and manage cached data.
 */
#
class CacheService
{
    /**
     * @var bool Whether caching is enabled.
     */
    private bool $cacheEnabled;
    /**
     * @var string The cache directory.
     */
    private string $cacheDir;
    /**
     * @var string The config cache file.
     */
    private string $configCacheFile;
    // private string $themeCacheFile;
    /**
     * @var string The plugin cache file.
     */
    private string $pluginCacheFile;
    
    public function __construct(ContainerInterface $container)
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
        $this->configCacheFile = $this->cacheDir . 'System/config.cache.php';
        // $this->themeCacheFile = $this->cacheDir . 'System/theme.cache.php';
        $this->pluginCacheFile = $this->cacheDir . 'System/plugin.cache.php';
        $this->cacheEnabled = isset($_ENV['CACHE_ENABLED']) ? $_ENV['CACHE_ENABLED'] === 'true' : false;
    }

    public function loadCachedConfig(callable $loader, String $resource): array
    {
        return $this->loadCached($this->configCacheFile, $loader, $resource);
    }

    // public function loadCachedThemes(callable $loader, String $resource): array
    // {
    //     return $this->loadCached($this->themeCacheFile, $loader, $resource);
    // }

    public function loadCachedPluginModel(callable $loader, String $resource): array
    {
        return $this->loadCached($this->pluginCacheFile, $loader, $resource);
    }

    private function loadCached(string $cacheFile, callable $loader, String $resource): array
    {
        $maxFileTime = $this->getMaxFileTime($resource);
        $cacheFileTime = file_exists($cacheFile) ? filemtime($cacheFile) : 0;
        if ($cacheFileTime >= $maxFileTime) {
            echo "Loading from cache " . date("d-m-Y H:i:s", $maxFileTime) . " < " . date("d-m-Y H:i:s", $cacheFileTime) . "<br>\n";
        }
        if ($this->cacheEnabled && file_exists($cacheFile) && filemtime($cacheFile) >= $maxFileTime) {
            return require $cacheFile;
        }
        switch($cacheFile) {
            case $this->configCacheFile:
                // Clear the plugin cache if the config cache is being rebuilt
                unlink($this->pluginCacheFile);
            case $this->pluginCacheFile:
                // TODO unlink the route cache if the plugin cache is being rebuilt
                break;
        }
        $data = call_user_func($loader);
        // Create parent directory if it does not exist
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . '; ?>');
        return $data;
    }

    private function getMaxFileTime(string $resource): int
    {
        if(is_dir($resource)) {
            $directory = new RecursiveDirectoryIterator($resource);
            $iterator = new RecursiveIteratorIterator($directory);
            $maxFileTime = 0;
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                $maxFileTime = max($maxFileTime, filemtime($file->getPathname()));
            }
            return $maxFileTime;
        } else {
            return filemtime($resource);
        }
    }
}
?>