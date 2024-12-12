<?php
// Kraut/Util/RequestBodyParserUtil.php

declare(strict_types=1);

namespace Kraut\Util;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestBodyParserUtil
{
    private const SUPPORTED_CONTENT_TYPES = [
        'application/x-www-form-urlencoded' => 'parseFormData',
        'application/json' => 'parseJsonData',
        'multipart/form-data' => 'parseMultipartData'
    ];

    public static function parseRequestBody(ServerRequestInterface $request, ?LoggerInterface $logger) : ServerRequestInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $baseContentType = self::getBaseContentType($contentType);
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
                $request = self::{$parser}($request);
                
                $logger?->info('Parsed request body', [
                    'content_type' => $baseContentType,
                    'parsed_data' => $request->getParsedBody()
                ]);
            } catch (\Throwable $e) {
                $logger?->error("Failed to parse request body", [
                    'content_type' => $baseContentType,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        return $request;
    }

    private static function parseFormData(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = (string)$request->getBody();
        if (empty($body)) {
            $body = file_get_contents('php://input');
        }
        
        parse_str($body, $data);
        return $request->withParsedBody($data);
    }

    private static function parseJsonData(ServerRequestInterface $request): ServerRequestInterface
    {
        $data = json_decode((string) $request->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                'Invalid JSON data: ' . json_last_error_msg()
            );
        }
        return $request->withParsedBody($data);
    }

    private static function parseMultipartData(ServerRequestInterface $request): ServerRequestInterface
    {
        // Already handled by PHP for multipart/form-data
        return $request->withParsedBody($_POST);
    }

    public static function getBaseContentType(string $contentType): string
    {
        if (empty($contentType)) {
            $contentType = 'application/x-www-form-urlencoded';
        }
        if ($pos = strpos($contentType, ';')) {
            return trim(substr($contentType, 0, $pos));
        }
        return trim($contentType);
    }
}

?>