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
use Kraut\Service\ConfigurationService;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;

#[Controller]
class UserController
{
    // private Environment $twig;
    // private AuthenticationService $authService;
    // private UserRepository $userRepository;
    private bool $registrationEnabled = true;
    private bool $registrationPublic = true;
    private bool $loginObfuscated = true;
    private string $secretKnock = 'knock';

    public function __construct(
        private Environment $twig,
        private AuthenticationService $authService,
        private UserRepository $userRepository,
        private ConfigurationService $configurationService
    ) {
        // $this->twig = $twig;
        // $this->authService = $authService;
        // $this->userRepository = $userRepository;
        $this->registrationEnabled = $this->configurationService->get('userplugin.registration.active', false);
        $this->registrationPublic = $this->configurationService->get('userplugin.registration.public', false);
        $this->loginObfuscated = $this->configurationService->get('userplugin.login.obfuscated', true);
        $this->secretKnock = $this->configurationService->get('userplugin.login.obfuscation', 'sesame-open');
    }

    #[Route(path: '/user/login[/{knock}]', methods: ['GET'])]
    public function showLoginForm(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if ($this->loginObfuscated) {
            $knock = $args['knock'] ?? '';
            if ($knock !== $this->secretKnock) {
                return ResponseUtil::respondNegative($this->twig);
            }
        }
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'login', [
            "login_post_address" => $request->getUri()->getPath(),
        ]);
    }

    #[Route(path: '/user/login[/{knock}]', methods: ['POST'])]
    public function login(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if ($this->loginObfuscated) {
            $knock = $args['knock'] ?? '';
            if ($knock !== $this->secretKnock) {
                return ResponseUtil::respondNegative($this->twig);
            }
        }
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if ($this->authService->login($username, $password)) {
            $redirect = $request->getAttribute('session')->get('redirect', '/');
            $request->getAttribute('session')->unset('redirect');
            return ResponseUtil::redirectTemporary($redirect);
        }
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'login', ['error' => 'Invalid credentials']);
    }

    #[Route(path: '/user/logout', methods: ['GET'], roles: ['user'])]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $this->authService->logout();
        return ResponseUtil::redirectTemporary('/');
    }

    #[Route(path: '/user/register', methods: ['GET'])]
    public function showRegistrationForm(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'UserPlugin', 'register');
    }

    #[Route(path: '/user/register', methods: ['POST'])]
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
    
    #[Route(path: '/user/change-password', methods: ['POST'])]
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
    
    #[Route(path: '/user/change-theme', methods: ['POST'])]
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

    // #[Route(path: '/user', methods: ['GET'], roles: ['user'])] // User home page

    // #[Route(path: '/user/reset-password', methods: ['GET'], roles: ['user'])] // show reset

    // #[Route(path: '/user/reset-password', methods: ['POST'], roles: ['user'])] // initiate reset

    // #[Route(path: '/user/reset-password/{secret}', methods: ['GET'], roles: ['user'])] // show reset form

    // #[Route(path: '/user/reset-password/{secret}', methods: ['POST'], roles: ['user'])] // reset password

    // #[Route(path: '/user/change-password', methods: ['GET'], roles: ['user'])] // show change password form

    // #[Route(path: '/user/change-password', methods: ['POST'], roles: ['user'])] // change password
}
?>