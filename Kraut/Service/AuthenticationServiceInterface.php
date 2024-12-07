<?php
declare(strict_types=1);
namespace Kraut\Service;

use Kraut\Model\UserInterface;


/**
 * Interface AuthenticationServiceInterface
 *
 * This interface defines the contract for authentication services.
 * Implementations of this interface should provide methods for user authentication,
 * including login, logout, and session management.
 *
 * @package Kraut\Service
 */
interface AuthenticationServiceInterface
{
    /**
     * Logs in a user with the provided username and password.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     * @return bool Returns true if the login is successful, false otherwise.
     */
    public function login(string $username, string $password): bool;
    /**
     * Logs out the current user.
     *
     * This method should handle any necessary cleanup or state changes
     * required to properly log out a user from the system.
     *
     * @return void
     */
    public function logout(): void;
    /**
     * Checks if the user is authenticated.
     *
     * @return bool True if the user is authenticated, false otherwise.
     */
    public function isAuthenticated(): bool;
    /**
     * Retrieves the current authenticated user.
     *
     * @return UserInterface|null The current user if authenticated, or null if no user is authenticated.
     */
    public function getCurrentUser(): ?UserInterface;
}
?>