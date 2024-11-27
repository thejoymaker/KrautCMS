<?php
// System/Attribute/Service.php

declare(strict_types=1);

namespace Kraut\Attribute;

use Attribute;

/**
 * Class Service
 *
 * Attribute to mark a class as a service.
 * This can be used for automatic service registration in a dependency injection container.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Service
{
}
?>