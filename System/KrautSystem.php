<?php
declare(strict_types=1);

namespace Kraut;

use DI\Container;
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

class KrautSystem
{
    private ContainerInterface $container;
    // private ConfigurationService $configService;
    // private SystemDiscoveryService $discoveryService;
    // private ThemeService $themeService;
    // private PluginService $pluginService;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

    public function setupSystem(): void
    {
        $this->setTheme();
        // Load plugins
        $pluginLoader = $this->container->get(PluginService::class);
        $pluginLoader->loadPlugins();
        // TODO 1. load system configuration
        // TODO 2 discover themes
        // TODO 3. setup theme
        // TODO 4. discover plugins
        // TODO 5. load plugins
        // TODO 6. persist setup cache
    }
    
    public function loadSystem(): void
    {

    }

    public function runSystem(string $method, string $uri): ResponseInterface
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