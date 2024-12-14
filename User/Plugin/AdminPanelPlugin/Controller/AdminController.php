<?php
// User/Plugin/AdminPanelPlugin/Controller/AdminController.php

declare(strict_types=1);

namespace User\Plugin\AdminPanelPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Service\CacheService;
use Kraut\Util\ResponseUtil;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AdminController
{
    public function __construct(private \Twig\Environment $twig,
                                private CacheService $cacheService)
    {
    }

    #[Route('/admin', ['GET'], ['admin'])]
    public function admin(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'admin');
    }

    #[Route('/admin/clearcache', ['POST'], ['admin'])]
    public function clearCache(ServerRequestInterface $request): ResponseInterface
    {
        if($request->getParsedBody()['action'] !== 'clear_cache') {
            return new Response(400, [], 'Invalid action');
        }
        $this->cacheService->nukeCache();
        return new Response(200, [], json_encode(['ok' => true]));
    }
}

?>