<?php

declare(strict_types=1);

namespace Kraut\Model;

/**
 * Class PluginInfo
 *
 * This class is part of the KrautCMS system and is responsible for handling
 * information related to plugins. It provides methods to retrieve and manage
 * plugin metadata, such as name, manifest, and other relevant details.
 *
 * @package Kraut\Model
 */
class PluginInfo
{
    /**
     * from config
     */
    private bool $active;
    /**
     * from config
     */
    private Manifest $manifest;
    /**
     * from controllers (this plugin)
     */
    private ?RouteModel $routeModel;
    /**
     * Plugin class name
     */
    private string $className;
    /**
     * Plugin base dir
     */
    private string $path;
    /**
     * View directory
     */
    private ?string $views;
    /**
     * Controller directory
     */
    private ?string $controllers;
    /**
     * Config file path
     */
    private string $configPath;


    public static function __set_state($array) : PluginInfo {
        return new self(
            $array['className'],
            $array['active'],
            $array['path'],
            $array['manifest'],
            $array['views'],
            $array['controllers'],
            $array['routeModel'],
            $array['configPath']
        );
    }

    public function __construct(
        string $className,
        bool $active,
        string $path,
        Manifest $manifest,
        ?string $views,
        ?string $controllers,
        ?RouteModel $routeModel,
        string $configPath
    ) {
        $this->className = $className;
        $this->active = $active;
        $this->path = $path;
        $this->manifest = $manifest;
        $this->views = $views;
        $this->controllers = $controllers;
        $this->routeModel = $routeModel;
        $this->configPath = $configPath;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getViews(): ?string
    {
        return $this->views;
    }

    public function getControllers(): ?string
    {
        return $this->controllers;
    }

    public function getRouteModel(): ?RouteModel
    {
        return $this->routeModel;
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    public function getPluginName(){
        return basename($this->path);
    }
}

?>