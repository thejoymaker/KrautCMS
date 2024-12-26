<?php
// User/Plugin/DeepSitePlugin/Service/DeepSiteService.php

declare(strict_types=1);

namespace User\Plugin\DeepSitePlugin\Service;

use Kraut\Service\ConfigurationService;

class DeepSiteService
{
    public function __construct(private ConfigurationService $configurationService)
    {
    }

    public function getSecretPath(): string
    {
        return $this->configurationService->get('deepsiteplugin.secret-path', 'secret-path');
    }

    public function getRootRouteBehavior(): string
    {
        return $this->configurationService->get('deepsiteplugin.deny-behavior', 'message');
    }

    public function getRedirectUrl(): string
    {
        return $this->configurationService->get('deepsiteplugin.redirect-url', 'https://duckduckgo.com');
    }

    public function getMessage(): string
    {
        return $this->configurationService->get('deepsiteplugin.message', 'Private page');
    }
}