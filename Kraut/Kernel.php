<?php
// System/Kernel.php

declare(strict_types=1);

namespace Kraut;

use DI\ContainerBuilder;
use Kraut\Exception\KrautException;
use Kraut\Service\AuthenticationServiceInterface;
use Kraut\Service\CacheService;
use Kraut\Util\ResponseUtil;
use Kraut\Service\ConfigurationService;
use Kraut\Service\NoopAuthenticationService;
use Kraut\Service\PluginService;
use Kraut\Service\RouteService;
use Kraut\Twig\HasPermissionTwigExtension;
use Kraut\Util\ServiceUtil;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PSpell\Config;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Kernel class for the KrautCMS framework.
 * 
 * This class is responsible for handling the core functionality of the KrautCMS.
 * It initializes and manages the various components and services required by the application.
 * 
 * @package KrautCMS
 * @subpackage Kernel
 */
class Kernel
{
    private KrautSystem $system;
    private ContainerInterface $container;

    /**
     * Initializes the DI container and the system.
     */
    public function __construct()
    {
        // Initialize the DI container
        $containerBuilder = new ContainerBuilder();

        // Enable PHP-DI to use PHP 8 attributes for autowiring
        $containerBuilder->useAttributes(true);

        // Add definitions for services
        $containerBuilder->addDefinitions([
            KrautSystem::class => \DI\autowire(KrautSystem::class),
            ConfigurationService::class => \DI\autowire(ConfigurationService::class),
            Psr17Factory::class => \DI\create(Psr17Factory::class),
            \Psr\Http\Message\ResponseFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\ServerRequestFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\StreamFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\UploadedFileFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Http\Message\UriFactoryInterface::class => \DI\get(Psr17Factory::class),
            \Psr\Log\LoggerInterface::class => function () {
                $logger = new \Monolog\Logger('krautcms');
                $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../Log/app.log', \Monolog\Level::Debug));
                return $logger;
            },
            Environment::class => function (ConfigurationService $config) {
                $loader = new FilesystemLoader(__DIR__ . '/View');
                $twig = new Environment($loader, [
                    'cache' => __DIR__ . '/../Cache/twig',
                    'debug' => true,
                ]);
                $pageName = $config->get(ConfigurationService::PAGE_NAME);
                $twig->addGlobal('pageName', $pageName);
                $pageDescription = $config->get(ConfigurationService::PAGE_DESCRIPTION);
                $twig->addGlobal('pageDescription', $pageDescription);
                $pageAuthor = $config->get(ConfigurationService::PAGE_AUTHOR);
                $twig->addGlobal('pageAuthor', $pageAuthor);
                $pageLanguage = $config->get(ConfigurationService::PAGE_LANGUAGE);
                $twig->addGlobal('pageLanguage', $pageLanguage);
                $twig->addExtension(new HasPermissionTwigExtension());
                // Optionally add global variables or extensions here
                return $twig;
            },
            EventDispatcherInterface::class => \DI\create(EventDispatcher::class),
            PluginService::class => function (ContainerInterface $c) {
                return new PluginService(
                    __DIR__ . '/../User/Plugin',
                    $c,
                    $c->get(EventDispatcherInterface::class),
                    $c->get(ConfigurationService::class),
                    $c->get(CacheService::class),
                    $c->get(LoggerInterface::class)
                );
            },
            AuthenticationServiceInterface::class => \DI\autowire(NoopAuthenticationService::class),
        ]);

        ServiceUtil::discoverPluginServices($containerBuilder);

        // Build the container
        $this->container = $containerBuilder->build();
        // $system = new KrautSystem();
        $this->system = $this->container->get(KrautSystem::class);
    }

    /**
     * Handles the incoming request and returns a response.
     *
     * @param string $method The HTTP method of the request (e.g., GET, POST).
     * @param string $uri The URI of the request.
     * @return ResponseInterface The response generated by handling the request.
     */
    public function handle(string $method, string $uri): ResponseInterface
    {
        $startTime = microtime(true);
        $logger = $this->container->get(LoggerInterface::class);
        $response = null;
        try {
            $this->system->discover();
            $requirementsMet = $this->system->requirementsMet();
            if(is_bool($requirementsMet) && $requirementsMet === true) {
                $this->system->load($method, $uri);
                $response = $this->system->run($method, $uri);
            } else {
                if(is_string($requirementsMet)) {
                    $response = ResponseUtil::respondRequirementsError($this->container, [], $requirementsMet);
                } else if(is_array($requirementsMet)) {
                    $response = ResponseUtil::respondRequirementsError($this->container, $requirementsMet);
                } else {
                    $response = ResponseUtil::respondRequirementsError($this->container, [], "Unknown requirements error. [{$method}] {$uri}");
                }
            }
        } catch (KrautException $e) {
            $logger->error($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            $twig = $this->container->get(Environment::class);
            $response = ResponseUtil::respondNegative($twig);
        } catch (\Throwable $e) {
            $logger->error($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            if(isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
                $response = ResponseUtil::respondErrorDetailed($e, $this->container);
            } else {
                $response = ResponseUtil::respondError($e, $this->container);
            }
        }
        $endTime = microtime(true);
        $logger->info("Request {$method} {$uri} completed in " . ($endTime - $startTime) . " seconds.");
        return $response;
    }
}
?>