<?php

// Kraut/Twig/HasPermissionTwigExtension.php

declare(strict_types=1);

namespace Kraut\Twig;

use Kraut\Model\UserInterface;
use Twig\Extension\AbstractExtension;

class HasPermissionTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('hasPermission', [$this, 'hasPermission']),
        ];
    }

    public function hasPermission(?UserInterface $user, array $requiredRoles): bool
    {
        if($user === null) {
            return in_array('guest', $requiredRoles);
        }
        $userRoles = $user->getRoles();
        if(in_array('superuser', $userRoles)) {
            return true;
        }
        foreach($requiredRoles as $requiredRole) {
            if(in_array($requiredRole, $userRoles)) {
                return true;
            }
        }
        return false;
    }
}