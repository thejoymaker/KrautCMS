<?php
declare(strict_types=1);

namespace Kraut;

use Kraut\Model\Manifest;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
// use Kraut\Service\ThemeService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
/**
 * The main class of the Kraut Core.
 * 
 * The KrautSystem class is called by the Kernel to initialize the system and run the application.
 * 
 * The class is responsible for the following phases:
 * 
 * 1. Initialization (loading the configuration, setting up the theme) by the constructor
 * 
 * 2. Discovery (discovering themes and plugins) by the discover method
 * 
 * 3. Requirements check (checking if the system requirements are met) by the requirementsMet method
 * 
 * 4. Loading Plugins (loading the plugins for the current request) by the load method
 * 
 * 5. Running the application (dispatching the request through the middleware queue) by the run method
 * 
 * The Kernel class is responsible for calling the methods of this class in the correct order.
 */
class KrautSystem
{
    /**
     * The configuration service.
     */
    private ConfigurationService $configService;
    /**
     * The theme service.
     */
    // private ThemeService $themeService;
    /**
     * The plugin service.
     */
    private PluginService $pluginService;
    /**
     * The manifest of the system.
     */
    private Manifest $manifest;

    private int $sequence;

    /**
     * The constructor of the KrautSystem class.
     * 
     * @param ContainerInterface $container The container interface.
     */
    public function __construct(private ContainerInterface $container)
    {
        $this->configService = $container->get(ConfigurationService::class);
        $this->pluginService = $this->container->get(PluginService::class);
        // $this->themeService = $this->container->get(ThemeService::class);
        if(!extension_loaded('json')) {
            throw new \RuntimeException('The JSON extension is required.');
        }
        $this->manifest = new Manifest(__DIR__ . '/Kraut.json');
        $this->setupTheme();
        $this->sequence = 1;
    }
    
    /**
     * Set up the theme. maps the theme directory to the 'Theme' namespace.
     */
    private function setupTheme(): void
    {
        $theme = $this->configService->get('kraut.theme.name', 'Ruben');
        $loader = $this->container->get(Environment::class)->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(__DIR__ . "/../User/Theme/{$theme}", 'Theme');
        }
    }

    /**
     * Discover themes and plugins.
     */
    public function discover(): void
    {
        if($this->sequence !== 1) {
            throw new \RuntimeException('Wrong sequence');
        }
        // $this->themeService->discoverThemes();
        $this->pluginService->discoverPlugins();
        $this->sequence++;
    }
    
    /**
     * Check if the system requirements are met.
     * 
     * The method checks if the cache directory is writable, if the PHP version is
     * sufficient and if all required PHP extensions are loaded.
     * 
     * @return bool|array|string True if the requirements are met, a string with 
     *      an error message or an array of missing extensions if not.
     */
    public function requirementsMet(): bool|array|string
    {
        if($this->sequence !== 2) {
            throw new \RuntimeException('Wrong sequence');
        }
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
        $this->sequence++;
        return true;
        // return ['some', 'bogus'];
    }

    // /**
    //  * Get the missing and outdated modules.
    //  * 
    //  * @param Manifest $manifest The manifest of the system.
    //  * 
    //  * @return array An array with the missing and outdated modules.
    //  */
    // public function getMissingModules(Manifest $manifest): array {
    //     $missingModules = [];
    //     $outdatedModules = [];
    //     foreach ($manifest->getRequiredPhpModules() as $module => $version) {
    //         if (!extension_loaded($module)) {
    //             $missingModules[] = $module;
    //         } elseif ($version !== '*' && phpversion($module) !== false 
    //         && version_compare(phpversion($module), $version, '<')) {
    //             $outdatedModules[] = $module;
    //         }
    //     }
    //     return ['missing' => $missingModules, 'outdated' => $outdatedModules];
    // }
    
    /**
     * Load the plugins for the current request.
     * 
     * @param string $method The HTTP method of the request.
     * @param string $path The path of the request.
     */
    public function load(string $method, string $path): void
    {
        if($this->sequence !== 3) {
            throw new \RuntimeException('Wrong sequence');
        }
        // Normalize the URI
        $path = parse_url($path, PHP_URL_PATH);
        $path = rawurldecode($path);

        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        $this->pluginService->loadPlugins($method, $path);
        $this->sequence++;
    }

    /**
     * Run the application.
     * 
     * @param string $method The HTTP method of the request.
     * @param string $uri The URI of the request.
     * 
     * @return ResponseInterface The response of the application.
     */
    public function run(string $method, string $uri): ResponseInterface
    {
        if($this->sequence !== 4) {
            throw new \RuntimeException('Wrong sequence');
        }
        // Normalize the URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rawurldecode($uri);

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Get the PSR-17 factory from the container
        /** @var Psr17Factory $psr17Factory */
        $psr17Factory = $this->container->get(Psr17Factory::class);

        // Create the ServerRequest
        $request = $psr17Factory->createServerRequest($method, $uri);
        // Create the default middleware queue
        $_POST['csrf_token'] = $_POST['csrf_token'] ?? '';
        $response = $this->executeMiddleware($request);

        // Dispatch an event after the response is generated
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $responseEvent = new \Kraut\Event\ResponseEvent($response);
        $eventDispatcher->dispatch($responseEvent, 'kernel.response');

        // Get the possibly modified response
        $response = $responseEvent->getResponse();

        return $response;
    }

    private function executeMiddleware(ServerRequestInterface $request): ResponseInterface
    {
        $middlewareQueue = [
            // \Kraut\Middleware\MalIntentDetectionMiddleware::class,
            \Kraut\Middleware\SessionMiddleware::class,
            \Kraut\Middleware\LoggingMiddleware::class,
            \Kraut\Middleware\RequestHeadersParserMiddleware::class,
            \Kraut\Middleware\RequestBodyParserMiddleware::class,
            \Kraut\Middleware\CsrfValidationMiddleware::class,
            \Kraut\Middleware\CsrfMiddleware::class,
            \Kraut\Middleware\AuthenticationMiddleware::class,
            \Kraut\Middleware\RoutingMiddleware::class,
            \Kraut\Middleware\MainNavigationMiddleware::class,
            \Kraut\Middleware\DispatchMiddleware::class,
        ];

        // Dispatch the middleware event
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $middlewareEvent = new \Kraut\Event\MiddlewareEvent($middlewareQueue);
        $eventDispatcher->dispatch($middlewareEvent, 'kernel.middleware');

        $middlewareEvent->postProcess();

        // Get the possibly modified middleware queue
        $middlewareQueue = $middlewareEvent->getMiddlewareQueue();

        // Create the Relay dispatcher with the container resolver
        $relay = new \Relay\Relay($middlewareQueue, [$this->container, 'get']);

        // Dispatch the request through the middleware queue
        $response = $relay->handle($request);
        return $response;
    }
}
?>