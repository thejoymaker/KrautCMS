<?php
// User/Plugin/UserPlugin/Middleware/AuthenticationMiddleware.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Middleware;

use Kraut\Controller\ResponseUtil;
use Kraut\Service\RouteService;
use User\Plugin\UserPlugin\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private AuthenticationService $authService;
    private RouteService $routeService;

    public function __construct(AuthenticationService $authService, RouteService $routeService)
    {
        $this->authService = $authService;
        $this->routeService = $routeService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $httpMethod = $request->getMethod();
        $user = $this->authService->getCurrentUser();

        // Get route attributes
        $route = $this->routeService->getRouteForUri($httpMethod, $uri);

        if ($route && !empty($route->roles)) {
            // Check if user has any of the required roles
            if (!$user || empty(array_intersect($user->getRoles(), $route->roles))) {
                return ResponseUtil::redirectTemporary('/login');
            }
        }

        return $handler->handle($request);
    }
}
?>