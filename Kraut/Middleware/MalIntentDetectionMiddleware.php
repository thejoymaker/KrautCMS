<?php
// System/Middleware/MalIntentDetectionMiddleware.php

declare(strict_types=1);

namespace Kraut\Middleware;

use Kraut\Util\RequestBodyParserUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Response;

class MalIntentDetectionMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    // List of regex patterns to detect malicious inputs
    private array $maliciousPatterns = [
        // SQL Injection patterns
        '/(\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bunion\b|--|#)/i',
        // XSS patterns
        '/(<script\b[^>]*>|<\/script>|javascript:|on\w+=)/i',
        // Command Injection patterns
        '/(;|\|\||&&)/',
        // Path Traversal patterns
        '/(\.\.\/|\.\.\\\\)/',
        // Template Injection patterns
        '/(\{\{.*\}\}|\{\%.*\%\})/',
        // XML Injection
        '/(<!DOCTYPE|\<\?xml)/i',
        // LDAP Injection
        '/(\&\&|\|\|)/',
        // Serialization attacks
        '/(O:\d+:"[^"]+":\d+:\{.*\})/',
        // Additional patterns as needed
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->detectMaliciousIntent($request)) {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            $this->logger->warning('Malicious request detected from IP: ' . $ipAddress);

            // Return a generic error response to the client
            return new Response(400, [], 'Bad Request');
        }

        // Continue processing the request
        return $handler->handle($request);
    }

    private function detectMaliciousIntent(ServerRequestInterface $request): bool
    {
        $dataToCheck = [];

        // Collect query parameters
        $queryParams = $request->getQueryParams();
        $dataToCheck = array_merge($dataToCheck, $this->flattenData($queryParams));

        // Collect raw body data
        $body = (string) $request->getBody();
        // $contentType = $request->getHeaderLine('Content-Type');
        // $parsedBody = $this->parseRawBody($body, $contentType);
        $request = RequestBodyParserUtil::parseRequestBody($request, $this->logger);
        $dataToCheck = array_merge($dataToCheck, $this->flattenData($request->getParsedBody()));

        // Collect headers
        $headers = $request->getHeaders();
        $dataToCheck = array_merge($dataToCheck, $this->flattenData($headers));

        // Collect URI path
        $dataToCheck[] = $request->getUri()->getPath();

        // Collect uploaded file names
        $uploadedFiles = $request->getUploadedFiles();
        $dataToCheck = array_merge($dataToCheck, $this->getUploadedFileNames($uploadedFiles));

        // Check each piece of data against malicious patterns
        foreach ($dataToCheck as $data) {
            foreach ($this->maliciousPatterns as $pattern) {
                if (preg_match($pattern, (string) $data)) {
                    return true;
                }
            }
        }

        return false;
    }

    // private function parseRawBody(string $body, string $contentType): array
    // {
    //     $parsedBody = [];

    //     if(empty($contentType)) {
    //         $contentType = 'application/x-www-form-urlencoded';
    //     }

    //     // Extract base content type without charset or boundary
    //     $semicolonPosition = strpos($contentType, ';');
    //     if ($semicolonPosition !== false) {
    //         $baseContentType = substr($contentType, 0, $semicolonPosition);
    //     } else {
    //         $baseContentType = $contentType;
    //     }

    //     switch (trim($baseContentType)) {
    //         case 'application/json':
    //             $parsedBody = json_decode($body, true) ?? [];
    //             break;
    //         case 'application/x-www-form-urlencoded':
    //             parse_str($body, $parsedBody);
    //             break;
    //         case 'multipart/form-data':
    //             // For multipart/form-data, parsing is complex
    //             // You might need to use a library or skip parsing here
    //             $parsedBody = []; // Skipping parsing for simplicity
    //             break;
    //         default:
    //             // Other content types can be handled here
    //             $parsedBody = [];
    //             break;
    //     }

    //     return $parsedBody;
    // }

    private function flattenData($data): array
    {
        $result = [];

        if (is_array($data)) {
            array_walk_recursive($data, function ($value) use (&$result) {
                if (is_scalar($value)) {
                    $result[] = $value;
                }
            });
        } elseif (is_scalar($data)) {
            $result[] = $data;
        }

        return $result;
    }

    private function getUploadedFileNames(array $uploadedFiles): array
    {
        $fileNames = [];

        array_walk_recursive($uploadedFiles, function ($file) use (&$fileNames) {
            if ($file instanceof UploadedFileInterface) {
                $fileNames[] = $file->getClientFilename();
            }
        });

        return $fileNames;
    }
}