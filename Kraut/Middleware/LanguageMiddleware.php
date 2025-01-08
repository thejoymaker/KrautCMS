<?php
// Kraut/Middleware/LanguageMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Service\ConfigurationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use Kraut\Service\LanguageService;
use Kraut\Util\AssetsUtil;

class LanguageMiddleware implements MiddlewareInterface
{
    private array $supportedLanguages;
    private string $defaultLanguage;
    // private const ASSETS_PATH = '/assets/';

    public function __construct(
        ConfigurationService $configurationService, 
        private LanguageService $languageService, 
        private Environment $twig)
    {
        $this->supportedLanguages = $configurationService->get('localizationplugin.supported', ['en']);
        $this->defaultLanguage = $configurationService->get('localizationplugin.default', 'en');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // Ignore /assets/.. paths
        if (strpos($path, AssetsUtil::ASSETS_PATH) === 0) {
            return $handler->handle($request);
        }

        $pathComponents = explode('/', trim($path, '/'));

        if (!empty($pathComponents) && in_array($pathComponents[0], $this->supportedLanguages)) {
            // Redirect to default language if no valid language code is found
            // return ResponseUtil::redirectTemporary('/' . $this->defaultLanguage . $path);
            // return new RedirectResponse('/' . $this->defaultLanguage . $path);
            // Set the language in the request attributes
            $language = array_shift($pathComponents);
            $this->languageService->setLanguage($language);
            $newPath = '/' . implode('/', $pathComponents);
            $newUri = $request->getUri()->withPath($newPath);
            $newRequest = $request->withUri($newUri)->withAttribute('language', $language);
            $this->twig->addGlobal('current_language', $language);
            $this->twig->addGlobal('supported_languages', $this->languageService->getSupportedLanguages());
            $this->twig->addGlobal('default_language', $this->defaultLanguage);
            return $handler->handle($newRequest);
        } else {
            $language = $this->languageService->getLanguage();
            $this->languageService->setLanguage($language);
            $this->twig->addGlobal('current_language', $language);
            $this->twig->addGlobal('supported_languages', $this->languageService->getSupportedLanguages());
            $this->twig->addGlobal('default_language', $this->defaultLanguage);
            $newRequest = $request->withAttribute('language', $this->defaultLanguage);
            return $handler->handle($newRequest);
        }
    }
}