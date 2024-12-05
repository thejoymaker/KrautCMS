<?php
declare(strict_types=1);

namespace Kraut\Service;

use Twig\Environment;

/**
 * Class ThemeService
 *
 * This service is responsible for managing themes.
 */
class ThemeService 
{
    private string $loadedTheme;

    public function __construct(Environment $environment)
    {
        $this->loadedTheme = 'default';
    }

    public function loadTheme(string $theme): void
    {
        $this->loadedTheme = $theme;
    }

    public function getLoadedTheme(): string
    {
        return $this->loadedTheme;
    }

    public function discoverThemes(): void
    {
        // Discover themes
    }
}

?>