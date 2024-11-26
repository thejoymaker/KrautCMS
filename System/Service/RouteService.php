<?php
// System/Service/RouteService.php

declare(strict_types=1);

namespace Kraut\Service;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

class RouteService
{
    private ContainerInterface $container;
    private string $controllerNamespace = 'Kraut\\Controller\\';
    private string $controllerDir;
    private string $pluginControllerDir;
    private Dispatcher $dispatcher;
    private array $routeMap = [];
    private string $cacheFile;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->controllerDir = __DIR__ . '/../Controller';
        $this->pluginControllerDir = __DIR__ . '/../../User/Plugin';
        $this->cacheFile = __DIR__ . '/../../Cache/role_attribute_cache.php';

        if (file_exists($this->cacheFile)) {
            $this->routeMap = include $this->cacheFile;
        }
    }

    public function loadRoutes(RouteCollector $routeCollector): void
    {
        if (!empty($this->routeMap)) {
            // Load routes from cached routeMap
            foreach ($this->routeMap as $httpMethod => $routes) {
                foreach ($routes as $path => $info) {
                    $routeCollector->addRoute($httpMethod, $path, $info['handler']);
                }
            }
        } else {
            // Load routes from controllers
            $this->loadRoutesFromDirectory($routeCollector, $this->controllerDir, $this->controllerNamespace);

            // Load routes from plugin controllers
            $pluginDirs = new RecursiveDirectoryIterator($this->pluginControllerDir);
            foreach ($pluginDirs as $pluginDir) {
                if ($pluginDir->isDir() && !in_array($pluginDir->getFilename(), ['.', '..'])) {
                    $pluginControllerDir = $pluginDir->getPathname() . '/Controller';
                    $pluginNamespace = 'User\\Plugin\\' . $pluginDir->getBasename() . '\\Controller\\';
                    $this->loadRoutesFromDirectory($routeCollector, $pluginControllerDir, $pluginNamespace);
                }
            }

            // Save the routeMap to cache
            file_put_contents($this->cacheFile, '<?php return ' . var_export($this->routeMap, true) . ';');
        }

        // Build the dispatcher
        $this->dispatcher = new GroupCountBased($routeCollector->getData());
    }

    private function loadRoutesFromDirectory(RouteCollector $routeCollector, string $directory, string $namespace): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $namespace . $file->getBasename('.php');
                if (class_exists($className)) {
                    $this->registerRoutesFromClass($className, $routeCollector);
                }
            }
        }
    }

    private function registerRoutesFromClass(string $className, RouteCollector $routeCollector): void
    {
        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass->getAttributes(Controller::class)) {
            return;
        }

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttributes = $method->getAttributes(Route::class);
            foreach ($routeAttributes as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                $handler = [$className, $method->getName()];

                // Register the route
                foreach ($route->methods as $httpMethod) {
                    $routeCollector->addRoute($httpMethod, $route->path, $handler);

                    // Cache the route
                    $this->routeMap[$httpMethod][$route->path] = [
                        'handler' => $handler,
                        'attribute' => $route,
                    ];
                }
            }
        }
    }

    public function getRouteForUri(string $httpMethod, string $uri): ?Route
    {
        return $this->routeMap[$httpMethod][$uri]['attribute'] ?? null;
    }

    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }
}
?>