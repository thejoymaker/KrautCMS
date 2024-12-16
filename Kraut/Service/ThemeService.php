<?php
// Kraut/Service/ThemeService.php
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
    // private string $loadedTheme;

    private array $themeModel = [];

    public function __construct(private Environment $environment,
                                private CacheService $cacheService,
                                private ConfigurationService $configurationService)
    {
        // $this->loadedTheme = 'default';
        $this->themeModel = $cacheService->loadCachedThemes([$this, "discoverThemes"], __DIR__ . "/../../User/Theme");
    }

    // public function loadTheme(string $theme): void
    // {
    //     $this->loadedTheme = $theme;
    // }

    // public function getLoadedTheme(): string
    // {
    //     return $this->loadedTheme;
    // }

    public function discoverThemes(): array
    {
        $themes = [];
        $themeDir = __DIR__ . '/../../User/Theme';

        $themeDirs = glob($themeDir . '/*', GLOB_ONLYDIR);

        foreach ($themeDirs as $theme) {
            $themeName = basename($theme);
            $themeConfig = "{$theme}/{$themeName}.json";
            if (file_exists($themeConfig)) {
                $themes[$themeName] = json_decode(file_get_contents($themeConfig), true);
            }
        }
        return $themes;
    }

    public function listThemes(): array
    {
        $themesList = [];
        foreach ($this->themeModel as $theme => $config) {
            $themesList[$theme] = [
                'active' => $theme === $this->configurationService->get(ConfigurationService::THEME_NAME, "default"),
                'name' => $theme,
                'author' => $config['author'],
                'description' => $config['description'],
                'version' => $config['version']
            ];
        }
        return $themesList;
    }
}

?>