<?php
// System/Kernel.php

declare(strict_types=1);

namespace Kraut;

use DI\ContainerBuilder;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Kernel
{
    private KrautSystem $system;
    private ContainerInterface $container;

    public function __construct()
    {
        // Initialize the DI container
        $containerBuilder = new ContainerBuilder();

        // Enable PHP-DI to use PHP 8 attributes for autowiring
        $containerBuilder->useAttributes(true);

        // Add definitions for services
        $containerBuilder->addDefinitions([
            ConfigurationService::class => \DI\create(ConfigurationService::class),
            Psr17Factory::class => \DI\create(Psr17Factory::class),
            \Psr\Http\Message\ResponseFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\ServerRequestFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\StreamFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\UploadedFileFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\UriFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Log\LoggerInterface::class => function () {
                $logger = new \Monolog\Logger('krautcms');
                $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../Cache/app.log', \Monolog\Logger::DEBUG));
                return $logger;
            },
            Environment::class => function () {
                $loader = new FilesystemLoader(__DIR__ . '/../User/Theme/default');
                $twig = new Environment($loader, [
                    'cache' => __DIR__ . '/../Cache/twig',
                    'debug' => true,
                ]);
                // Optionally add global variables or extensions here
                return $twig;
            },
            EventDispatcherInterface::class => \DI\create(EventDispatcher::class),
            PluginService::class => function (ContainerInterface $c) {
                return new PluginService(
                    __DIR__ . '/../User/Plugin',
                    $c,
                    $c->get(EventDispatcherInterface::class)
                );
            },
        ]);

        // Build the container
        $this->container = $containerBuilder->build();
        // $system = new KrautSystem();
        $this->system = $this->container->get(KrautSystem::class);
    }

    public function handle(string $method, string $uri): ResponseInterface
    {
        $this->system->setupSystem();
        $this->system->loadSystem();
        return $this->system->runSystem($method, $uri);
    }
}
?>