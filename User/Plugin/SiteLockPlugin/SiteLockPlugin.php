<?php
// User/Plugin/SiteLockPlugin/SiteLockPlugin.php

declare(strict_types=1);

namespace User\Plugin\SiteLockPlugin;

use Kraut\Event\MiddlewareEvent;
use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;
use Kraut\Service\PluginService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use User\Plugin\SiteLockPlugin\Service\SiteLockService;

class SiteLockPlugin implements PluginInterface
{

    public function __construct()
    {
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
        $e->insertBefore("Kraut\Middleware\AuthenticationMiddleware", "User\Plugin\SiteLockPlugin\Middleware\SiteLockMiddleware");
    }
}