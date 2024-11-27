<?php
// System/Plugin/PluginInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface PluginInterface extends EventSubscriberInterface
{
    public static function getSubscribedEvents(): array;
    public function activate(): void;
    public function deactivate(): void;
    public function getContentProvider(): ?ContentProviderInterface;
    public function getRequirements(): array;
}
?>