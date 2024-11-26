<?php
// User/Plugin/PagesPlugin/Controller/PagesController.php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Nyholm\Psr7\Response;

#[Controller]
class PagesController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    #[Route(path: '/pages/{slug:[a-zA-Z0-9\-]+}', methods: ['GET'])]
    public function showPage(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';
    
        // Fetch the page content based on slug
        $page = $this->getPageContent($slug);
    
        if ($page === null) {
            return new Response(404, [], 'Page not found');
        }
    
        $html = $this->twig->render('@PagesPlugin/page.html.twig', ['page' => $page]);
    
        return new Response(200, [], $html);
    }

    private function getPageContent(string $slug): ?array
    {
        // Placeholder for your content fetching logic
        // Replace with actual data retrieval (e.g., from a database or file)
    
        $pages = [
            'about-us' => [
                'title' => 'About Us',
                'content' => '<p>This is the About Us page content.</p>',
            ],
            'contact' => [
                'title' => 'Contact',
                'content' => '<p>This is the Contact page content.</p>',
            ],
            // Add more pages as needed
        ];
    
        return $pages[$slug] ?? null;
    }
}