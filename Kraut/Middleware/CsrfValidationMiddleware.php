<?php
// System/Middleware/CsrfValidationMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Util\CsrfTokenUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

class CsrfValidationMiddleware implements MiddlewareInterface
{
    private string $csrfTokenKey = 'csrf_token';

    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Only process POST requests
        if ($request->getMethod() === 'POST') {
            $session = $request->getAttribute('session');
            if (!$session) {
                throw new \RuntimeException('Session is required for CSRF protection.');
            }

            // $tokenInSession = $session->get($this->csrfTokenKey);
            $parsedBody = $request->getParsedBody();
            $tokenInForm = $parsedBody[$this->csrfTokenKey] ?? null;
            $contenttype = $_SERVER['CONTENT_TYPE'];
            if (!$tokenInForm || !CsrfTokenUtil::isValidToken($tokenInForm)) {
                // Invalid CSRF token
                $responseFactory = $this->container->get(\Psr\Http\Message\ResponseFactoryInterface::class);
                $response = $responseFactory->createResponse(403);
                $response->getBody()->write('Invalid CSRF token.');
                return $response;
            }
        }

        return $handler->handle($request);
    }
}
?>