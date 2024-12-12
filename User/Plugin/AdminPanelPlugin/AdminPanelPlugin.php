<?php
// User/Plugin/AdminPanelPlugin/AdminPanelPlugin.php

declare(strict_types=1);

namespace User\Plugin\AdminPanelPlugin;

use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;

class AdminPanelPlugin implements PluginInterface
{
    public function __construct()
    {
        echo 'AdminPanelPlugin loaded';
    }

    public static function getSubscribedEvents(): array
    {
        return [];
    }

    public function activate(FileSystem $fileSystem): void
    {
        // Activation logic here
    }

    public function deactivate(): void
    {
        // Deactivation logic here
    }

    public function getContentProvider(): ?ContentProviderInterface
    {
        return null;
    }
}

?>