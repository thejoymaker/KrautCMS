<?php
// User/Plugin/UserPlugin/Middleware/AuthenticationMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Exception\KrautException;
use Kraut\Http\Session;
use Kraut\Service\AuthenticationServiceInterface;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Util\PermissionUtil;
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
        private ConfigurationService $configurationService,
        private \Twig\Environment $twig
        )
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $httpMethod = $request->getMethod();
        $user = $this->authService->getCurrentUser();

        if ($user) {
            // Add current_user as a global variable in Twig
            $this->twig->addGlobal('current_user', $user);
            $request = $request->withAttribute('current_user', $user);
            if (in_array('superuser', $user->getRoles())) {
                // SUPERUSER
                return $handler->handle($request);
            }
        }
        // Get route roles
        $roles = $this->pluginService->getRolesForRoute($httpMethod, $uri);

        $hasPermission = PermissionUtil::hasPermission($user, $roles);

        if (!$hasPermission) {
            // DISALLOWED ROUTE
            if ($this->pluginService->pluginActive('UserPlugin')) {
                // LOGIN AVAILABLE
                $loginObfuscated = $this->configurationService->get('userplugin.login.obfuscated', true);
                if(!$loginObfuscated){
                    /** @var Session $session */
                    $session = $request->getAttribute('session');
                    $session?->set('redirect', $uri);
                    return ResponseUtil::redirectTemporary('/user/login');
                }
            }
            throw KrautException::accessDenied($request);
        }
        return $handler->handle($request);
    }
}
?>