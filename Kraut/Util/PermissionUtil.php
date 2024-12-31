<?php

// Kraut/Util/PermissionUtil.php

declare(strict_types=1);

namespace Kraut\Util;

use Kraut\Model\UserInterface;

class PermissionUtil
{
    public static function hasPermission(?UserInterface $user, ?array $requiredRoles): bool
    {
        if ($requiredRoles === null || empty($requiredRoles)) {
            return true;
        }
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