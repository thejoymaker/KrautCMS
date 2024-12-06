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
            $pluginNamespace = 'User\\Plugin\\' . $pluginName;
            $pluginServicesDir = $pluginDirectory . '/Service';
            if (is_dir($pluginServicesDir)) {
                $pluginServices = glob($pluginServicesDir . '/*.php');
                foreach ($pluginServices as $serviceFileName) {
                    $serviceClassName = $pluginNamespace . '\\Service\\' . basename($serviceFileName, '.php');
                    // $containerBuilder->addDefinitions([
                    //     $serviceClassName => DI\autowire($serviceClassName),
                    // ]);
                    if(class_exists($serviceClassName)){
                        $interfaceNames = class_implements($serviceClassName);
                        if(is_array($interfaceNames)){
                            foreach($interfaceNames as $interfaceName){
                                if(strpos($interfaceName, 'ServiceInterface') !== false){
                                    $services[$interfaceName] = \DI\autowire($serviceClassName);
                                }
                            }
                        }
                        // $services[$serviceClassName] = \DI\autowire($serviceClassName);
                    }
                }
            }
        }
        return $services;
    }

    public static function discoverPluginServices(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinitions(self::discoverServices());
    }
}

?>