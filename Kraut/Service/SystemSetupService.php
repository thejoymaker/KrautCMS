<?php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class SystemDiscoveryService
 *
 * This service is responsible for discovering and managing system components.
 */
class SystemSetupService
{
    private string $userDir;

    private string $configDir;

    private string $themeDir;

    private string $pluginDir;

    private ThemeService $themeService;

    private PluginService $pluginService;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, ThemeService $themeService, PluginService $pluginService)
    {
        $this->container = $container;
        $this->userDir = __DIR__ . '/../../User/';
        $this->configDir = $this->userDir . 'Config/';
        $this->themeDir = $this->userDir . 'Theme/';
        $this->pluginDir = $this->userDir . 'Plugin/';
        $this->themeService = $themeService;
        $this->pluginService = $pluginService;
    }

    public function discoverPlugins()
    {
    }

    public function getUserDir(): string
    {
        return $this->userDir;
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    public function getPluginDir(): string
    {
        return $this->pluginDir;
    }

    public function getThemeDir(): string
    {
        return $this->themeDir;
    }
}