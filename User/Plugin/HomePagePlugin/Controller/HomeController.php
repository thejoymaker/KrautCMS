<?php
// System/Controller/HomeController.php

declare(strict_types=1);

namespace User\Plugin\HomePagePlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Nyholm\Psr7\Response;
use User\Plugin\HomePagePlugin\Service\GreetingService;

#[Controller]
class HomeController
{
    private LoggerInterface $logger;
    private GreetingService $greetingService;
    private Environment $twig;

    public function __construct(
        LoggerInterface $logger,
        GreetingService $greetingService,
        Environment $twig
    ) {
        $this->logger = $logger;
        $this->greetingService = $greetingService;
        $this->twig = $twig;
    }

    #[Route(path: '/', methods: ['GET'])]
    public function index(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->logger->info('Home page accessed');
        return ResponseUtil::respondRelative($this->twig, 'Theme', 'home', []);
    }

    #[Route(path: '/hello[/{name}]', methods: ['GET'], roles: ['user'])]
    public function hello(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $name = $args['name'] ?? 'Guest';
        $greeting = $this->greetingService->getGreeting($name);
        $this->logger->info("Greeting page accessed with name: {$name}");
        return ResponseUtil::respondRelative($this->twig, 'Theme', 'hello', ['greeting' => $greeting]);
    }
}
?>