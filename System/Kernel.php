<?php
// System/Kernel.php

declare(strict_types=1);

namespace Kraut;

use DI\ContainerBuilder;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Kraut\Routing\RouteLoader;
use Kraut\Service\ConfigService;
use Kraut\Service\ConfigurationService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;
use function FastRoute\cachedDispatcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Kraut\Service\PluginService;
use Psr\Container\ContainerInterface;

class Kernel
{
    private $container;

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

        $this->setTheme();
    }
    
    private function setTheme(): void
    {
        $configService = $this->container->get(ConfigurationService::class);
        $theme = $configService->get('theme', 'default');

        $loader = $this->container->get(Environment::class)->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(__DIR__ . "/../User/Theme/{$theme}", 'Theme');
        }
    }

    public function handle(string $method, string $uri): ResponseInterface
    {
        // Normalize the URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rawurldecode($uri);

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Get the PSR-17 factory from the container
        $psr17Factory = $this->container->get(Psr17Factory::class);

        // Create the ServerRequest
        $request = $psr17Factory->createServerRequest($method, $uri);

        // Load plugins
        $pluginLoader = $this->container->get(PluginService::class);
        $pluginLoader->loadPlugins();

        // Create the default middleware queue
        $middlewareQueue = [
            \Kraut\Middleware\LoggingMiddleware::class,
            \Kraut\Middleware\RequestBodyParserMiddleware::class,
            \Kraut\Middleware\RoutingMiddleware::class,
            \Kraut\Middleware\DispatchMiddleware::class,
        ];

        // Dispatch the middleware event
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $middlewareEvent = new \Kraut\Event\MiddlewareEvent($middlewareQueue);
        $eventDispatcher->dispatch($middlewareEvent, 'kernel.middleware');

        // Get the possibly modified middleware queue
        $middlewareQueue = $middlewareEvent->getMiddlewareQueue();

        // Create the Relay dispatcher with the container resolver
        $relay = new \Relay\Relay($middlewareQueue, [$this->container, 'get']);

        // Dispatch the request through the middleware queue
        $response = $relay->handle($request);

        // Dispatch an event after the response is generated
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $responseEvent = new \Kraut\Event\ResponseEvent($response);
        $eventDispatcher->dispatch($responseEvent, 'kernel.response');

        // Get the possibly modified response
        $response = $responseEvent->getResponse();

        return $response;
    }
}
?>