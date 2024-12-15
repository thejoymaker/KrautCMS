<?php
// User/Plugin/AdminPanelPlugin/Controller/AdminController.php

declare(strict_types=1);

namespace User\Plugin\AdminPanelPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Service\CacheService;
use Kraut\Service\PluginService;
use Kraut\Util\ResponseUtil;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AdminController
{
    public function __construct(private \Twig\Environment $twig,
                                private CacheService $cacheService,
                                private PluginService $pluginService)
    {
    }

    #[Route('/admin', ['GET'], ['admin'])]
    public function admin(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'admin');
    }

    #[Route('/admin/action', ['POST'], ['admin'])]
    public function action(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getParsedBody()['action'] ?? '';
        switch ($action) {
        case 'clear_cache':
            $this->cacheService->nukeCache();
                return new Response(200, [], json_encode(['ok' => true]));
            default:
                return new Response(400, [], 'Invalid action');
        }
    }

    #[Route('/admin/query', ['POST'], ['admin'])]
    public function query(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getParsedBody()['query'] ?? '';
        switch ($query) {
        case 'list_plugins':
            $result = $this->pluginService->listPlugins();
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
        default:
            return new Response(400, [], 'Invalid query');
        }
    }
}
        
?>