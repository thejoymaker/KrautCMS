<?php
// User/Plugin/UserPlugin/Entity/User.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Entity;

class User
{
    private string $id;
    private string $username;
    private string $passwordHash;
    private array $roles;

    public function __construct(string $id, string $username, string $passwordHash, array $roles = ['user'])
    {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'passwordHash' => $this->passwordHash,
            'roles' => $this->roles,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['username'],
            $data['passwordHash'],
            $data['roles'] ?? ['user']
        );
    }
}
?>