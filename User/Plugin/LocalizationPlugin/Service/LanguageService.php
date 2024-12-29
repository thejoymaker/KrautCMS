<?php
// User/Plugin/LocalizationPlugin/Service/LanguageService.php

declare(strict_types=1);

namespace User\Plugin\LocalizationPlugin\Service;

use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Util\ArrayUtil;

class LanguageService
{
    private string $currentLang = 'en';
    private array $labelResourceMap = [];
    private array $currentLangLabels = [];

    public function __construct(
        private PluginService $pluginService, 
        private ConfigurationService $configurationService)
    {
    }

    public function setLanguage(string $language): void
    {
        $this->currentLang = $language;
        $activePluginPaths = $this->pluginService->getActivePluginPaths();
        foreach ($activePluginPaths as $pluginPath) {
            $labelResourcePath = $pluginPath . '/labels';
            if (is_dir($labelResourcePath)) {
                $nameSpace = strtolower(basename($pluginPath));
                $jsonFiles = glob($labelResourcePath . "/*.{$this->currentLang}.json");
                foreach ($jsonFiles as $jsonFile) {
                    $fileName = basename($jsonFile, ".{$this->currentLang}.json");
                    // $labels = array_merge($labels, json_decode(file_get_contents($jsonFile), true));
                    $this->labelResourceMap[$nameSpace][$fileName] = $jsonFile;
                }
                // $this->labelResourceMap[$pluginPath] = $labelResourcePath;
            }
        }
    }

    public function getDefaultLanguage(): string
    {
        return $this->configurationService->get('localizationplugin.default', 'en');
    }

    public function getLanguage(): string
    {
        return $this->currentLang;
    }

    public function getSupportedLanguages(): array
    {
        return $this->configurationService->get('localizationplugin.supported', ['en']);
    }

    public function getLabel(string $key): string
    {
        try {
            if(!ArrayUtil::isset($key, $this->currentLangLabels)) {
                $keyPath = explode('.', $key);
                if(count($keyPath) < 2) {
                    return $key;
                }
                $nameSpace = $keyPath[0];
                $fileName = $keyPath[1];
                $fileToLoad = $this->labelResourceMap[$nameSpace][$fileName];
                // $fileExists = file_exists($fileToLoad);
                // if(!$fileExists) {
                //     return $key;
                // }
                $newArray = json_decode(file_get_contents($fileToLoad), true);
                $this->currentLangLabels = array_merge($this->currentLangLabels, $newArray);
            }
            return ArrayUtil::unpack($key,  $this->currentLangLabels);
        } catch (\Exception $e) {
            return $key;
        }
    }
}