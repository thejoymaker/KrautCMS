<?php
// System/Middleware/CsrfMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Util\CsrfTokenUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Nyholm\Psr7\Stream;

class CsrfMiddleware implements MiddlewareInterface
{
    private string $csrfTokenKey = 'csrf_token';

    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Generate or retrieve the CSRF token from the session
        $session = $request->getAttribute('session');
        if (!$session) {
            throw new \RuntimeException('Session is required for CSRF protection.');
        }

        // Generate or retrieve the CSRF token
        $csrfToken = CsrfTokenUtil::generateToken();

        // Pass the CSRF token to the request attributes for use in controllers/templates
        $request = $request->withAttribute($this->csrfTokenKey, $csrfToken);

        // Process the request and get the response
        $response = $handler->handle($request);

        // Only modify HTML responses
        if ($this->isHtmlResponse($response)) {
            $response = $this->injectCsrfToken($response, $csrfToken);
        }

        return $response;
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        $contentType = strtolower($response->getHeaderLine('Content-Type'));
        return strpos($contentType, 'text/html') !== false;
    }

    private function injectCsrfToken(ResponseInterface $response, string $csrfToken): ResponseInterface
    {
        $body = (string) $response->getBody();

        // Use DOMDocument to safely parse and manipulate HTML
        $dom = new \DOMDocument();
        @$dom->loadHTML($body, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Inject CSRF token in head as meta tag
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head) {
            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'csrf-token');
            $meta->setAttribute('content', htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'));
            $head->appendChild($meta);
        }
        
        // Find all <form> elements in the document
        $forms = $dom->getElementsByTagName('form');

        foreach ($forms as $form) {
            // Create a new hidden input element
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', $this->csrfTokenKey);
            $input->setAttribute('value', $csrfToken);

            // Append the hidden input to the form
            $form->appendChild($input);
        }

        // Get the updated HTML
        $html = $dom->saveHTML();

        // Create a new response body
        $stream = Stream::create($html);

        // Return a new response with the updated body
        return $response->withBody($stream);
    }
}
?>