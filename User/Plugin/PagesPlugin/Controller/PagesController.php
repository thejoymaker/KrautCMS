<?php
// User/Plugin/PagesPlugin/Controller/PagesController.php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Nyholm\Psr7\Response;
use User\Plugin\PagesPlugin\Twig\PageRoutingExtension;

#[Controller]
class PagesController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $twig->addExtension(new PageRoutingExtension());
    }

    #[Route(path: '/pages', methods: ['GET'])]
    public function listPages(ServerRequestInterface $request): ResponseInterface
    {
        // Fetch the list of pages
        $pages = $this->getPages();

        // Render the template with the list of pages
        // $html = $this->twig->render('@PagesPlugin/list.html.twig', ['pages' => $pages]);

        // return new Response(200, [], $html);

        return ResponseUtil::respondRelative($this->twig, 'PagesPlugin','list', ['pages' => $pages]);
    }

    private function getPages(): array
    {
        // Define the path to your content files
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';

        // Initialize an empty array to hold the pages
        $pages = [];

        // Iterate over the content directory to fetch page information
        foreach (glob($contentDir . '/*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);
            $filePath = $dir . '/content.txt';

            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $title = ucwords(str_replace('-', ' ', $slug));

                $pages[] = [
                    'id' => $slug,
                    'title' => $title,
                    'content' => $content,
                ];
            }
        }

        return $pages;
    }

    #[Route(path: '/pages/{slug:[a-zA-Z0-9\-]+}/edit', methods: ['GET', 'POST'], roles: ['editor'])]
    public function editPage(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';

        // Fetch the page content based on slug
        $page = $this->getPageContent($slug);

        if ($page === null) {
            return new Response(404, [], 'Page not found');
        }

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $content = $parsedBody['content'] ?? '';

            // Save the content to file
            $this->savePageContent($slug, $content);

            // Redirect to the page view
            return new Response(302, ['Location' => $this->generateUrl('page_show', ['slug' => $slug])]);
        }

        // Render the editor template
        return ResponseUtil::respondRelative(
            $this->twig,
            'PagesPlugin',
            'editor',
            ['page' => [
                'id' => $slug,
                'title' => $page['title'],
                'content' => $page['content'],
            ]]
        );
    }

    private function savePageContent(string $slug, string $content): void
    {
        // Define the path to your content files
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';

        // Sanitize the slug to prevent directory traversal
        $safeSlug = basename($slug);

        // Construct the file path
        $filePath = $contentDir . '/' . $safeSlug . '/content.txt';

        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Write the content to the file
        file_put_contents($filePath, $content);
    }

    private function generateUrl(string $routeName, array $parameters = []): string
    {
        // Use the same logic as in your PageRoutingExtension
        switch ($routeName) {
            case 'page_show':
                $slug = $parameters['slug'] ?? '';
                return '/pages/' . urlencode($slug);
            default:
                return '/';
        }
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