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
use Psr\Container\ContainerInterface;
use User\Plugin\PagesPlugin\Persistence\PageEntry;
use User\Plugin\PagesPlugin\Persistence\PageRepository;
use User\Plugin\PagesPlugin\Twig\PageRoutingExtension;

#[Controller]
class PagesController
{
    public function __construct(private ContainerInterface $container, 
                                private Environment $twig, 
                                private PageRepository $pageRepository)
    {
        $twig->addExtension(new PageRoutingExtension());
    }

    #[Route(path: '/pages', methods: ['GET'])]
    public function listPages(ServerRequestInterface $request): ResponseInterface
    {
        // Fetch the list of pages
        $pages = $this->pageRepository->list();

        // Render the template with the list of pages
        // $html = $this->twig->render('@PagesPlugin/list.html.twig', ['pages' => $pages]);

        // return new Response(200, [], $html);

        return ResponseUtil::respondRelative($this->twig, 'PagesPlugin','list', ['pages' => $pages->getEntries()]);
    }

    #[Route(path: '/pages/{slug:[a-zA-Z0-9\-]+}/edit', methods: ['GET', 'POST'], roles: ['editor'])]
    public function editPage(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';

        // Fetch the page content based on slug
        $page = $this->pageRepository->getPage($slug);

        if ($page === null) {
            return new Response(404, [], 'Page not found');
        }

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $content = $parsedBody['content'] ?? '';

            // PageEntry::validateContent($content);
            // Save the content to file
            $this->pageRepository->save(new PageEntry($slug, '', $content));

            // Redirect to the page view
            return new Response(302, ['Location' => $this->generateUrl('page_show', ['slug' => $slug])]);
        }

        // Render the editor template
        return ResponseUtil::respondRelative(
            $this->twig,
            'PagesPlugin',
            'editor',
            ['page' => $page]
        );
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
        $page = $this->pageRepository->getPage($slug);
    
        if ($page === null) {
            return new Response(404, [], 'Page not found');
        }
    
        $html = $this->twig->render('@PagesPlugin/page.html.twig', ['page' => $page]);
    
        return new Response(200, [], $html);
    }
}