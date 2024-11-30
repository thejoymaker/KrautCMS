<?php
// System/Service/CacheService.php

declare(strict_types=1);

namespace Kraut\Service;

/**
 * Class CacheService
 *
 * Service responsible for caching data.
 * This service provides methods to store, retrieve, and manage cached data.
 */
#
class CacheService
{
    private string $cacheDir;
    private string $configCacheFile;
    private string $themeCacheFile;
    private string $pluginCacheFile;
    private string $routeCacheFile;
    private string $routeAttributeCacheFile;
    
    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
        $this->configCacheFile = $this->cacheDir . 'System/config.cache.php';
        $this->themeCacheFile = $this->cacheDir . 'System/theme.cache.php';
        $this->pluginCacheFile = $this->cacheDir . 'System/plugin.cache.php';
        $this->routeCacheFile = $this->cacheDir . 'Route/route.cache.php';
        $this->routeAttributeCacheFile = $this->cacheDir . 'Route/route_attribute.cache.php';
    }

    public function loadCachedConfig(callable $loader, String $resource): array
    {
        return $this->loadCached($this->configCacheFile, $loader, $resource);
    }

    private function loadCached(string $cacheFile, callable $loader, String $resource): array
    {
        if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($resource)) {
            return require $cacheFile;
        }
        $data = $loader();
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';');
        return $data;
    }
}
?>