<?php
declare(strict_types=1);

namespace Kraut;

use Kraut\Model\Manifest;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Service\ThemeService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class KrautSystem
{
    private ConfigurationService $configService;
    private ThemeService $themeService;
    private PluginService $pluginService;
    private Manifest $manifest;

    public function __construct(private ContainerInterface $container)
    {
        $this->configService = $container->get(ConfigurationService::class);
        $this->pluginService = $this->container->get(PluginService::class);
        $this->themeService = $this->container->get(ThemeService::class);
        $this->manifest = new Manifest(__DIR__ . '/Kraut.json');
        $this->setupTheme();
    }
    
    private function setupTheme(): void
    {
        $theme = $this->configService->get('theme', 'default');
        $loader = $this->container->get(Environment::class)->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(__DIR__ . "/../User/Theme/{$theme}", 'Theme');
        }
    }

    public function discover(): void
    {
        $this->themeService->discoverThemes();
        $this->pluginService->discoverPlugins();
    }

    public function requirementsMet(): bool|array|string
    {
        if(!is_writable(__DIR__ . '/../Cache')) {
            return 'The cache directory is not writable.';
        }
        $requiredPhpVersion = $this->pluginService->getMaxRequiredPhpVersion($this->manifest->getRequiredPhpVersion());
        $currentPhpVersion = phpversion();
        if(version_compare($currentPhpVersion, $requiredPhpVersion, '<')) {
            return 'Current PHP Version: ' . $currentPhpVersion . ' < Required PHP Version: ' . $requiredPhpVersion;
        }
        $requiredExtensions = $this->pluginService->getRequiredExtensions();
        $systemRequiredExtensions = $this->manifest->getRequiredPhpModules();
        foreach ($systemRequiredExtensions as $extension => $version) {
            if (!in_array($extension, $requiredExtensions)) {
                $extensions[] = $extension;
            }
        }
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }
        if(!empty($missingExtensions)) {
            return $missingExtensions;
        }
        return true;
        // return ['some', 'bogus'];
    }

    public function getMissingModules(Manifest $manifest): array {
        $missingModules = [];
        $outdatedModules = [];
        foreach ($manifest->getRequiredPhpModules() as $module => $version) {
            if (!extension_loaded($module)) {
                $missingModules[] = $module;
            } elseif ($version !== '*' && phpversion($module) !== false 
            && version_compare(phpversion($module), $version, '<')) {
                $outdatedModules[] = $module;
            }
        }
        return ['missing' => $missingModules, 'outdated' => $outdatedModules];
    }
    
    public function load(string $method, string $path): void
    {
        // Normalize the URI
        $path = parse_url($path, PHP_URL_PATH);
        $path = rawurldecode($path);

        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        $this->pluginService->loadPlugins($method, $path);
        // throw new \RuntimeException('Not implemented');

    }

    public function run(string $method, string $uri): ResponseInterface
    {
        // Normalize the URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rawurldecode($uri);

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Get the PSR-17 factory from the container
        $psr17Factory = $this->container->get(Psr17Factory::class);

        // Create the ServerRequest
        $request = $psr17Factory->createServerRequest($method, $uri);
        // Create the default middleware queue
        $middlewareQueue = [
            \Kraut\Middleware\LoggingMiddleware::class,
            \Kraut\Middleware\RequestBodyParserMiddleware::class,
            \Kraut\Middleware\RoutingMiddleware::class,
            \Kraut\Middleware\DispatchMiddleware::class,
        ];

        // Dispatch the middleware event
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $middlewareEvent = new \Kraut\Event\MiddlewareEvent($middlewareQueue);
        $eventDispatcher->dispatch($middlewareEvent, 'kernel.middleware');

        // Get the possibly modified middleware queue
        $middlewareQueue = $middlewareEvent->getMiddlewareQueue();

        // Create the Relay dispatcher with the container resolver
        $relay = new \Relay\Relay($middlewareQueue, [$this->container, 'get']);

        // Dispatch the request through the middleware queue
        $response = $relay->handle($request);

        // Dispatch an event after the response is generated
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $responseEvent = new \Kraut\Event\ResponseEvent($response);
        $eventDispatcher->dispatch($responseEvent, 'kernel.response');

        // Get the possibly modified response
        $response = $responseEvent->getResponse();

        return $response;
    }
}
?>