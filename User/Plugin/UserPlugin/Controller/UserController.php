<?php
// User/Plugin/UserPlugin/Controller/UserController.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Controller;

use User\Plugin\UserPlugin\Service\AuthenticationService;
use User\Plugin\UserPlugin\Repository\UserRepository;
use User\Plugin\UserPlugin\Entity\User;
use Twig\Environment;
use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;

#[Controller]
class UserController
{
    private Environment $twig;
    private AuthenticationService $authService;
    private UserRepository $userRepository;

    public function __construct(
        Environment $twig,
        AuthenticationService $authService,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    #[Route(path: '/login', methods: ['GET'])]
    public function showLoginForm(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'login');
    }

    #[Route(path: '/login', methods: ['POST'])]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if ($this->authService->login($username, $password)) {
            $redirect = $request->getAttribute('session')->get('redirect', '/admin');
            $request->getAttribute('session')->unset('redirect');
            return ResponseUtil::redirectTemporary($redirect);
        }
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'login', ['error' => 'Invalid credentials']);
    }

    #[Route(path: '/logout', methods: ['GET'])]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $this->authService->logout();
        return ResponseUtil::redirectTemporary('/');
    }

    #[Route(path: '/register', methods: ['GET'])]
    public function showRegistrationForm(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'register');
    }

    #[Route(path: '/register', methods: ['POST'])]
    public function register(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody() ?? [];
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
    
        // Validate that the username is not empty
        if (empty($username)) {
            return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'register', ['error' => 'Username cannot be empty']);
        }
    
        // Validate that the passwords match
        if ($password !== $confirmPassword) {
            return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'register', ['error' => 'Passwords do not match']);
        }
    
        // Check if the username already exists
        if ($this->userRepository->getUserByUsername($username)) {
            return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'register', ['error' => 'Username already exists']);
        }
    
        // Proceed with registration
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = bin2hex(random_bytes(16)); // Generate a random unique ID
    
        $user = new User($userId, $username, $passwordHash);
        $this->userRepository->addUser($user);
        $this->authService->login($username, $password);
    
        return ResponseUtil::redirectTemporary('/admin');
    }

    // #[Route(path: '/admin', methods: ['GET'], roles: ['user'])]
    // public function admin(ServerRequestInterface $request): ResponseInterface
    // {
    //     return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'admin');
    // }
    
    #[Route(path: '/change-password', methods: ['POST'])]
    public function changePassword(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmNewPassword = $data['confirm_new_password'] ?? '';
    
        // Validate that the new passwords match
        if ($newPassword !== $confirmNewPassword) {
            return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'admin', ['error' => 'New passwords do not match']);
        }
    
        // Get the current user
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return ResponseUtil::redirectTemporary('/login');
        }
    
        // Verify current password
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'admin', ['error' => 'Current password is incorrect']);
        }
    
        // Update password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPasswordHash($newPasswordHash);
        $this->userRepository->updateUser($user);
    
        return ResponseUtil::redirectTemporary('/admin');
    }
    
    #[Route(path: '/change-theme', methods: ['POST'])]
    public function changeTheme(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $theme = $data['theme'] ?? 'default';
    
        // Get the current user
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            // return new Response(302, ['Location' => '/login']);
            return ResponseUtil::redirectTemporary('/login');
        }
        return ResponseUtil::redirectTemporary('/admin');
    }
}
?>