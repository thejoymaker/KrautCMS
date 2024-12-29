<?php
// User/Plugin/LocalizationPlugin/Middleware/LanguageMiddleware.php
declare(strict_types=1);

namespace User\Plugin\LocalizationPlugin\Middleware;

use Kraut\Service\ConfigurationService;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use User\Plugin\LocalizationPlugin\Service\LanguageService;

class LanguageMiddleware implements MiddlewareInterface
{
    private array $supportedLanguages;
    private string $defaultLanguage;
    private const ASSETS_PATH = '/assets/';

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
        if (strpos($path, self::ASSETS_PATH) === 0) {
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
            $this->twig->addGlobal('current_language', $language);
            $this->twig->addGlobal('supported_languages', $this->languageService->getSupportedLanguages());
            $this->twig->addGlobal('default_language', $this->defaultLanguage);
            $newRequest = $request->withAttribute('language', $this->defaultLanguage);
            return $handler->handle($newRequest);
        }
    }
}