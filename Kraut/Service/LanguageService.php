<?php
// Kraut/Service/LanguageService.php

declare(strict_types=1);

namespace Kraut\Service;

use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Util\ArrayUtil;

class LanguageService
{
    private string $currentLang = 'en';
    private array $langResourceIndex = [];
    private array $currentLangPack = [];

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
            $labelResourcePath = $pluginPath . '/lang';
            if (is_dir($labelResourcePath)) {
                $nameSpace = strtolower(basename($pluginPath));
                $jsonFiles = glob($labelResourcePath . "/*.{$this->currentLang}.json");
                foreach ($jsonFiles as $jsonFile) {
                    $fileName = basename($jsonFile, ".{$this->currentLang}.json");
                    $this->langResourceIndex[$nameSpace][$fileName] = $jsonFile;
                }
            }
        }
    }

    public function getDefaultLanguage(): string
    {
        return $this->configurationService->get('kraut.language.default', 'en');
    }

    public function getLanguage(): string
    {
        return $this->currentLang;
    }

    public function getSupportedLanguages(): array
    {
        return $this->configurationService->get('kraut.language.supported', ['en']);
    }

    public function lang(string $key, ?string $lang = null): string
    {
        try {
            if(!ArrayUtil::isset($key, $this->currentLangPack)) {
                $keyPath = explode('.', $key);
                if(count($keyPath) < 2) {
                    return $key;
                }
                $nameSpace = $keyPath[0];
                $fileName = $keyPath[1];
                $fileToLoad = $this->langResourceIndex[$nameSpace][$fileName];
                $newArray = json_decode(file_get_contents($fileToLoad), true);
                $this->currentLangPack = array_merge($this->currentLangPack, $newArray);
            }
            return ArrayUtil::unpack($key,  $this->currentLangPack);
        } catch (\Exception $e) {
            return $key;
        }
    }
}