<?php
// User/Plugin/UserPlugin/Entity/User.php

declare(strict_types=1);

namespace User\Plugin\UserPlugin\Entity;

class User
{
    private string $id;
    private string $username;
    private string $passwordHash;

    public function __construct(string $id, string $username, string $passwordHash)
    {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'passwordHash' => $this->passwordHash,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['username'],
            $data['passwordHash']
        );
    }
}
?>