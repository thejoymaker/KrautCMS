<?php
declare(strict_types=1);
namespace Kraut\Service;

use Kraut\Model\UserInterface;
use Kraut\Util\CsrfTokenUtil;

class NoopAuthenticationService implements AuthenticationServiceInterface
{
    public function login(string $username, string $password): bool
    {
        return false;
    }
    public function logout(): void
    {
    }
    public function isAuthenticated(): bool
    {
        return false;
    }
    public function getCurrentUser(): ?UserInterface
    {
        return null;
    }

    // public function generateCsrfToken(): string
    // {
    //     return CsrfTokenUtil::generateToken();
    // }

    // public function validateCsrfToken(string $token): bool
    // {
    //     return CsrfTokenUtil::validateToken($token);
    // }
}
?>