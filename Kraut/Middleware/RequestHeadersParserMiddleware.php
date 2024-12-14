<?php
// Kraut/Middleware/RequestHeadersParserMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Psr\Http\Server\MiddlewareInterface;

class RequestHeadersParserMiddleware implements MiddlewareInterface
{
    public function __construct(private \Psr\Log\LoggerInterface $logger)
    {
    }

    public function process(
        \Psr\Http\Message\ServerRequestInterface $request, 
        \Psr\Http\Server\RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $this->logger->info('Request headers: ' . json_encode($request->getHeaders()));
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $request = $request->withHeader('Content-Type', $_SERVER['CONTENT_TYPE']);
            $this->logger->info('Content-Type: ' . $_SERVER['CONTENT_TYPE']);
        }
        return $handler->handle($request);
    }
}
?>