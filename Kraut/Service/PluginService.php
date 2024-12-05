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
    /** @var array<string,PluginInfo> */
    private array $pluginModel = [];

    /**
     * PluginService constructor.
     *
     * @param string $pluginDir The directory where plugins are stored.
     * @param ContainerInterface $container The dependency injection container.
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param ConfigurationService $configService The configuration service.
     * @param CacheService $cacheService The cache service.
     * @param RouteService $routeService The route service.
     */
    public function __construct(
        private string $pluginDir,
        private ContainerInterface $container,
        private EventDispatcherInterface $eventDispatcher,
        private ConfigurationService $configService,
        private CacheService $cacheService,
        private RouteService $routeService
    ) {
    }

    /**
     * Discover plugins (cached).
     */
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

    /**
     * Load the plugins.
     *
     * @param string $method The HTTP method.
     * @param string $path The request path.
     */
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
     * Collect routes from all active plugins.
     *
     * @param RouteCollector $routeCollector The route collector.
     */
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

    /**
     * Get the roles required for a route.
     *
     * @param string $method The HTTP method.
     * @param string $path The request path.
     *
     * @return array The roles required for the route.
     */
    public function getRolesForRoute(string $method, string $path): array
    {
        $roles = [];
        foreach ($this->pluginModel as $pluginName => $pluginInfo) {
            if (!$pluginInfo->isActive()) {
                continue;
            }
            $routeMap = $pluginInfo->getRouteModel()?->getRouteMap();
            if (!$routeMap) {
                continue;
            }
            foreach ($routeMap as $httpMethod => $routes) {
                foreach ($routes as $routePath => $info) {
                    if ($routePath === $path && $httpMethod === $method) {
                        $roles = array_merge($roles, $info['roles']);
                    }
                }
            }
        }
        return $roles;
    }

    /**
     * Get the maximum required PHP version for all plugins.
     *
     * @param string $systemRequiredVersion
     *   The system required PHP version.
     *
     * @return string
     *   The maximum required PHP version.
     */
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

    /**
     * Get the required PHP extensions for all plugins.
     */
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