<?php
// User/Plugin/UserPlugin/Service/AuthenticationService.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Service;

use Kraut\Attribute\Service;
use Kraut\Service\AuthenticationServiceInterface;
use User\Plugin\UserPlugin\Repository\UserRepository;
use User\Plugin\UserPlugin\Entity\User;

#[Service]
class AuthenticationService implements AuthenticationServiceInterface
{
    private UserRepository $userRepository;
    private ?User $currentUser = null;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->loadCurrentUser();
    }

    private function loadCurrentUser(): void
    {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $this->currentUser = $this->userRepository->getUserById($userId);
        }
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userRepository->getUserByUsername($username);

        if ($user && password_verify($password, $user->getPasswordHash())) {
            $_SESSION['user_id'] = $user->getId();
            $this->currentUser = $user;
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        $this->currentUser = null;
        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }
    
}
?>