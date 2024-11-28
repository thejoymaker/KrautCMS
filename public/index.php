<?php
// public/index.php

declare(strict_types=1);

// Enable error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Kraut\Kernel;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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
    $kernel = new Kernel();
    $response = $kernel->handle(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI']
    );

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
?>