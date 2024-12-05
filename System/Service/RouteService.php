<?php
// System/Service/RouteService.php

declare(strict_types=1);

namespace Kraut\Service;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Model\RouteModel;
use Manifest;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
/**
 * Class RouteService
 *
 * Service responsible for loading and managing routes.
 * It scans directories for controller classes and registers their routes.
 */
class RouteService
{
    private ContainerInterface $container;
    private string $pluginDir;
    private ?Dispatcher $dispatcher = null;
    // private array $routeMap = [];
    /**
     * @var RouteModel[]
     */
    private array $routeModelMap;
    private CacheService $cacheService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pluginDir = __DIR__ . '/../../User/Plugin';
        $this->cacheService = $container->get(CacheService::class);
    }

    /**
     * Loads routes into the RouteModel.
     */
    public function loadRoutes(): void
    {
        // $this->routeMap = $this->cacheService->loadCachedRouteAttributes([$this, 'discoverRoutes'], __DIR__ . "/../../User/Plugin");
        // if (!empty($this->routeMap)) {
            // Load routes from cached routeMap
            // foreach ($this->routeMap as $httpMethod => $routes) {
            //     foreach ($routes as $path => $info) {
            //         $routeCollector->addRoute($httpMethod, $path, $info['handler']);
            //     }
            // }
        // } else {
            // Load routes from controllers
            // $this->loadRoutesFromDirectory($routeCollector, $this->controllerDir, $this->controllerNamespace);

            // Load routes from plugin controllers
            
            // $pluginDirs = new RecursiveDirectoryIterator($this->pluginDir);
            // foreach ($pluginDirs as $pluginDir) {
            //     if ($pluginDir->isDir() && !in_array($pluginDir->getFilename(), ['.', '..'])) {
            //         $pluginControllerDir = $pluginDir->getPathname() . '/Controller';
            //         $pluginNamespace = 'User\\Plugin\\' . $pluginDir->getBasename() . '\\Controller\\';
            //         $this->loadRoutesFromDirectory($pluginControllerDir, $pluginNamespace);
            //     }
            // }

            // Save the routeMap to cache
            // file_put_contents($this->cacheFile, '<?php return ' . var_export($this->routeMap, true) . ';');
        // }

    }

    public function discoverRoutes(string $controllerPath): RouteModel
    {
        $model = new RouteModel();
        $pluginName = basename($controllerPath . '/..');
        $controllerNamespace = 'User\\Plugin\\' . $pluginName . '\\Controller\\';
        $this->loadRoutesFromDirectory($controllerPath, $controllerNamespace, $model);
        return $model;
        // $pluginsDir = $this->pluginDir;
        // $plugins = glob($pluginsDir . '/*', GLOB_ONLYDIR);
        // foreach ($plugins as $plugin) {
        //     $pluginName = basename($plugin);
        //     $pluginControllerDir = $plugin . '/Controller';
        //     if(null === $pluginControllerDir || !is_dir($pluginControllerDir)) {
        //         continue;
        //     }
        //     $controllerNamespace = 'User\\Plugin\\' . $pluginName . '\\Controller\\';
        //     $this->loadRoutesFromDirectory($pluginControllerDir, $controllerNamespace);
        // }
        // return $this->routeMap;

    }

    /**
     * Loads routes from a directory into the model.
     *
     * @param RouteCollector $routeCollector The route collector.
     * @param string $directory The directory to scan for controllers.
     * @param string $namespace The namespace of the controllers.
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
     * Registers routes from a controller class into the RouteCollector.
     *
     * @param string $className The fully qualified class name of the controller.
     * @param RouteCollector $routeCollector The route collector.
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
                $handler = [$className, $method->getName()];
                // Register the route
                foreach ($route->methods as $httpMethod) {
                    $model->addRoute($route, $handler);
                }
            }
        }
    }

    private RouteCollector $routeCollector; 

    public function setRouteCollector(RouteCollector $routeCollector): void
    {
        $this->routeCollector = $routeCollector;
    }

    /**
     * Returns the dispatcher instance.
     *
     * @return Dispatcher The dispatcher instance.
     */
    public function getDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null && $this->routeCollector !== null) {
            $this->dispatcher = new GroupCountBased($this->routeCollector->getData());
        } elseif ($this->dispatcher === null) {
            throw new \RuntimeException('Route collector not set.');
        }
        return $this->dispatcher;
    }
}
?>