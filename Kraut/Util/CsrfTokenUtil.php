<?php
declare(strict_types=1);

namespace Kraut\Util;

class CsrfTokenUtil
{
    public static function generateToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function isValidToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        $storedToken = $_SESSION['csrf_token'];
        unset($_SESSION['csrf_token']);

        return hash_equals($storedToken, $token);
    }
}

?>