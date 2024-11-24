<?php
// User/Plugin/UserPlugin/UserPlugin.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin;

use Kraut\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kraut\Event\MiddlewareEvent;
use Psr\Container\ContainerInterface;

class UserPlugin implements PluginInterface, EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.middleware' => 'onKernelMiddleware',
        ];
    }

    public function activate(): void
    {
        // Retrieve the Twig environment
        /** @var \Twig\Environment $twig */
        $twig = $this->container->get(\Twig\Environment::class);
    
        // Retrieve the existing loader
        /** @var \Twig\Loader\FilesystemLoader $loader */
        $loader = $twig->getLoader();
    
        if ($loader instanceof \Twig\Loader\FilesystemLoader) {
            // Add your plugin's template path without overwriting the loader
            $loader->addPath(__DIR__ . '/View', 'UserPlugin');
        }
    
        // Add current_user as a global variable in Twig
        $twig->addGlobal('current_user', $this->container->get(\User\Plugin\UserPlugin\Service\AuthenticationService::class)->getCurrentUser());
        
        // Configure Twig to load plugin templates
        // $loader = $this->container->get(\Twig\Loader\FilesystemLoader::class);
        // $loader->addPath(__DIR__ . '/views', 'UserPlugin');

        // Add current_user as a global variable in Twig
        // $twig = $this->container->get(\Twig\Environment::class);
        // $twig->setLoader($loader);
        // $twig->addGlobal('current_user', $this->container->get(\User\Plugin\UserPlugin\Service\AuthenticationService::class)->getCurrentUser());
    }

    public function deactivate(): void
    {
        // Any deactivation logic can be placed here.
    }

    public function onKernelMiddleware(MiddlewareEvent $event): void
    {
        // Insert AuthMiddleware before RoutingMiddleware
        $event->insertBefore(
            \Kraut\Middleware\RoutingMiddleware::class,
            \User\Plugin\UserPlugin\Middleware\AuthenticationMiddleware::class
        );
    }
}

?>