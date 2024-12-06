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