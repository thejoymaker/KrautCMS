<?php
// User/Plugin/UserPlugin/Middleware/AuthMiddleware.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Middleware;

use User\Plugin\UserPlugin\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();

        // Define protected routes
        $protectedRoutes = [
            '/admin',
            // Add more protected routes here
        ];

        if (in_array($uri, $protectedRoutes)) {
            if (!$this->authService->isAuthenticated()) {
                return new Response(302, ['Location' => '/login']);
            }
        }

        return $handler->handle($request);
    }
}
?>