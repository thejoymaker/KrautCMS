<?php
// User/Plugin/PagesPlugin/Controller/AssetController.php

namespace User\Plugin\PagesPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Service\ConfigurationService;
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

    #[Route(path: '/assets/{path:.+}', methods: ['GET'])]
    public function serve(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var ConfigurationService $configService */
        $configService = $this->container->get(ConfigurationService::class);
        $theme = $configService->get(ConfigurationService::THEME_NAME, "default");

        $assetPath = $args['path'] ?? '';

        // Sanitize the asset path to prevent directory traversal
        $assetPath = str_replace(['..', './', '\\'], '', $assetPath);

        // Build the full path to the asset
        $fullPath = __DIR__ . '/../../../Theme/' . $theme . '/assets/' . $assetPath;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $pluginsDir = __DIR__ . '/../../';
            $allPluginDirs = array_filter(glob($pluginsDir . '*'), 'is_dir');
            foreach ($allPluginDirs as $pluginDir) {
                $pluginName = basename($pluginDir);
                $assetsDir = $pluginsDir . $pluginName . '/View/assets/';
                $fullPath = $assetsDir . $assetPath;
                if (file_exists($fullPath) && is_file($fullPath)) {
                    break;
                }
            }
            // $pluginName = '';
            // $assetsDir = $pluginDir . $pluginName . '/View/assets/';
        }

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return new Response(404, [], 'Asset not found');
        }

        // Get the mime type of the asset
        $mimeType = $this->getMimeType($fullPath);

        // Read the file contents
        $stream = Stream::create(fopen($fullPath, 'rb'));

        // Return the response with the appropriate headers
        return new Response(200, [
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($fullPath),
            'Cache-Control' => 'public, max-age=1' // Adjust caching as needed
        ], $stream);
    }

    private function getMimeType(string $filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            // Add more extensions and mime types as needed
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}