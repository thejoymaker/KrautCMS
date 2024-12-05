<?php
declare(strict_types=1);

namespace Kraut\Model;

use Kraut\Attribute\Route;

class RouteModel
{
    /**
     * [httpMethod][path][handler|roles]
     */
    private array $routeMap;

    public static function __set_state($array): RouteModel
    {
        return new self($array['routeMap']);
    }

    public function __construct(array $routeMap = [])
    {
        $this->routeMap = $routeMap;
    }
    /**
     * Adds a route to the route map.
     *
     * @param Route $route The route to add.
     */
    public function addRoute(Route $route, $handler): void
    {
        foreach ($route->methods as $method) {
            $this->routeMap[$method][$route->path] = [
                'handler' => $handler,
                'roles' => $route->roles,
            ];
        }
    }

    public function getRolesForRoute(string $httpMethod, string $path): array
    {
        return $this->routeMap[$httpMethod][$path]['roles'] ?? [];
    }

    public function getHandlerForRoute(string $httpMethod, string $path): callable
    {
        return $this->routeMap[$httpMethod][$path]['handler'];
    }

    public function hasRoute(string $httpMethod, string $path): bool
    {
        return isset($this->routeMap[$httpMethod][$path]);
    }

    public function appendAll(RouteModel $otherModel): void
    {
        $this->routeMap = array_merge_recursive($this->routeMap, $otherModel->getRouteMap());
    }

    public function getRouteMap(): array
    {
        return $this->routeMap;
    }
}
?>