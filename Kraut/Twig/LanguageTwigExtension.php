<?php
// Kraut/Twig/LabelTwigExtension.php

declare(strict_types=1);

namespace Kraut\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Kraut\Service\LanguageService;

class LanguageTwigExtension extends AbstractExtension
{
    public function __construct(private LanguageService $languageService)
    {
    }

    public function getFunctions(): array
    {
        return [
            // new TwigFunction('label', [$this, 'getLabel']),
            new TwigFunction('lang', [$this->languageService, 'lang']),
        ];
    }

    // public function lang(string $key, ?string $lang = null): string
    // {
    //     return $this->languageService->lang($key, $lang);
    // }
}