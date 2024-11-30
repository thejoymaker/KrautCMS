<?php
declare(strict_types=1);

namespace Kraut;

use DI\Container;
use DI\ContainerBuilder;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Service\SystemDiscoveryService;
use Kraut\Service\SystemSetupService;
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
    private ConfigurationService $configService;
    private SystemSetupService $setupService;
    private PluginService $pluginService;

    public function __construct(ContainerInterface $container, ConfigurationService $configService, SystemSetupService $setupService)
    {
        $this->container = $container;
        $this->configService = $configService;
        $this->setupService = $setupService;
    }
    
    private function setupTheme(): void
    {
        $theme = $this->configService->get('theme', 'default');
        $loader = $this->container->get(Environment::class)->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(__DIR__ . "/../../User/Theme/{$theme}", 'Theme');
        }
    }

    public function discover(): void
    {
        $this->pluginService = $this->container->get(PluginService::class);
        // TODO 1. load system configuration
        // TODO 2 discover themes
        // TODO 3. setup theme
        $this->setupTheme();
        // TODO 4. discover plugins
        // TODO 5. load plugins
        // TODO 6. persist setup cache
    }
    
    public function load(): void
    {
        $this->pluginService->loadPlugins();

    }

    public function run(string $method, string $uri): ResponseInterface
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