<?php
// System/Service/CacheService.php

declare(strict_types=1);

namespace Kraut\Service;

use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class CacheService
 *
 * Service responsible for caching data.
 * This service provides methods to store, retrieve, and manage cached data.
 */
#
class CacheService
{
    private bool $cacheEnabled = false;
    private string $cacheDir;
    private string $configCacheFile;
    private string $pluginConfigCacheFile;
    private string $themeCacheFile;
    private string $pluginCacheFile;
    private string $routeCacheFile;
    private string $routeAttributeCacheFile;
    
    public function __construct(ContainerInterface $container)
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
        $this->configCacheFile = $this->cacheDir . 'System/config.cache.php';
        $this->pluginConfigCacheFile = $this->cacheDir . 'System/plugin_config.cache.php';
        $this->themeCacheFile = $this->cacheDir . 'System/theme.cache.php';
        $this->pluginCacheFile = $this->cacheDir . 'System/plugin.cache.php';
        $this->routeCacheFile = $this->cacheDir . 'Route/route.cache.php';
        $this->routeAttributeCacheFile = $this->cacheDir . 'Route/route_attribute.cache.php';
        // $this->cacheEnabled = $container->get(ConfigurationService::class)->get(ConfigurationService::CACHE_ENABLED, false);
    }

    public function loadCachedConfig(callable $loader, String $resource): array
    {
        return $this->loadCached($this->configCacheFile, $loader, $resource);
    }

    public function loadCachedPluginConfig(callable $loader, String $resource): array
    {
        return $this->loadCached($this->pluginConfigCacheFile, $loader, $resource);
    }

    public function loadCachedThemes(callable $loader, String $resource): array
    {
        return $this->loadCached($this->themeCacheFile, $loader, $resource);
    }

    public function loadCachedPluginModel(callable $loader, String $resource): array
    {
        return $this->loadCached($this->pluginCacheFile, $loader, $resource);
    }

    public function loadCachedRouteAttributes(callable $loader, String $resource): array
    {
        return $this->loadCached($this->pluginCacheFile, $loader, $resource);
    }

    private function loadCached(string $cacheFile, callable $loader, String $resource): array
    {
        $maxFileTime = $this->getMaxFileTime($resource);
        if ($this->cacheEnabled && file_exists($cacheFile) && filemtime($cacheFile) >= $maxFileTime) {
            return require $cacheFile;
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
            foreach ($iterator as $file) {
                $maxFileTime = max($maxFileTime, filemtime($file->getPath()));
            }
            return $maxFileTime;
        } else {
            return filemtime($resource);
        }
    }
}
?>