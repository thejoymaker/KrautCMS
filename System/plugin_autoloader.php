<?php
// plugins_autoloader.php

class PluginAutoloader
{
    private $pluginsDirectory;
    private $cacheFile;
    private $classMap = [];
    private $cacheEnabled = true;

    public function __construct($pluginsDirectory = null, $cacheEnabled = true)
    {
        $this->pluginsDirectory = $pluginsDirectory ?: __DIR__ . '/plugins';
        $this->cacheFile = sys_get_temp_dir() . '/plugin_classmap.php';
        $this->cacheEnabled = $cacheEnabled;

        $this->initialize();
    }

    private function initialize()
    {
        if ($this->cacheEnabled && $this->isCacheValid()) {
            $this->loadClassMapFromCache();
        } else {
            $this->buildClassMap();
            if ($this->cacheEnabled) {
                $this->writeCache();
            }
        }

        $this->registerAutoloader();
    }

    private function isCacheValid()
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $cacheMTime = filemtime($this->cacheFile);
        $pluginsMTime = $this->getPluginsModificationTime();

        return $cacheMTime >= $pluginsMTime;
    }

    private function getPluginsModificationTime()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->pluginsDirectory, FilesystemIterator::SKIP_DOTS)
        );

        $maxMTime = 0;
        foreach ($iterator as $file) {
            $mtime = $file->getMTime();
            if ($mtime > $maxMTime) {
                $maxMTime = $mtime;
            }
        }

        return $maxMTime;
    }

    private function loadClassMapFromCache()
    {
        $this->classMap = require $this->cacheFile;
    }

    private function buildClassMap()
    {
        $this->classMap = [];
        $directoryIterator = new RecursiveDirectoryIterator(
            $this->pluginsDirectory,
            FilesystemIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $classes = $this->getClassesFromFile($file->getPathname());
                foreach ($classes as $class) {
                    $this->classMap[$class] = $file->getPathname();
                }
            }
        }
    }

    private function getClassesFromFile($file)
    {
        $contents = file_get_contents($file);
        $tokens = token_get_all($contents);

        $namespace = '';
        $classes = [];
        $i = 0;

        while ($i < count($tokens)) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                $i += 2; // Skip namespace keyword and whitespace
                while ($tokens[$i][0] !== T_WHITESPACE && $tokens[$i] !== ';') {
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                    $i++;
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                $i += 2; // Skip class keyword and whitespace
                $className = $tokens[$i][1];
                $fullClassName = ($namespace ? $namespace . '\\' : '') . $className;
                $classes[] = $fullClassName;
            }

            $i++;
        }

        return $classes;
    }

    private function writeCache()
    {
        $exported = var_export($this->classMap, true);
        $content = "<?php\nreturn $exported;\n";
        file_put_contents($this->cacheFile, $content, LOCK_EX);
    }

    private function registerAutoloader()
    {
        spl_autoload_register(function ($class) {
            if (isset($this->classMap[$class])) {
                require $this->classMap[$class];
            }
        });
    }
}

// Initialize the plugin autoloader
// new PluginAutoloader();
?>