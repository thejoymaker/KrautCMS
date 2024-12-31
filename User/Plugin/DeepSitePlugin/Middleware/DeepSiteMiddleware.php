<?php
// User/Middleware/DeepSiteMiddleware.php

declare(strict_types=1);

namespace User\Plugin\DeepSitePlugin\Middleware;

use Kraut\Util\ResponseUtil;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Plugin\DeepSitePlugin\Service\DeepSiteService;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\HtmlResponse;

class DeepSiteMiddleware implements MiddlewareInterface
{
    public function __construct(
        private DeepSiteService $deepSiteService,
        private \Twig\Environment $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $pathComponents = explode('/', trim($path, '/'));
        
        if (!isset($_SESSION['deepsiteaccess']) || $_SESSION['deepsiteaccess'] !== true) {
            // return $handler->handle($request);
            if (empty($pathComponents) || $pathComponents[0] !== $this->deepSiteService->getSecretPath()) {
                if ($this->deepSiteService->getRootRouteBehavior() === 'redirect') {
                    return ResponseUtil::redirectTemporary($this->deepSiteService->getRedirectUrl());
                } else {
                    return ResponseUtil::respondPlainText($this->deepSiteService->getMessage(), 400);
                }
            } else {
                $_SESSION['deepsiteaccess'] = true;
            }
        }
            
        // Remove the secret path component
        if(!empty($pathComponents) && $pathComponents[0] === $this->deepSiteService->getSecretPath()) {
            array_shift($pathComponents);
            $newPath = '/' . implode('/', $pathComponents);
            $newUri = $request->getUri()->withPath($newPath);
            $newRequest = $request->withUri($newUri);
            return $handler->handle($newRequest);
        } else {
            return $handler->handle($request);
        }
    }
}