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
        // Define the path to your content files
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';

        // Sanitize the slug to prevent directory traversal
        $safeSlug = basename($slug);

        // Construct the file path
        $filePath = $contentDir . '/' . $safeSlug . '/content.txt';

        if (!file_exists($filePath)) {
                return null;
        }

        // Read the content from the file
        $content = file_get_contents($filePath);

        // Generate a title from the slug or include a title in the content file
        $title = ucwords(str_replace('-', ' ', $safeSlug));

        return [
            'title' => $title,
            'content' => $content,
        ];
    }
}