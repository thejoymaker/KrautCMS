<?php
declare(strict_types=1);

// plugins_autoloader.php

class PluginAutoloader
{
    private string $pluginsDirectory;
    private array $namespaceMap = [];
    private bool $cacheEnabled;
    private string $cacheFile;

    public function __construct(string $pluginsDirectory = null, bool $cacheEnabled = true)
    {
        $this->pluginsDirectory = $pluginsDirectory ?: __DIR__ . '/plugins';
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheFile = sys_get_temp_dir() . '/plugin_psr4_autoload.php';

        if ($this->cacheEnabled && $this->isCacheValid()) {
            $this->loadNamespaceMapFromCache();
        } else {
            $this->buildNamespaceMap();
            if ($this->cacheEnabled) {
                $this->writeCache();
            }
        }

        $this->registerAutoloader();
    }

    private function isCacheValid(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $cacheMTime = filemtime($this->cacheFile);
        $pluginsMTime = $this->getPluginsModificationTime();

        return $cacheMTime >= $pluginsMTime;
    }

    private function getPluginsModificationTime(): int
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->pluginsDirectory, FilesystemIterator::SKIP_DOTS)
        );

        $maxMTime = 0;
        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $mtime = $file->getMTime();
            if ($mtime > $maxMTime) {
                $maxMTime = $mtime;
            }
        }

        return $maxMTime;
    }

    private function loadNamespaceMapFromCache(): void
    {
        $this->namespaceMap = require $this->cacheFile;
    }

    private function buildNamespaceMap(): void
    {
        $this->namespaceMap = [];
        foreach (glob($this->pluginsDirectory . '/*', GLOB_ONLYDIR) as $pluginDir) {
            $manifestFile = $pluginDir . '/plugin.json';
            if (file_exists($manifestFile)) {
                $manifest = json_decode(file_get_contents($manifestFile), true);
                if (isset($manifest['autoload']['psr-4'])) {
                    foreach ($manifest['autoload']['psr-4'] as $namespace => $path) {
                        $baseDir = realpath($pluginDir . '/' . $path);
                        if ($baseDir) {
                            $this->namespaceMap[$namespace] = $baseDir . '/';
                        }
                    }
                }
            } else {
                // Default to plugin directory name as namespace and 'src/' as base directory
                $pluginName = basename($pluginDir);
                $namespace = $pluginName . '\\';
                $baseDir = realpath($pluginDir . '/src');
                if ($baseDir) {
                    $this->namespaceMap[$namespace] = $baseDir . '/';
                }
            }
        }
    }

    private function writeCache(): void
    {
        $exported = var_export($this->namespaceMap, true);
        $content = "<?php\nreturn $exported;\n";
        file_put_contents($this->cacheFile, $content, LOCK_EX);
    }

    private function registerAutoloader(): void
    {
        spl_autoload_register(function (string $class): void {
            foreach ($this->namespaceMap as $prefix => $baseDir) {
                if (strpos($class, $prefix) === 0) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                    if (file_exists($file)) {
                        require $file;
                    }
                    return;
                }
            }
        });
    }
}

// Initialize the plugin autoloader
// new PluginAutoloader();
