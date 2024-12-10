<?php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Util\RequestBodyParserUtil;
use Nyholm\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RequestBodyParserMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getMethod() !== 'POST') {
            return $handler->handle($request);
        }

        $request = RequestBodyParserUtil::parseRequestBody($request, $this->logger); 

        return $handler->handle($request);
    }
}
?>