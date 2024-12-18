<?php
// User/Plugin/UserPlugin/Repository/UserRepository.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Repository;

use Kraut\Attribute\Service;
use Symfony\Component\Yaml\Yaml;
use User\Plugin\UserPlugin\Entity\User;

#[Service]
class UserRepository
{
    private string $usersDir;

    public function __construct()
    {
        $this->usersDir = __DIR__ . '/../../../Content/UserPlugin/users';

        if (!is_dir($this->usersDir)) {
            mkdir($this->usersDir, 0777, true);
        }
    }

    public function getUserByUsername(string $username): ?User
    {
        $directories = glob($this->usersDir . '/*', GLOB_ONLYDIR);
    
        foreach ($directories as $dir) {
            $userFile = $dir . '/' . basename($dir) . '.yaml';
    
            if (file_exists($userFile)) {
                $data = Yaml::parseFile($userFile);
                if ($data['username'] === $username) {
                    return User::fromArray($data);
                }
            }
        }
    
        return null;
    }

    public function getUserById(string $id): ?User
    {
        $directories = glob($this->usersDir . '/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $username = basename($dir);
            $userFile = $dir . '/' . $username . '.yaml';

            if (file_exists($userFile)) {
                $data = Yaml::parseFile($userFile);
                if ($data['id'] === $id) {
                    return User::fromArray($data);
                }
            }
        }

        return null;
    }

    public function addUser(User $user): void
    {
        $safeUsername = $this->sanitizeUsername($user->getName());
        $userDir = $this->usersDir . '/' . $safeUsername;
    
        if (!is_dir($userDir)) {
            mkdir($userDir, 0777, true);
        }
    
        $userFile = $userDir . '/' . $safeUsername . '.yaml';
        $data = $user->toArray();
    
        file_put_contents($userFile, Yaml::dump($data));
    }
    
    public function updateUser(User $user): void
    {
        $safeUsername = $this->sanitizeUsername($user->getName());
        $userDir = $this->usersDir . '/' . $safeUsername;

        if (!is_dir($userDir)) {
            throw new \Exception("User directory does not exist.");
        }

        $userFile = $userDir . '/' . $safeUsername . '.yaml';
        $data = $user->toArray();

        file_put_contents($userFile, Yaml::dump($data));
    }
    
    private function sanitizeUsername(string $username): string
    {
        // Remove any characters that are not alphanumeric, underscores, or dashes
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $username);
    }
}
?>