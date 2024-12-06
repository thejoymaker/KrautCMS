<?php
// System/Kernel.php

declare(strict_types=1);

namespace Kraut;

use DI\ContainerBuilder;
use Kraut\Service\CacheService;
use Kraut\Util\ResponseUtil;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Service\RouteService;
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

class Kernel
{
    private KrautSystem $system;
    private ContainerInterface $container;

    /**
     * 
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
                $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../Cache/app.log', \Monolog\Logger::DEBUG));
                return $logger;
            },
            Environment::class => function () {
                $loader = new FilesystemLoader(__DIR__ . '/View');
                $twig = new Environment($loader, [
                    'cache' => __DIR__ . '/../Cache/twig',
                    'debug' => true,
                ]);
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
        ]);

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