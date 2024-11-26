<?php
// System/Attribute/Route.php

declare(strict_types=1);

namespace Kraut\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public string $path;
    public array $methods;
    public array $roles;

    public function __construct(string $path, array $methods = ['GET'], array $roles = [])
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->roles = $roles;
    }

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