<?php
// System/Routing/RouteLoader.php

declare(strict_types=1);

namespace Kraut\Routing;

use FastRoute\RouteCollector;
use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

class RouteLoader
{
    private ContainerInterface $container;
    private string $controllerNamespace = 'Kraut\\Controller\\';
    private string $controllerDir;
    private string $pluginControllerDir = __DIR__ . '/../../User/Plugin';


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->controllerDir = __DIR__ . '/../Controller';
    }

    public function loadRoutes(RouteCollector $routeCollector): void
    {
        $this->loadRoutesFromDirectory($routeCollector, $this->controllerDir, $this->controllerNamespace);

        $pluginDirs = new RecursiveDirectoryIterator($this->pluginControllerDir);
        foreach ($pluginDirs as $pluginDir) {
            if ($pluginDir->isDir() && !in_array($pluginDir->getFilename(), ['.', '..'])) {
                $pluginControllerDir = $pluginDir->getPathname() . '/Controller';
                $pluginNamespace = 'User\\Plugin\\' . $pluginDir->getBasename() . '\\Controller\\';
                $this->loadRoutesFromDirectory($routeCollector, $pluginControllerDir, $pluginNamespace);
            }
        }
    }

    private function loadRoutesFromDirectory(RouteCollector $routeCollector, string $directory, string $namespace): void
    {
        if (!is_dir($directory)) {
            return;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

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

        // Check if the class has the #[Controller] attribute
        if (!$reflectionClass->getAttributes(Controller::class)) {
            return;
        }

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                $handler = [$className, $method->getName()];

                // Register the route with FastRoute
                foreach ($route->methods as $httpMethod) {
                    $routeCollector->addRoute($httpMethod, $route->path, $handler);
                }
            }
        }
    }
}
?>