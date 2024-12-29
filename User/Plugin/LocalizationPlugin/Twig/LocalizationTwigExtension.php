<?php
// User/Plugin/LocalizationPlugin/Twig/LocalizationTwigExtension.php

declare(strict_types=1);

namespace User\Plugin\LocalizationPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use User\Plugin\LocalizationPlugin\Service\LanguageService;

class LocalizationTwigExtension extends AbstractExtension
{

    public function __construct(private LanguageService $languageService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('localize', [$this, 'localize']),
        ];
    }

    public function localize(string $url, ?string $language = null): string
    {
        $language = $language ?? $this->languageService->getLanguage();
        return '/' . $language . $url;
    }
}