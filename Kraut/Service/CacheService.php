<?php
// System/Service/CacheService.php

declare(strict_types=1);

namespace Kraut\Service;

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
    // private string $themeCacheFile;
    /**
     * @var string The plugin cache file.
     */
    private string $pluginCacheFile;

    private string $fastRouteCacheFile;
    
    public function __construct(private ContainerInterface $container)
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
        $this->configCacheFile = $this->cacheDir . 'System/config.cache.php';
        // $this->themeCacheFile = $this->cacheDir . 'System/theme.cache.php';
        $this->pluginCacheFile = $this->cacheDir . 'System/plugin.cache.php';
        $this->fastRouteCacheFile = $this->cacheDir . 'System/fastroute.cache.php';
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
        $maxFileTime = TimeUtil::maxFileMTime($resource);
        $cacheFileTime = file_exists($cacheFile) ? filemtime($cacheFile) : 0;
        $logger = $this->container->get(LoggerInterface::class);
        if ($this->cacheEnabled && file_exists($cacheFile) && $cacheFileTime >= $maxFileTime) {
            $logger->info("Loading cached data from {$cacheFile}");
            return require $cacheFile;
        }
        switch($cacheFile) {
            case $this->configCacheFile:
                // Clear the plugin cache if the config cache is being rebuilt
                if(file_exists($this->pluginCacheFile)){
                    unlink($this->pluginCacheFile);
                }
                break;
            case $this->pluginCacheFile:
                if(file_exists($this->fastRouteCacheFile)){
                    unlink($this->fastRouteCacheFile);
                }
                break;
        }
        $data = call_user_func($loader);
        // Create parent directory if it does not exist
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }
        $logger->info("Caching data to {$cacheFile}");
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . '; ?>');
        return $data;
    }
}
?>