<?php

// Kraut/Model/ServerRequestContainer.php

declare(strict_types=1);

namespace Kraut\Model;

use Psr\Http\Message\ServerRequestInterface;

/**
 * The ServerRequestContainer class.
 * 
 * The ServerRequestContainer class is a container for the ServerRequestInterface.
 * 
 * The class is used to store the ServerRequestInterface in a container.
 */
class ServerRequestContainer 
{
    /**
     * The ServerRequestInterface.
     */
    private ServerRequestInterface $serverRequest;

    /**
     * The constructor of the ServerRequestContainer class.
     * 
     * @param ServerRequestInterface $serverRequest The ServerRequestInterface.
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * Get the ServerRequestInterface.
     * 
     * This method returns the ServerRequestInterface.
     * 
     * @return ServerRequestInterface The ServerRequestInterface.
     */
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    /**
     * Set the ServerRequestInterface.
     * 
     * This method sets the ServerRequestInterface.
     * 
     * @param ServerRequestInterface $serverRequest The ServerRequestInterface.
     */
    public function setServerRequest(ServerRequestInterface $serverRequest): void
    {
        $this->serverRequest = $serverRequest;
    }
}