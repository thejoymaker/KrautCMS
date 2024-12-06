<?php

declare(strict_types=1);

namespace Kraut\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RequestBodyParserMiddleware implements MiddlewareInterface
{
    private const SUPPORTED_CONTENT_TYPES = [
        'application/x-www-form-urlencoded' => 'parseFormData',
        'application/json' => 'parseJsonData',
        'multipart/form-data' => 'parseMultipartData'
    ];

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
        // var_dump($request);
        // die();
        // $contentType = $this->getBaseContentType($request->getHeaderLine('Content-Type'));
        // $this->logger->info("Parsing request {$request->getBody()} as {$contentType}");
        // if (isset(self::SUPPORTED_CONTENT_TYPES[$contentType])) {
        //     $parser = self::SUPPORTED_CONTENT_TYPES[$contentType];
        //     try {
        //         $request = $this->{$parser}($request);
        //     } catch (\Throwable $e) {
        //         $this->logger->error("Failed to parse {$contentType} request body: " . $e->getMessage());
        //         throw new \RuntimeException(
        //             "Failed to parse {$contentType} request body: " . $e->getMessage()
        //         );
        //     }
        // }

        // return $handler->handle($request);


        // Default to form-urlencoded if no content type
        $contentType = $request->getHeaderLine('Content-Type');
        if (empty($contentType)) {
            $contentType = 'application/x-www-form-urlencoded';
        }

        $baseContentType = $this->getBaseContentType($contentType);
        
        if (isset(self::SUPPORTED_CONTENT_TYPES[$baseContentType])) {
            $parser = self::SUPPORTED_CONTENT_TYPES[$baseContentType];
            try {
                // Get raw input
                $rawInput = file_get_contents('php://input');
                
                // Create new stream with raw input
                $stream = fopen('php://temp', 'r+');
                fwrite($stream, $rawInput);
                rewind($stream);
                
                // Update request with stream
                $request = $request->withBody(new \Nyholm\Psr7\Stream($stream));
                
                // Parse body
                $request = $this->{$parser}($request);
                
                $this->logger->info('Parsed request body', [
                    'content_type' => $baseContentType,
                    'parsed_data' => $request->getParsedBody()
                ]);
            } catch (\Throwable $e) {
                $this->logger->error("Failed to parse request body", [
                    'content_type' => $baseContentType,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $handler->handle($request);
    }

    private function parseFormData(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = (string)$request->getBody();
        if (empty($body)) {
            $body = file_get_contents('php://input');
        }
        
        parse_str($body, $data);
        return $request->withParsedBody($data);
        // parse_str((string) $request->getBody(), $data);
        // return $request->withParsedBody($data);
    }

    private function parseJsonData(ServerRequestInterface $request): ServerRequestInterface
    {
        $data = json_decode((string) $request->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                'Invalid JSON data: ' . json_last_error_msg()
            );
        }

        return $request->withParsedBody($data);
    }

    private function parseMultipartData(ServerRequestInterface $request): ServerRequestInterface
    {
        // Already handled by PHP for multipart/form-data
        return $request->withParsedBody($_POST);
    }

    private function getBaseContentType(string $contentType): string
    {
        $this->logger->info("Parsing request body as {$contentType}");
        // Extract base content type without parameters
        if ($pos = strpos($contentType, ';')) {
            return trim(substr($contentType, 0, $pos));
        }
        return trim($contentType);
    }
}
?>