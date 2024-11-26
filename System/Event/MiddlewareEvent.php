<?php
// System/Event/MiddlewareEvent.php

declare(strict_types=1);

namespace Kraut\Event;

use Symfony\Contracts\EventDispatcher\Event;
// TODO remove middleware copy queue and state it is a current snapshot of the middleware queue
class MiddlewareEvent extends Event
{
    private array $middlewareQueue;

    public function __construct(array $middlewareQueue)
    {
        $this->middlewareQueue = $middlewareQueue;
    }

    public function getMiddlewareQueue(): array
    {
        return $this->middlewareQueue;
    }

    public function setMiddlewareQueue(array $middlewareQueue): void
    {
        $this->middlewareQueue = $middlewareQueue;
    }

    /**
     * Insert a middleware before a specific middleware in the queue.
     */
    public function insertBefore(string $targetMiddleware, string $newMiddleware): void
    {
        $index = array_search($targetMiddleware, $this->middlewareQueue, true);

        if ($index !== false) {
            array_splice($this->middlewareQueue, $index, 0, [$newMiddleware]);
        } else {
            // If the target middleware is not found, append at the end
            $this->middlewareQueue[] = $newMiddleware;
        }
    }

    /**
     * Insert a middleware after a specific middleware in the queue.
     */
    public function insertAfter(string $targetMiddleware, string $newMiddleware): void
    {
        $index = array_search($targetMiddleware, $this->middlewareQueue, true);

        if ($index !== false) {
            array_splice($this->middlewareQueue, $index + 1, 0, [$newMiddleware]);
        } else {
            // If the target middleware is not found, append at the end
            $this->middlewareQueue[] = $newMiddleware;
        }
    }
}
?>