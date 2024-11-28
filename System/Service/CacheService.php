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

    private string $routeCacheFile = 'route.cache.php';
    
    private string $routeAttributeCacheFile = 'routeAttribute.cache.php';

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../Cache/';
    }
}
?>