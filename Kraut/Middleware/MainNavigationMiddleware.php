<?php
// Kraut/Middleware/MainNavigationMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class MainNavigationMiddleware implements MiddlewareInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $this->navigationService->setActiveItem($request->getUri()->getPath());
        $navData = Yaml::parseFile(__DIR__ . '/../../User/Config/Navigation.yml');
        $this->twig->addGlobal('main_navigation', $navData['main_navigation']);
        return $handler->handle($request);
    }
}