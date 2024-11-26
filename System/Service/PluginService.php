<?php
// System/Service/PluginService.php

declare(strict_types=1);

namespace Kraut\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Kraut\Plugin\PluginInterface;

class PluginService
{
    private string $pluginDir;
    private ContainerInterface $container;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        string $pluginDir,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->pluginDir = $pluginDir;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function loadPlugins(): void
    {
        $config = require __DIR__ . '/../../User/Config/Plugins.php';

        $pluginDirectories = glob($this->pluginDir . '/*', GLOB_ONLYDIR);

        foreach ($pluginDirectories as $pluginPath) {
            $pluginName = basename($pluginPath);

            // Check if the plugin is enabled in the configuration
            if (!($config[$pluginName] ?? false)) {
                continue; // Skip loading this plugin
            }

            $className = "User\\Plugin\\$pluginName\\$pluginName";

            if (class_exists($className)) {
                /** @var PluginInterface $plugin */
                $plugin = $this->container->get($className);

                // Activate the plugin
                $plugin->activate();

                // Register event subscribers
                $this->eventDispatcher->addSubscriber($plugin);
            }
        }
    }
}
?>