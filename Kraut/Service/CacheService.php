<?php
// System/Service/CacheService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Util\AssetsUtil;
use Kraut\Util\CacheUtil;
use Kraut\Util\FileSystemUtil;
use Kraut\Util\TimeUtil;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
    private string $themeCacheFile;
    /**
     * @var string The plugin cache file.
     */
    private string $pluginCacheFile;

    private string $fastRouteCacheFile;
    
    public function __construct(private ContainerInterface $container)
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
        $this->configCacheFile = $this->cacheDir . 'System/config.cache.php';
        $this->themeCacheFile = $this->cacheDir . 'System/theme.cache.php';
        $this->pluginCacheFile = $this->cacheDir . 'System/plugin.cache.php';
        $this->fastRouteCacheFile = $this->cacheDir . 'System/fastroute.cache.php';
        $this->cacheEnabled = isset($_ENV['CACHE_ENABLED']) ? $_ENV['CACHE_ENABLED'] === 'true' : false;
    }

    public function nukeCache(): void
    {
        $cacheFiles = [
            $this->cacheDir . 'System/services.php',
            $this->configCacheFile,
            $this->themeCacheFile,
            $this->pluginCacheFile,
            $this->fastRouteCacheFile
        ];

        foreach ($cacheFiles as $cacheFile) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }

        $twigCacheDir = $this->cacheDir . 'twig';
        if (file_exists($twigCacheDir)) {
            FileSystemUtil::unlinkDir($twigCacheDir);
        }

        AssetsUtil::deleteAssetCache();
    }

    public function loadCachedConfig(callable $loader, String $resource): array
    {
        return $this->loadCached($this->configCacheFile, $loader, $resource);
    }

    public function loadCachedThemes(callable $loader, String $resource): array
    {
        return $this->loadCached($this->themeCacheFile, $loader, $resource);
    }

    public function loadCachedPluginModel(callable $loader, String $resource): array
    {
        return $this->loadCached($this->pluginCacheFile, $loader, $resource);
    }

    public function invalidateCache($cacheFile): void
    {
        switch($cacheFile) {
            case $this->configCacheFile:
                // Clear the plugin cache if the config cache is being rebuilt
                if(file_exists($this->pluginCacheFile)){
                    unlink($this->pluginCacheFile);
                }
                break;
            case $this->pluginCacheFile:
                // Clear the fast route cache if the plugin cache is being rebuilt
                if(file_exists($this->fastRouteCacheFile)){
                    unlink($this->fastRouteCacheFile);
                }
                break;
        }
    }

    private function loadCached(string $cacheFile, callable $loader, String $resource): array
    {
        $logger = $this->container->get(LoggerInterface::class);
        return CacheUtil::loadCached($cacheFile, $loader, $resource, 
            $this->cacheEnabled, [$this, 'invalidateCache'], $logger);
    }
}
?>