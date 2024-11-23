<?php
// User/Plugin/WelcomePlugin/WelcomePlugin.php

declare(strict_types=1);

namespace User\Plugin\WelcomePlugin;

use Kraut\Plugin\PluginInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Psr\Log\LoggerInterface;
use Kraut\Event\ResponseEvent;

class WelcomePlugin implements PluginInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }

    public function activate(): void
    {
        $this->logger->info('WelcomePlugin activated');
    }

    public function deactivate(): void
    {
        $this->logger->info('WelcomePlugin deactivated');
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $content = $response->getBody()->__toString();
        $content .= '<!-- WelcomePlugin Footer -->';

        // Create a new response with the modified content
        $newResponse = $response->withBody(\Nyholm\Psr7\Stream::create($content));
        $event->setResponse($newResponse);
    }
}
?>