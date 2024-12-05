<?php
// System/Attribute/Route.php

declare(strict_types=1);

namespace Kraut\Attribute;

use Attribute;

/**
 * Class Route
 *
 * Attribute to define a route for a controller method.
 * This attribute can be used to specify the path, HTTP methods, and required roles for the route.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @var string The path for the route.
     */
    public string $path;

    /**
     * @var array The HTTP methods allowed for the route.
     */
    public array $methods;

    /**
     * @var array The roles required to access the route.
     */
    public array $roles;

    /**
     * Route constructor.
     *
     * @param string $path The path for the route.
     * @param array $methods The HTTP methods allowed for the route.
     * @param array $roles The roles required to access the route.
     */
    public function __construct(string $path, array $methods = ['GET'], array $roles = [])
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->roles = $roles;
    }

    /**
     * Restores the state of the Route object from an array.
     *
     * @param array $state The state array.
     * @return self The restored Route object.
     */
    public static function __set_state(array $state): self
    {
        return new self(
            $state['path'],
            $state['methods'],
            $state['roles']
        );
    }
}
?>