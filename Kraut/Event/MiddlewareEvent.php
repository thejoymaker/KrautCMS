<?php
// System/Event/MiddlewareEvent.php

declare(strict_types=1);

namespace Kraut\Event;

use Symfony\Contracts\EventDispatcher\Event;
// TODO remove middleware copy queue and state it is a current snapshot of the middleware queue
class MiddlewareEvent extends Event
{
    private array $middlewareQueue;
    private array $middlewareToBeAdded = [];

    public function __construct(array $middlewareQueue)
    {
        $this->middlewareQueue = $middlewareQueue;
    }


    /**
     * Post-processes the middleware queue by adding new middleware in relation to existing ones.
     *
     * This method loops through the post-processing queue and adds new middleware based on their
     * specified relation ('before' or 'after') to existing middleware. It continues to loop until
     * no more new middleware can be added or the maximum number of iterations is reached.
     *
     * @return void
     */
    public function postProcess(): void
    {
        $maxRepeat = count($this->middlewareToBeAdded);
        $numberAdded = 0;
        // loop through the postprocessing queue as long as there are new middleware to add
        for ($i = 0; $i < $maxRepeat; $i++) {
            foreach ($this->middlewareToBeAdded as $newMiddleware => $targetMiddleware) {
                foreach ($targetMiddleware as $middleware => $relation) {
                    // check if the new middleware is not already present in 
                    // the queue and the target middleware is present
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
            if ($numberAdded === $maxRepeat) {
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
            $this->middlewareToBeAdded[$newMiddleware][$targetMiddleware] ='before';
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
            $this->middlewareToBeAdded[$newMiddleware][$targetMiddleware] = 'after';
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