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
use User\Plugin\LocalizationPlugin\Service\LanguageService;

class MainNavigationMiddleware implements MiddlewareInterface
{
    public function __construct(private Environment $twig, private LanguageService $languageService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if(strpos($path, '/assets/') === 0) {
            return $handler->handle($request);
        }
        $language = $request->getAttribute('language');
        // $this->navigationService->setActiveItem($request->getUri()->getPath());
        $navigationDefinitionFile = __DIR__ . "/../../User/Config/Navigation.{$language}.yml";
        if (!file_exists($navigationDefinitionFile)) {
            $navigationDefinitionFile = __DIR__ . "/../../User/Config/Navigation.{$this->languageService->getDefaultLanguage()}.yml";
        }
        if (!file_exists($navigationDefinitionFile)) {
            $navigationDefinitionFile = __DIR__ . "/../../User/Config/Navigation.yml";
        }
        if (!file_exists($navigationDefinitionFile)) {
            throw new \RuntimeException('Navigation definition file not found');
        }
        $navData = Yaml::parseFile($navigationDefinitionFile);
        $this->twig->addGlobal('main_navigation', $navData['main_navigation']);
        return $handler->handle($request);
    }
}