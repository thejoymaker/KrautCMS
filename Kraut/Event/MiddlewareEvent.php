<?php
// System/Event/MiddlewareEvent.php

declare(strict_types=1);

namespace Kraut\Event;

use Symfony\Contracts\EventDispatcher\Event;
// TODO remove middleware copy queue and state it is a current snapshot of the middleware queue
class MiddlewareEvent extends Event
{
    private array $middlewareQueue;
    private array $middlewareAdded = [];

    public function __construct(array $middlewareQueue)
    {
        $this->middlewareQueue = $middlewareQueue;
    }

    public function postProcess(): void
    {
        $maxRepeat = sizeof($this->middlewareAdded);
        $numberAdded = 0;
        for ($i = 0; $i < $maxRepeat; $i++) {
            foreach ($this->middlewareAdded as $newMiddleware => $targetMiddleware) {
                foreach ($targetMiddleware as $middleware => $relation) {
                    if(!$this->isPresent($newMiddleware) && $this->isPresent($middleware)) {
                        switch ($relation) {
                            case 'before':
                                $this->insertBefore($middleware, $newMiddleware);
                                break;
                            case 'after':
                                $this->insertAfter($middleware, $newMiddleware);
                                break;
                        }
                        $numberAdded++;
                    }
                }
            }
            if($numberAdded === $maxRepeat) {
                break;
            }
        }

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
            // If the target middleware is not found, append to the postprocessing queue
            $this->middlewareAdded[$newMiddleware][$targetMiddleware] ='before';
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
            // If the target middleware is not found, append to the postprocessing queue
            $this->middlewareAdded[$newMiddleware][$targetMiddleware] = 'after';
        }
    }

    /**
     * Check if a middleware is present in the queue.
     */
    public function isPresent(string $middleware): bool
    {
        return in_array($middleware, $this->middlewareQueue, true);
    }
}
?>