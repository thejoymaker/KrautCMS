<?php
// System/Service/RouteService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Model\RouteModel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
/**
 * Class RouteService
 *
 * Service responsible for discovering routes.
 * It scans directories for controller classes and registers their routes.
 */
class RouteService
{
    /**
     * Discovers routes from a controller directory.
     *
     * @param string $controllerPath The path to the controller directory.
     */
    public function discoverRoutes(string $controllerPath): RouteModel
    {
        $model = new RouteModel();
        $pluginName = basename(realpath($controllerPath . '/..'));
        $controllerNamespace = 'User\\Plugin\\' . $pluginName . '\\Controller\\';
        $this->loadRoutesFromDirectory($controllerPath, $controllerNamespace, $model);
        return $model;
    }

    /**
     * Loads routes from a directory into the model.
     *
     * @param string $directory The directory to scan for controllers.
     * @param string $namespace The namespace of the controllers.
     * @param RouteModel $model The route model to load routes into.
     */
    private function loadRoutesFromDirectory(string $directory, string $namespace, RouteModel $model): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $namespace . $file->getBasename('.php');
                if (class_exists($className)) {
                    $this->registerRoutesFromClass($className, $model);
                }
            }
        }
    }

    /**
     * Registers routes from a controller class into the RouteModel.
     *
     * @param string $className The fully qualified class name of the controller.
     * @param RouteModel $model The route model to load routes into.
     */
    private function registerRoutesFromClass(string $className, RouteModel $model): void
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
                /** @var callable $handler */
                $handler = [$className, $method->getName()];
                // Register the route
                foreach ($route->methods as $httpMethod) {
                    $model->addRoute($route, $handler);
                }
            }
        }
    }
}
?>