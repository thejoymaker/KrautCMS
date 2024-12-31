<?php
// User/Plugin/PagesPlugin/Controller/AssetController.php

namespace User\Plugin\PagesPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Service\ConfigurationService;
use Kraut\Util\AssetsUtil;
use Kraut\Util\FileSystemUtil;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Container\ContainerInterface;

#[Controller]
class AssetController
{
    public function __construct(private ContainerInterface $container)
    {
    }

    // #[Route(path: '/favicon.ico', methods: ['GET'])]
    // public function favicon(ServerRequestInterface $request, array $args): ResponseInterface
    // {
    //     return $this->serve($request, ['path' => 'favicon.ico']);
    // }

    #[Route(path: AssetsUtil::ASSETS_ROUTE_PATTERN, methods: ['GET'])]
    public function serve(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var ConfigurationService $configService */
        return ResponseUtil::openAssetStream($this->container, $args['path']);
    }
}