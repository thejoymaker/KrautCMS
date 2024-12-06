<?php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Http\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Add the session to the request attributes
        $request = $request->withAttribute('session', new Session());

        return $handler->handle($request);
    }
}
?>