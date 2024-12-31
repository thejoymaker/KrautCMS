<?php

declare(strict_types=1);

namespace Kraut\Util;

use Kraut\Service\CacheService;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Nyholm\Psr7\Stream;

/**
 * Class ResponseUtil
 *
 * Utility class for generating HTTP responses, including redirects
 * and rendering templates using the Twig templating engine.
 */
class ResponseUtil
{
    /**
     * Generates a temporary redirect response to the specified route.
     *
     * @param string $route The URL or route path to redirect to.
     * @return ResponseInterface A response object with a 302 status code and Location header.
     */
    public static function redirectTemporary(string $route): ResponseInterface
    {
        return new Response(302, ['Location' => $route]);
    }

    /**
     * Generates a permanent redirect response to the specified route.
     *
     * @param string $route The URL or route path to redirect to.
     * @return ResponseInterface A response object with a 301 status code and Location header.
     */
    public static function redirectPermanent(string $route): ResponseInterface
    {
        return new Response(301, ['Location' => $route]);
    }

    /**
     * Renders a Twig template and returns a response with the rendered content.
     *
     * @param Environment $twig      The Twig environment instance.
     * @param string      $template  The name of the template to render (without extension).
     * @param array       $parameters Optional associative array of parameters to pass to the template.
     * @return ResponseInterface A response object containing the rendered HTML content.
     */
    public static function respond(Environment $twig, string $template, array $parameters = []): ResponseInterface
    {
        $content = $twig->render("{$template}.html.twig", $parameters);
        $response = new Response(200, ['Content-Type' => 'text/html; charset=UTF-8']);
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * Renders a Twig template from a namespaced path and returns a response.
     *
     * @param Environment $twig       The Twig environment instance.
     * @param string      $namespace  The namespace alias for the template path.
     * @param string      $template   The name of the template to render (without extension).
     * @param array       $parameters Optional associative array of parameters to pass to the template.
     * @return ResponseInterface A response object containing the rendered HTML content.
     */
    public static function respondRelative(Environment $twig, string $pluginName, string $templateName, array $data = [], int $code = 200): ResponseInterface
    {
        $templatePath = "@{$pluginName}/{$templateName}.html.twig";
        try {
            $html = $twig->render($templatePath, $data);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error rendering template: {$templatePath}", 0, $e);
        }
        // $html = $twig->render($templatePath, $data);
        return new Response(
            $code,
            ['Content-Type' => 'text/html; charset=UTF-8'],
            $html
        );
    }

    /**
     * Generates a (404 Not Found) error response.
     *
     * @param Environment $twig    The Twig environment instance.
     * @param string      $plugin  The name of the plugin or module generating the error.
     * @param string      $message The error message to display.
     * @return ResponseInterface A response object with a 404 status code and error message.
     */
    public static function respondNegative(Environment $twig, $code = 404): ResponseInterface {
        $content = $twig->render('negative.html.twig');
        $response = new Response($code, ['Content-Type' => 'text/html; charset=UTF-8']);
        $response->getBody()->write($content);
        return $response;
    }

    public static function respondError(\Throwable $e, ContainerInterface $container): ResponseInterface
    {
        $twig = $container->get(Environment::class);
        $content = $twig->render('error.html.twig', ['error' => $e->getMessage()]);
        $response = new Response(500, ['Content-Type' => 'text/html; charset=UTF-8'], $content);
        return $response;
    }

    public static function respondErrorDetailed(\Throwable $e, ContainerInterface $container): ResponseInterface
    {
        $twig = $container->get(Environment::class);
        $trace = $e->getTrace();
        $content = $twig->render('error.html.twig', [
            'error' => $e->getMessage(),
            'stack_trace' => $trace
        ]);
        $response = new Response(500, ['Content-Type' => 'text/html; charset=UTF-8'], $content);
        return $response;
    }

    public static function respondRequirementsError(ContainerInterface $container, array $missingModules, string $requirementsMessage = null): ResponseInterface
    {
        $twig = $container->get(Environment::class);
        $content = $twig->render('requirements-error.html.twig', [
            'requirements_message' => $requirementsMessage,
            'missing_modules' => $missingModules
        ]);
        $response = new Response(500, ['Content-Type' => 'text/html; charset=UTF-8'], $content);
        return $response;
    }

    public static function respondPlainText(string $content, int $status = 200): ResponseInterface
    {
        $response = new Response($status, ['Content-Type' => 'text/plain']);
        $response->getBody()->write($content);
        return $response;
    }

    public static function openAssetStream(ContainerInterface $container, string $path): ResponseInterface
    {
        $configurationService = $container->get(ConfigurationService::class);
        $pluginService = $container->get(PluginService::class);
        $fullPath = AssetsUtil::locateAsset(
            $path, $configurationService, $pluginService);
        if ($fullPath === null) {
            return new Response(404, ['Content-Type' => 'text/plain'], 'Asset not found');
        }
        // Read the file contents
        $stream = Stream::create(fopen($fullPath, 'rb'));
        // Get the mime type of the asset
        $mimeType = self::getMimeType($fullPath);
        // Return the response with the appropriate headers
        return new Response(200, [
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($fullPath),
            'Cache-Control' => 'public, max-age=1' // Adjust caching as needed
        ], $stream);
    }

    private static function getMimeType(string $filePath): string
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
?>