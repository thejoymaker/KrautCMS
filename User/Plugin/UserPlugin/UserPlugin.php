<?php
// User/Plugin/UserPlugin/UserPlugin.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin;

use Kraut\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kraut\Event\MiddlewareEvent;
use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\FileSystem;
use Psr\Container\ContainerInterface;
use User\Plugin\UserPlugin\Service\AuthenticationService;

class UserPlugin implements PluginInterface, EventSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
        ];
    }

    public function activate(FileSystem $fileSystem): void
    {
        // Retrieve the Twig environment
        /** @var \Twig\Environment $twig */
        // $twig = $this->container->get(\Twig\Environment::class);
    
        // Add current_user as a global variable in Twig
        // $twig->addGlobal('current_user', $this->container->
        //     get(AuthenticationService::class)->getCurrentUser());

    }

    public function deactivate(): void
    {
        // Any deactivation logic can be placed here.
    }

    public function getContentProvider(): ?ContentProviderInterface
    {
        // Return the content provider for this plugin
        // For example, you might want to return a custom content provider
        return null;
    }
}

?>