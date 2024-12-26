<?php
// User/Plugin/SiteLockPlugin/Service/SiteLockService.php

declare(strict_types=1);

namespace User\Plugin\SiteLockPlugin\Service;

use Kraut\Service\ConfigurationService;
use Symfony\Component\Yaml\Yaml;

class SiteLockService
{
    private array $hashedPasswords;

    public function __construct(private ConfigurationService $configurationService)
    {
        $config = Yaml::parseFile(__DIR__ . '/../../../Content/SiteLockPlugin/passwords.yml');
        $this->hashedPasswords = $config['passwords'];
    }

    public function isValidPassword(string $password): bool
    {
        foreach ($this->hashedPasswords as $hashedPassword) {
            if (password_verify($password, $hashedPassword)) {
                return true;
            }
        }
        return false;
    }

    public function getMessage(): string|null
    {
        return $this->configurationService->get('sitelockplugin.message', null);
    }   
}