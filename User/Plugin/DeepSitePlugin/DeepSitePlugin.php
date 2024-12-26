<?php
// User/Plugin/DeepSitePlugin/DeepSitePlugin.php

declare(strict_types=1);

namespace User\Plugin\DeepSitePlugin;

use Kraut\Event\MiddlewareEvent;
use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;

class DeepSitePlugin implements PluginInterface
{

    public function __construct(private ContainerInterface $container)
    {
        // $config = Yaml::parseFile(__DIR__ . '/../../Config/DeepSiteConfig.yml');
        // $this->deepSiteService = new DeepSiteService($config);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            "kernel.middleware" => "onKernelMiddleware",
        ];
    }

    public function activate(FileSystem $fileSystem): void
    {
        // Perform any initialization required by the plugin
    }

    public function deactivate(): void
    {
        // Clean up any resources or services initialized during activation
    }

    public function getContentProvider(): ?ContentProviderInterface
    {
        return null;
    }

    public function onKernelMiddleware(MiddlewareEvent &$e): void
    {
        $e->insertBefore("Kraut\Middleware\AuthenticationMiddleware", "User\Plugin\DeepSitePlugin\Middleware\DeepSiteMiddleware");
    }
}