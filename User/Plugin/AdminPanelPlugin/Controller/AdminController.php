<?php
// User/Plugin/AdminPanelPlugin/Controller/AdminController.php

declare(strict_types=1);

namespace User\Plugin\AdminPanelPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Util\ResponseUtil;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AdminController
{
    public function __construct(private \Twig\Environment $twig)
    {
    }

    #[Route('/admin', ['GET'], ['admin'])]
    public function admin(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'admin');
    }
}

?>