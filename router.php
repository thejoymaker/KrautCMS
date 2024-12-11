<?php
// router.php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$requestedFile = __DIR__ . '/public' . $uri;

// If the requested file exists, serve it directly
if ($uri !== '/' && file_exists($requestedFile)) {
    return false;
}

// Otherwise, route the request to your application's front controller
require_once __DIR__ . '/public/index.php';