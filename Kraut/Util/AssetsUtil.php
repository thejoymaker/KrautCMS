<?php

// Kraut/Util/AssetsUtil.php

declare(strict_types=1);

namespace Kraut\Util;

use Http\Discovery\Composer\Plugin;
use Kraut\Service\CacheService;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;

class AssetsUtil
{
    public const ASSETS_PATH = '/assets/';
    
    // private const CACHE_FILE = __DIR__ . '/../../Cache/System/assets.cache.php';

    private const CACHE_FILE = __DIR__ . '/../../Cache/System/assets.cache.php';

    public const ASSETS_ROUTE_PATTERN = self::ASSETS_PATH . '{path:.+}';

    public static function isAssetPath(string $path): bool
    {
        return strpos($path, self::ASSETS_PATH) === 0;
    }

    /**
     * Load the asset directories for the active plugins and the theme.
     * 
     * Plugin assets are located in the plugin's View/assets/ directory.
     * 
     * Plugin assets can override theme assets.
     * 
     * @return array the asset scape array organized by [asset path => full path].
     */
    private static function loadAssetDirectories(ConfigurationService $configurationService,
        PluginService $pluginService): array
    {
        $assetsDirs = [];
        $activePluginDirs = $pluginService->getActivePluginPaths();
        foreach ($activePluginDirs as $pluginDir) {
            $pluginAssetsDir = $pluginDir . '/View' . self::ASSETS_PATH;
            if(!file_exists($pluginAssetsDir) || !is_dir($pluginAssetsDir)) {
                continue;
            }
            $pluginName = basename($pluginDir);
            $assetsDirs[$pluginName] = realpath($pluginAssetsDir);
        }
        $theme = $configurationService->get(ConfigurationService::THEME_NAME, "Ruben");
        $themeAssetsDir = __DIR__ . '/../../User/Theme/' . $theme . self::ASSETS_PATH;
        $assetsDirs['Theme'] = realpath($themeAssetsDir);
        return $assetsDirs;
    }

    private static function loadPathFromAssetCache(
        string $assetPath, ConfigurationService $configService, PluginService $pluginService): string | null
    {
        $fullPath = null;
        $assetCache = self::loadAssetCache();
        if(!isset($assetCache[$assetPath])) {
            $assetScape = self::loadAssetDirectories($configService, $pluginService);
            foreach ($assetScape as $assetDir) {
                $fullPath = $assetDir . '/' . $assetPath;
                if (file_exists($fullPath) && is_file($fullPath)) {
                    break;
                }
            }
            if(is_null($fullPath)) {
                return null;
            }
            $assetCache[$assetPath] = $fullPath;
            self::persistAssetCache($assetCache);
        } else {
            $fullPath = $assetCache[$assetPath];
            if(!file_exists($fullPath) || !is_file($fullPath)) {
                $fullPath = null;
                // $assetCache[$assetPath] = $fullPath;
                unset($assetCache[$assetPath]);
                self::persistAssetCache($assetCache);
            }
        }
        return $fullPath;
    }

    private static function persistAssetCache(array $assetCache): void
    {
        file_put_contents(self::CACHE_FILE, '<?php return ' . var_export($assetCache, true) . ';');
    }

    private static function loadAssetCache(): array
    {
        if (file_exists(self::CACHE_FILE)) {
            return include self::CACHE_FILE;
        }
        return [];
    }

    public static function deleteAssetCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }

    public static function locateAsset(
        string $assetPath,
        ConfigurationService $configService,
        PluginService $pluginService
        ): string | null
    {
        $assetPath = str_replace(['..', './', '\\'], '', $assetPath);
        $fullPath = self::loadPathFromAssetCache($assetPath, $configService, $pluginService);
        if (is_null($fullPath) || !file_exists($fullPath) || !is_file($fullPath)) {
           return null;
        }
        return $fullPath;
    }
}