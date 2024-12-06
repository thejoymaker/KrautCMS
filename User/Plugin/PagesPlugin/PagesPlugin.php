<?php
// User/Plugin/PagesPlugin/PagesPlugin.php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin;

use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\Content\ListResultInterface;
use Kraut\Plugin\FileSystem;
use Kraut\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use User\Plugin\PagesPlugin\Persistence\PageRepository;

class PagesPlugin implements PluginInterface
{
    private ?ContentProviderInterface $contentProvider = null;

    public function __construct(private EventDispatcherInterface $eventDispatcher,
                                private ContainerInterface $container)
    {
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
        if(is_null($this->contentProvider)){
            $this->contentProvider = new PageRepository();
        }
        return $this->contentProvider;
    }

    public function onKernelRequest($event): void
    {
        // Handle the kernel.request event
        // For example, you might want to modify the request or add some custom logic
    }
}
?>