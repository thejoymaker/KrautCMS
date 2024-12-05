<?php
// System/Attribute/Controller.php

declare(strict_types=1);

namespace Kraut\Attribute;

use Attribute;

/**
 * Class Controller
 *
 * Attribute to mark a class as a controller.
 * This can be used for automatic route registration and dependency injection.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
}
?>