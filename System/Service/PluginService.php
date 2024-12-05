<?php
// System/Service/PluginService.php

declare(strict_types=1);

namespace Kraut\Service;

use FastRoute\RouteCollector;
use Kraut\Model\Manifest;
use Kraut\Model\PluginInfo;
use Kraut\Plugin\FileSystem;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Kraut\Plugin\PluginInterface;
use Monolog\Logger;
use PSpell\Config;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class PluginService
 *
 * Service to manage plugins.
 * This service is responsible for loading, activating, and managing plugins within the system.
 */
class PluginService
{
    private string $pluginDir;
    private ContainerInterface $container;
    private EventDispatcherInterface $eventDispatcher;
    private ConfigurationService $configService;
    private CacheService $cacheService;
    private RouteService $routeService;
    /** @var array<string,PluginInfo> */
    private array $pluginModel = [];

    /**
     * PluginService constructor.
     *
     * @param string $pluginDir
     *   The directory where plugins are stored.
     * @param ContainerInterface $container
     *   The dependency injection container.
     * @param EventDispatcherInterface $eventDispatcher
     *   The event dispatcher.
     */
    public function __construct(
        string $pluginDir,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        ConfigurationService $configService
    ) {
        $this->pluginDir = $pluginDir;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        // $this->cacheFile = __DIR__ . '/../../Cache/plugin_cache.php';
        $this->configService = $configService;
        $this->cacheService = $container->get(CacheService::class);
        $this->routeService = $container->get(RouteService::class);
    }

    public function collectRoutes(RouteCollector $routeCollector): void
    {
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            if (!$pluginInfo->isActive()) {
                continue;
            }
            $routeMap = $pluginInfo->getRouteModel()?->getRouteMap();
            if (!$routeMap) {
                continue;
            }
            foreach ($routeMap as $httpMethod => $routes) {
                foreach ($routes as $path => $info) {
                    $routeCollector->addRoute($httpMethod, $path, $info['handler']);
                }
            }
        }
        // $routeMap = $this->routeModel->getRouteMap();
        // foreach ($routeMap as $httpMethod => $routes) {
        //     foreach ($routes as $path => $info) {
        //         $routeCollector->addRoute($httpMethod, $path, $info['handler']);
        //     }
        // }
        // $this->routeCollector = $routeCollector;
    }

    public function discoverPlugins() {
        $this->pluginModel = $this->cacheService->loadCachedPluginModel([$this, 'initializeModel'], 
            $this->pluginDir);
    }

    /**
     * Initialize the plugin model. called by CacheService. Do not call directly!
     *
     * @return array The plugin model.
     */
    public function initializeModel(): array
    {
        // $routeModel = $this->routeService->discoverRoutes();
        // $this->pluginConfig = $this->cacheService->loadCachedConfig();
        $plugins = [];
        $pluginDirectories = glob($this->pluginDir . '/*', GLOB_ONLYDIR);
        foreach ($pluginDirectories as $pluginPath) {
            $pluginName = basename($pluginPath);
            $manifestFile = $pluginPath . "/{$pluginName}.json";
            if(!file_exists($manifestFile)) {
                continue;
            }
            $manifest = new Manifest($pluginPath . "/{$pluginName}.json");
            $viewsPath = $pluginPath . '/View';
            if (!is_dir($viewsPath)) {
                $viewsPath = null;
            }
            $controllersPath = $pluginPath . '/Controller';
            $pluginRoutes = null;
            if (!is_dir($controllersPath)) {
                $controllersPath = null;
            } else {
                $pluginRoutes = $this->routeService->discoverRoutes($controllersPath);
            }

            $pluginNameLower = strtolower($pluginName);
            $active = $this->configService->get("{$pluginNameLower}.active", null);
            if ($active === null) {
                $defaultConfig = "{$pluginPath}/default.config.json";
                if(!file_exists($defaultConfig)) {
                    $defaultConfig = null;
                }
                $this->configService->installPluginConfig($pluginName, $defaultConfig);
                $active = $this->configService->get("{$pluginNameLower}.active", true);
            }
            $className = "User\\Plugin\\$pluginName\\$pluginName";
            $plugins[$pluginName] = new PluginInfo(
                $className, 
                $active, 
                $pluginPath, 
                $manifest,
                $viewsPath,
                $controllersPath,
                $pluginRoutes
            );
        }
        return $plugins;
    }

    public function loadPlugins(string $method, string $path): void
    {
        $loader = $this->container->get(Environment::class)->getLoader();
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            // Check if the plugin is enabled in the configuration
            if (!$pluginInfo->isActive()) {
                continue; // Skip loading this plugin
            }

            $className = "User\\Plugin\\$pluginName\\$pluginName";
            if (class_exists($className)) {
                $plugin = $this->container->get($className);
                if ($plugin instanceof PluginInterface) {
                    if ($loader instanceof FilesystemLoader && $pluginInfo->getViews() !== null) {
                        $viewPath = $pluginInfo->getViews();
                        // $this->container->get(LoggerInterface::class)->info("View path: $viewPath");
                        if (is_dir($viewPath)) {
                            $loader->addPath($viewPath, $pluginName);
                        }
                    }
                    $this->eventDispatcher->addSubscriber($plugin);
                    $plugin->activate(FileSystem::create($pluginInfo->getPath()));
                }
            }
        }
    }

    /**
     * Get the active plugin directories.
     *
     * @return array
     *   An array of active plugin directories.
     */
    public function getActivePluginDirectories(): array
    {
        $activePlugins = [];
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            if ($pluginInfo->isActive()) {
                $activePlugins[] = $pluginInfo->getPath();
            }
        }
        return $activePlugins;
    }

    /**
     * Get the plugins.
     *
     * @return array
     *   An array of plugins.
     */
    public function getModel(): array
    {
        return $this->pluginModel;
    }

    public function getMaxRequiredPhpVersion(string $systemRequiredVersion): string
    {
        $maxVersion = $systemRequiredVersion;
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            $requiredVersion = $pluginInfo->getManifest()->getRequiredPhpVersion();
            if(is_null($requiredVersion)) {
                continue;
            }
            if (version_compare($requiredVersion, $maxVersion, '>')) {
                $maxVersion = $requiredVersion;
            }
        }
        return $maxVersion;
    }

    public function getRequiredExtensions(): array
    {
        $extensions = [];
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            $requiredExtensions = $pluginInfo->getManifest()->getRequiredPhpModules();
            foreach ($requiredExtensions as $extension => $version) {
                if (!in_array($extension, $extensions)) {
                    $extensions[] = $extension;
                }
            }
        }
        return $extensions;
    }
}
?>