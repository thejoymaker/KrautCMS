<?php
// User/Plugin/PagesPlugin/PagesPlugin.php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin;

use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PagesPlugin implements PluginInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
            // Add other events and their corresponding methods here
        ];
    }

    public function activate(FileSystem $fileSystem): void
    {
        // Code to run when the plugin is activated
        // For example, you might want to register routes or initialize settings
    }

    public function deactivate(): void
    {
        // Code to run when the plugin is deactivated
        // For example, you might want to unregister routes or clean up settings
    }

    public function getContentProvider(): ?ContentProviderInterface
    {
        // Return the content provider for this plugin
        // For example, you might want to return a custom content provider
        return null;
    }

    public function getRequirements(): array
    {
        // Return the requirements for this plugin
        // For example, you might want to return an array of required services
        return [];
    }

    public function onKernelRequest($event): void
    {
        // Handle the kernel.request event
        // For example, you might want to modify the request or add some custom logic
    }
}
?>