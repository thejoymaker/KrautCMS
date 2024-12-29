<?php
// User/Plugin/LocalizationPlugin/Twig/LabelTwigExtension.php

declare(strict_types=1);

namespace User\Plugin\LocalizationPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use User\Plugin\LocalizationPlugin\Service\LanguageService;

class LabelTwigExtension extends AbstractExtension
{
    private string $defaultLanguage;

    public function __construct(private LanguageService $languageService)
    {
        $this->defaultLanguage = $languageService->getLanguage();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('label', [$this, 'getLabel']),
        ];
    }

    public function getLabel(string $key): string
    {
        return $this->languageService->getLabel($key);
    }
}