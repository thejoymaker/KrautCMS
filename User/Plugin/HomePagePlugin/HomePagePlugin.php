<?php
// User/Plugin/HomePagePlugin/HomePagePlugin.php

declare(strict_types=1);

namespace User\Plugin\HomePagePlugin;

use Kraut\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HomePagePlugin implements PluginInterface
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

    public function activate(): void
    {
        // Code to run when the plugin is activated
        // For example, you might want to register routes or initialize settings
    }

    public function deactivate(): void
    {
        // Code to run when the plugin is deactivated
        // For example, you might want to unregister routes or clean up settings
    }

    public function onKernelRequest($event): void
    {
        // Handle the kernel.request event
        // For example, you might want to modify the request or add some custom logic
    }
}
?>