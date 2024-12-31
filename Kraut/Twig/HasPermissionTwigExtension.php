<?php

// Kraut/Twig/HasPermissionTwigExtension.php

declare(strict_types=1);

namespace Kraut\Twig;

use Kraut\Model\UserInterface;
use Kraut\Util\PermissionUtil;
use Twig\Extension\AbstractExtension;

class HasPermissionTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('hasPermission', [PermissionUtil::class, 'hasPermission']),
        ];
    }
}