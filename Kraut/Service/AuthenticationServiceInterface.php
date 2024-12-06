<?php
declare(strict_types=1);
namespace Kraut\Service;

use Kraut\Model\UserInterface;

interface AuthenticationServiceInterface
{
    public function login(string $username, string $password): bool;
    public function logout(): void;
    public function isAuthenticated(): bool;
    public function getCurrentUser(): ?UserInterface;
    // public function generateCsrfToken(): string;
    // public function validateCsrfToken(string $token): bool;
}
?>