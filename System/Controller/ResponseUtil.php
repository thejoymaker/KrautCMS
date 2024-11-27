<?php

declare(strict_types=1);

namespace Kraut\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;

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
        $response = new Response();
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
    public static function respondRelative(Environment $twig, string $namespace, string $template, array $parameters = []): ResponseInterface
    {
        $content = $twig->render("@{$namespace}/{$template}.html.twig", $parameters);
        $response = new Response();
        $response->getBody()->write($content);
        return $response;
    }
}

?>