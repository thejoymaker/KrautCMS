<?php
// User/Plugin/UserPlugin/Middleware/AuthenticationMiddleware.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Middleware;

use Kraut\Service\PluginService;
use Kraut\Util\ResponseUtil;
use Kraut\Service\RouteService;
use User\Plugin\UserPlugin\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{


    public function __construct(
        private AuthenticationService $authService,
        private PluginService $pluginService,
        )
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $httpMethod = $request->getMethod();
        $user = $this->authService->getCurrentUser();

        // Get route roles
        $roles = $this->pluginService->getRolesForRoute($httpMethod, $uri);

        if (!empty($roles)) {
            // Check if user has any of the required roles
            if (!$user || empty(array_intersect($user->getRoles(), $roles))) {
                return ResponseUtil::redirectTemporary('/login');
            }
        }

        return $handler->handle($request);
    }
}
?>