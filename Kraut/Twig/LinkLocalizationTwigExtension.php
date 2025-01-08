<?php
// Kraut/Twig/LinkLocalizationTwigExtension.php

declare(strict_types=1);

namespace Kraut\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Kraut\Service\LanguageService;

class LinkLocalizationTwigExtension extends AbstractExtension
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