<?php
// User/Plugin/SiteLockPlugin/Middleware/SiteLockMiddleware.php

declare(strict_types=1);

namespace User\Plugin\SiteLockPlugin\Middleware;

use GuzzleHttp\Psr7\Response;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use User\Plugin\SiteLockPlugin\Service\SiteLockService;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class SiteLockMiddleware implements MiddlewareInterface
{
    // private SiteLockService $siteLockService;

    public function __construct(private SiteLockService $siteLockService, private Environment $twig)
    {
        // $this->siteLockService = $siteLockService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if ($session && $session->get('site_lock_open') === true) {
            return $handler->handle($request);
        }

        if ($request->getMethod() === 'POST' && $request->getParsedBody()['password']) {
            $password = $request->getParsedBody()['password'];
            if ($this->siteLockService->isValidPassword($password)) {
                $session->set('site_lock_open', true);
                // return $handler->handle($request);
                $requestPath = $request->getUri()->getPath();
                return ResponseUtil::redirectTemporary($requestPath);
            }
        }

        return ResponseUtil::respondRelative($this->twig, 'SiteLockPlugin', 'site-lock', 
            ["message"=>$this->siteLockService->getMessage()]);
    }
}