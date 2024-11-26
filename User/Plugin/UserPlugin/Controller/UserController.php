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
        $content = $this->twig->render('@UserPlugin/login.html.twig');
        return new Response(200, [], $content);
    }

    #[Route(path: '/login', methods: ['POST'])]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->authService->login($username, $password)) {
            return new Response(302, ['Location' => '/admin']);
        }

        $content = $this->twig->render('@UserPlugin/login.html.twig', ['error' => 'Invalid credentials']);
        return new Response(200, [], $content);
    }

    #[Route(path: '/logout', methods: ['GET'])]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $this->authService->logout();
        return new Response(302, ['Location' => '/']);
    }

    #[Route(path: '/register', methods: ['GET'])]
    public function showRegistrationForm(ServerRequestInterface $request): ResponseInterface
    {
        $content = $this->twig->render('@UserPlugin/register.html.twig');
        return new Response(200, [], $content);
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
            $content = $this->twig->render('@UserPlugin/register.html.twig', ['error' => 'Username cannot be empty']);
            return new Response(200, [], $content);
        }
    
        // Validate that the passwords match
        if ($password !== $confirmPassword) {
            $content = $this->twig->render('@UserPlugin/register.html.twig', ['error' => 'Passwords do not match']);
            return new Response(200, [], $content);
        }
    
        // Check if the username already exists
        if ($this->userRepository->getUserByUsername($username)) {
            $content = $this->twig->render('@UserPlugin/register.html.twig', ['error' => 'Username already exists']);
            return new Response(200, [], $content);
        }
    
        // Proceed with registration
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = bin2hex(random_bytes(16)); // Generate a random unique ID
    
        $user = new User($userId, $username, $passwordHash);
        $this->userRepository->addUser($user);
        $this->authService->login($username, $password);
    
        return new Response(302, ['Location' => '/admin']);
    }

    #[Route(path: '/admin', methods: ['GET'], roles: ['user'])]
    public function admin(ServerRequestInterface $request): ResponseInterface
    {
        $content = $this->twig->render('@UserPlugin/admin.html.twig');
        return new Response(200, [], $content);
    }
    
    #[Route(path: '/change-password', methods: ['POST'])]
    public function changePassword(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmNewPassword = $data['confirm_new_password'] ?? '';
    
        // Validate that the new passwords match
        if ($newPassword !== $confirmNewPassword) {
            $content = $this->twig->render('@UserPlugin/admin.html.twig', ['error' => 'New passwords do not match']);
            return new Response(200, [], $content);
        }
    
        // Get the current user
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return new Response(302, ['Location' => '/login']);
        }
    
        // Verify current password
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            $content = $this->twig->render('@UserPlugin/admin.html.twig', ['error' => 'Current password is incorrect']);
            return new Response(200, [], $content);
        }
    
        // Update password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPasswordHash($newPasswordHash);
        $this->userRepository->updateUser($user);
    
        // $content = $this->twig->render('@UserPlugin/admin.html.twig', ['success' => 'Password changed successfully']);
        // return new Response(200, [], $content);
        return new Response(302, ['Location' => '/admin']);
    }
    
    #[Route(path: '/change-theme', methods: ['POST'])]
    public function changeTheme(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $theme = $data['theme'] ?? 'default';
    
        // Get the current user
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return new Response(302, ['Location' => '/login']);
        }
    
        // Update theme preference
        // $user->setTheme($theme);
        // $this->userRepository->updateUser($user);
    
        // $content = $this->twig->render('@UserPlugin/admin.html.twig', ['success' => 'Theme changed successfully']);
        // return new Response(200, [], $content);
        return new Response(302, ['Location' => '/admin']);
    }
}
?>