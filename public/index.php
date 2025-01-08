<?php
// public/index.php

declare(strict_types=1);
function enableErrorReporting()
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require __DIR__ . '/../vendor/autoload.php';

use Kraut\Kernel;
use Dotenv\Dotenv;

try {
    // Load environment variables from .env file
    function initializeDotenv()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }
    initializeDotenv();
    if (isset($_ENV['APP_DEBUG']) === false) {
        echo "Failed to load environment variables.";
        die();
    }
    if ($_ENV['APP_DEBUG'] === 'true') {
        enableErrorReporting();
    }
    /**
     * Main entry point for the application.
     *
     * This function initializes the application kernel, handles the incoming HTTP request,
     * and sends the appropriate HTTP response.
     *
     * @return void
     */
    function main()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        // Normalize the URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rawurldecode($uri);

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        // Create the application kernel
        $kernel = new Kernel($method, $uri);
        // Handle the request
        $response = $kernel->handle();
        // Send HTTP headers and response
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        echo $response->getBody();
    }
    main();
} catch (Throwable $e) {
    echo "No sir, I don't like it.";
    echo $e->getMessage();  
    // TODO log...
    die();
}
