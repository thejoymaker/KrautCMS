<?php


declare(strict_types=1);


namespace Kraut\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;

class ResponseUtil
{
    public static function redirectTemporary(Environment $twig, string $route): ResponseInterface
    {
        return new Response(302, ['Location' => $route]);
    }
    
    public static function respond(Environment $twig, string $template, array $parameters): ResponseInterface
    {
        $content = $twig->render("{$template}.html.twig", $parameters);
        $response = new Response();
        $response->getBody()->write($content);
        return $response;
    }
    
    public static function respondRelative(Environment $twig, string $namespace, string $template, array $parameters): ResponseInterface
    {
        $content = $twig->render("@{$namespace}/{$template}.html.twig", $parameters);
        $response = new Response();
        $response->getBody()->write($content);
        return $response;
    }
}

?>