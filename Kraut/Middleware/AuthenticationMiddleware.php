<?php
// User/Plugin/UserPlugin/Middleware/AuthenticationMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Http\Session;
use Kraut\Service\AuthenticationServiceInterface;
use Kraut\Service\PluginService;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationServiceInterface $authService,
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
                /** @var Session $session */
                $session = $request->getAttribute('session');
                $session->set('redirect', $uri);
                return ResponseUtil::redirectTemporary('/login');
            }
        }

        return $handler->handle($request);
    }
}
?>