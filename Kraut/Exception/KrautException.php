<?php
// Kraut/Exception/SecurityException.php

declare(strict_types=1);

namespace Kraut\Exception;

use Exception;
use Kraut\Model\UserInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class KrautException extends Exception
{
    const CONTROLLER_ERROR = 1;

    const ACCESS_DENIED = 403;
    const NOT_FOUND = 404;
    const INTERNAL_SERVER_ERROR = 500;

    public static function controllerError(ServerRequestInterface $request, array $context = []): self
    {
        return new self('Controller Error', self::CONTROLLER_ERROR, null, $request, $context);
    }

    public static function accessDenied(ServerRequestInterface $request): self
    {
        return new self('Access denied', self::ACCESS_DENIED, null, $request);
    }

    public static function notFound(ServerRequestInterface $request): self
    {
        return new self('Not found', self::NOT_FOUND, null, $request);
    }

    public static function internalServerError(ServerRequestInterface $request): self
    {
        return new self('Internal server error', self::INTERNAL_SERVER_ERROR, null, $request);
    }

    public function __construct(
        string $message = 'Unspecified exception', 
        int $code = 1, 
        Throwable|null $previous = null, 
        private ServerRequestInterface|null $request = null,
        private array $context = [])
    {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getUser(): ?UserInterface
    {
        return $this->request?->getAttribute('current_user');
    }
}