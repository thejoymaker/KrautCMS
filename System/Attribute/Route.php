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

    public function __construct(string $path, array $methods = ['GET'])
    {
        $this->path = $path;
        $this->methods = $methods;
    }
}
?>