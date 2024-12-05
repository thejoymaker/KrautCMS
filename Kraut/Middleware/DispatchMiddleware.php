<?php
// System/Middleware/DispatchMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class DispatchMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handlerInfo = $request->getAttribute('handler');

        if ($handlerInfo === null) {
            // No handler found, delegate to the next middleware
            return $handler->handle($request);
        }

        [$className, $methodName] = $handlerInfo;
        $vars = $request->getAttribute('vars', []);

        // Use the container to instantiate the controller
        $controller = $this->container->get($className);

        // Call the controller method
        return $controller->$methodName($request, $vars);
    }
}
?>