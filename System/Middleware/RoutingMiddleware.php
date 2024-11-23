<?php
// System/Middleware/RoutingMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Kraut\Routing\RouteLoader;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use function FastRoute\cachedDispatcher;

class RoutingMiddleware implements MiddlewareInterface
{
    private Dispatcher $dispatcher;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->dispatcher = cachedDispatcher(function (RouteCollector $routeCollector) {
            $routeLoader = new RouteLoader($this->container);
            $routeLoader->loadRoutes($routeCollector);
        }, [
            'cacheFile' => __DIR__ . '/../../Cache/route.cache',
            'cacheDisabled' => false,
        ]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $responseFactory = $this->container->get(\Psr\Http\Message\ResponseFactoryInterface::class);
                $response = $responseFactory->createResponse(404);
                $response->getBody()->write('404 Not Found');
                return $response;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $responseFactory = $this->container->get(\Psr\Http\Message\ResponseFactoryInterface::class);
                $response = $responseFactory->createResponse(405);
                $response->getBody()->write('405 Method Not Allowed');
                return $response;

            case Dispatcher::FOUND:
                $handlerInfo = $routeInfo[1];
                $vars = $routeInfo[2];

                // Add route information to the request attributes
                $request = $request
                    ->withAttribute('handler', $handlerInfo)
                    ->withAttribute('vars', $vars);

                // Proceed to the next middleware
                return $handler->handle($request);

            default:
                $responseFactory = $this->container->get(\Psr\Http\Message\ResponseFactoryInterface::class);
                $response = $responseFactory->createResponse(500);
                $response->getBody()->write('500 Internal Server Error');
                return $response;
        }
    }
}
?>