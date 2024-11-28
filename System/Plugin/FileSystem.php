<?php
// System/Plugin/FileSystem.php

declare(strict_types=1);

namespace Kraut\Plugin;

class FileSystem
{
    private string $pluginDir;

    private string $pluginName;

    public function __construct(string $pluginDir)
    {
        $this->pluginDir = $pluginDir;
        $this->pluginName = basename($pluginDir);
    }

    public function getPluginDir(): string
    {
        return $this->pluginDir;
    }

    public function getContentDir(): string
    {
        return $this->pluginDir . '/../../Content/' . basename($this->pluginDir);
    }

    public function getConfigFile(): string
    {
        return "{$this->pluginDir}/../../Config/{$this->pluginName}.cfg.php";
    }

    public function getCacheDir(): string
    {
        return "{$this->pluginDir}/../../Cache/{$this->pluginName}";
    }
}