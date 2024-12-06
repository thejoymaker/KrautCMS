<?php
declare(strict_types=1);
namespace Kraut\Util;

use Psr\Log\LoggerInterface;

class CacheUtil
{
    /**
     * Load data from cache if the cache is newer than the resource. 
     * otherwise, load the data from the loader and cache it.
     * invalidator is called when the cache is invalid.
     * 
     * @param string $cacheFile The cache file.
     * @param callable $loader The loader function.
     * @param string $resource The resource file or directory.
     * @param bool $cacheEnabled Whether caching is enabled.
     * @param callable $invalidator The invalidator function.
     * @param LoggerInterface $logger The logger.
     * 
     * @return array The data.
     */
    public static function loadCached(string $cacheFile, callable $loader, string $resource, 
        bool $cacheEnabled = true, callable $invalidator = null, LoggerInterface $logger = null): array
    {
        $maxFileTime = TimeUtil::maxFileMTime($resource);
        $cacheFileTime = file_exists($cacheFile) ? filemtime($cacheFile) : 0;
        // $logger = $this->container->get(LoggerInterface::class);
        if ($cacheEnabled && $cacheFileTime >= $maxFileTime) {
            if(!is_null($logger)){
                $cacheFileName = basename($cacheFile);
                $logger->info("Loading cached data from {$cacheFileName}");
            }
            return require $cacheFile;
        }
        if(!is_null($invalidator)){
            $invalidator($cacheFile);
        }
        $data = call_user_func($loader);
        // Create parent directory if it does not exist
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }
        if(!is_null($logger)){
            $cacheFileName = basename($cacheFile);
            $logger->info("Caching data to {$cacheFileName}");
        }
        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . '; ?>');
        return $data;
    }

    // public static function loadCachedByTTL(string $file, callable $loader, int $ttl = 3600): mixed
    // {
    //     if (file_exists($file) && time() - filemtime($file) < $ttl) {
    //         return unserialize(file_get_contents($file));
    //     }
    //     $data = $loader();
    //     file_put_contents($file, serialize($data));
    //     return $data;
    // }
}
?>