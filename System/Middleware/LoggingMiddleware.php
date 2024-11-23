<?php
// System/Middleware/LoggingMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    // The container will inject LoggerInterface automatically
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('Incoming request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
        ]);

        return $handler->handle($request);
    }
}
?>