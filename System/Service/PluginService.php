<?php
// System/Service/PluginService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Plugin\FileSystem;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Kraut\Plugin\PluginInterface;
use Monolog\Logger;
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
    private string $cacheFile;
    private array $plugins = [];

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
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->pluginDir = $pluginDir;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheFile = __DIR__ . '/../../Cache/plugin_cache.php';
    }

    /**
     * Loads plugins from the plugin directory.
     *
     * This method scans the plugin directory, loads plugin classes, and activates them if they implement the PluginInterface.
     * It also caches the loaded plugins to improve performance.
     *
     * @return void
     */
    public function loadPlugins(): void
    {
        $config = require __DIR__ . '/../../User/Config/Plugins.php';

        $loader = $this->container->get(Environment::class)->getLoader();

        // if (false && file_exists($this->cacheFile)) {
        //     $this->plugins = include $this->cacheFile;
        // } else {
        $pluginDirectories = glob($this->pluginDir . '/*', GLOB_ONLYDIR);

        foreach ($pluginDirectories as $pluginPath) {
            $pluginName = basename($pluginPath);

            // Check if the plugin is enabled in the configuration
            if (!($config[$pluginName] ?? false)) {
                continue; // Skip loading this plugin
            }

            $className = "User\\Plugin\\$pluginName\\$pluginName";
            if (class_exists($className)) {
                $plugin = $this->container->get($className);
                if ($plugin instanceof PluginInterface) {
                    $this->plugins[$pluginName] = [
                        'class' => $className,
                        'active' => true,
                    ];

                    if ($loader instanceof FilesystemLoader) {
                        $viewPath = $pluginPath . '/View';
                        $this->container->get(LoggerInterface::class)->info("View path: $viewPath");
                        if (is_dir($viewPath)) {
                            $loader->addPath($viewPath, $pluginName);
                        }
                    }
                    $this->eventDispatcher->addSubscriber($plugin);
                    $plugin->activate(FileSystem::create($pluginPath));
                }
            }
        }

            // Cache the plugins
            // file_put_contents($this->cacheFile, '<?php return ' . var_export($this->plugins, true) . ';');
        // }
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
        foreach ($this->plugins as $pluginName => $pluginData) {
            if ($pluginData['active']) {
                $activePlugins[] = $this->pluginDir . '/' . $pluginName;
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
    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
?>