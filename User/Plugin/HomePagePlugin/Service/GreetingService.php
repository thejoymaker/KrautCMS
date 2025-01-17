<?php
// System/Service/GreetingService.php

declare(strict_types=1);

namespace User\Plugin\HomePagePlugin\Service;

use Kraut\Attribute\Service;

#[Service]
class GreetingService
{
    public function getGreeting(string $name): string
    {
        return "Hello, {$name}!";
    }
}
?>