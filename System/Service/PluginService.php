<?php
// System/Service/PluginService.php

declare(strict_types=1);

namespace Kraut\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Kraut\Plugin\PluginInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PluginService
{
    private string $pluginDir;
    private ContainerInterface $container;
    private EventDispatcherInterface $eventDispatcher;
    private string $cacheFile;
    private array $plugins = [];

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
    
    public function loadPlugins(): void
    {
        $config = require __DIR__ . '/../../User/Config/Plugins.php';

        $loader = $this->container->get(Environment::class)->getLoader();

        if (false && file_exists($this->cacheFile)) {
            $this->plugins = include $this->cacheFile;
        } else {
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
                        $plugin->activate();
                    }
                }
            }

            // Cache the plugins
            file_put_contents($this->cacheFile, '<?php return ' . var_export($this->plugins, true) . ';');
        }
    }

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

    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
?>