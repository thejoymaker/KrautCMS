<?php

declare(strict_types=1);

namespace Kraut\Util;

use DI\ContainerBuilder;

class ServiceUtil
{
    public static function discoverServices(): array
    {
        $services = [];
        $pluginDir = __DIR__ . '/../../User/Plugin';
        $pluginDirectories = glob($pluginDir . '/*', GLOB_ONLYDIR);
        foreach ($pluginDirectories as $pluginDirectory) {
            $pluginName = basename($pluginDirectory);
            $pluginConfig = __DIR__ . '/../../User/Config/' . $pluginName . '.json';
            if (file_exists($pluginConfig)) {
                $pluginConfigData = json_decode(file_get_contents($pluginConfig), true);
                $lcPluginName = strtolower($pluginName);
                $enabled = isset($pluginConfigData[$lcPluginName]['active'])
                    && $pluginConfigData[$lcPluginName]['active'] === true;
                if (!$enabled) {
                    continue;
                }
            } else {
                continue;
            }
            $pluginNamespace = 'User\\Plugin\\' . $pluginName;
            $pluginServicesDir = $pluginDirectory . '/Service';
            if (is_dir($pluginServicesDir)) {
                $pluginServices = glob($pluginServicesDir . '/*.php');
                foreach ($pluginServices as $serviceFileName) {
                    $serviceClassName = $pluginNamespace . '\\Service\\' . basename($serviceFileName, '.php');
                    if (class_exists($serviceClassName)) {
                        $interfaceNames = class_implements($serviceClassName);
                        if (is_array($interfaceNames)) {
                            foreach ($interfaceNames as $interfaceName) {
                                if (strpos($interfaceName, 'ServiceInterface') !== false) {
                                    $services[$interfaceName] = $serviceClassName;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $services;
    }

    public static function discoverPluginServices(ContainerBuilder $containerBuilder)
    {
        $file = __DIR__ . '/../../Cache/System/services.php';
        $loader = [ServiceUtil::class, 'discoverServices'];
        $resource = __DIR__ . '/../../User/Plugin';
        $enabled = $_ENV['CACHE_ENABLED'] === 'true';
        $invalidator = null;
        $logger = null;
        $definitionClasses = CacheUtil::loadCached($file, $loader, $resource, $enabled, $invalidator, $logger);
        $definitions = [];
        foreach ($definitionClasses as $interfaceName => $className) {
            $definitions[$interfaceName] = \DI\autowire($className);
        }
        $containerBuilder->addDefinitions($definitions);
    }
}
