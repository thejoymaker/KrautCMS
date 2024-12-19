<?php
// User/Plugin/PagesPlugin/Controller/PagesController.php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Markdown\KrautParsedown;
use Kraut\Service\AuthenticationServiceInterface;
use Kraut\Service\ConfigurationService;
use Kraut\Util\ResponseUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Nyholm\Psr7\Response;
use Parsedown;
use Psr\Container\ContainerInterface;
use User\Plugin\PagesPlugin\Persistence\PageEntry;
use User\Plugin\PagesPlugin\Persistence\PageRepository;
use User\Plugin\PagesPlugin\Twig\PageRoutingExtension;
use User\Plugin\PagesPlugin\Util\PagePathUtil;

#[Controller]
class PagesController
{
    public function __construct(private ContainerInterface $container, 
                                private Environment $twig, 
                                private PageRepository $pageRepository)
    {
        $twig->addExtension(new PageRoutingExtension());
    }

    #[Route(path: '/', methods: ['GET'])]
    public function home(ServerRequestInterface $request): ResponseInterface
    {
        $configService = $this->container->get(ConfigurationService::class);
        $homeEndPointPath = $configService->get('pagesplugin.home', '/pages');
        return ResponseUtil::redirectTemporary($homeEndPointPath);
        // return new Response(200, [], 'Welcome to the Pages Plugin!');
    }

    #[Route(path: '/pages', methods: ['GET'])]
    public function listPages(ServerRequestInterface $request): ResponseInterface
    {
        // Fetch the list of pages
        $pages = $this->pageRepository->list();

        $userRoles = [];

        $authenticationService = $this->container->get(AuthenticationServiceInterface::class);

        if ($authenticationService->isAuthenticated()) {
            $userRoles = $authenticationService->getCurrentUser()->getRoles();
        }

        return ResponseUtil::respondRelative(
            $this->twig, 
            'PagesPlugin',
            'list', 
            [
                'pages' => $pages->getEntries(),
                'userRoles' => $userRoles
            ]
        );
    }

    #[Route(path: '/pages/{slug:[a-zA-Z0-9\-]+}/edit', methods: ['GET', 'POST'], roles: ['editor'])]
    public function editPage(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';

        // Fetch the page content based on slug
        $page = $this->pageRepository->getPage($slug);

        if ($page === null) {
            return ResponseUtil::respondNegative($this->twig);
        }

        $metadata = $page->getMetadata();

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $content = $parsedBody['content'] ?? '';
            // $metadata = $parsedBody['metadata'] ?? [];
            // PageEntry::validateContent($content);
            // Save the content to file
            $this->pageRepository->save(new PageEntry($slug, '', $content, $metadata));

            // Redirect to the page view
            return new Response(302, ['Location' => PagePathUtil::generatePath('page_show', ['slug' => $slug])]);
        }

        // Render the editor template
        return ResponseUtil::respondRelative(
            $this->twig,
            'PagesPlugin',
            'editor',
            ['page' => $page]
        );
    }

    #[Route(path: '/pages/create', methods: ['GET', 'POST'], roles: ['editor'])]
    public function createPage(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $metadata = $parsedBody['metadata'] ?? [];
            // Retrieve form data
            $slug = $parsedBody['slug'] ?? '';
            // $title = $parsedBody['title'] ?? '';
            $content = $parsedBody['content'] ?? '';

            // Validate input
            $errors = $this->validatePageData($slug, $content, $metadata);
            if (!empty($errors)) {
                // Render the form again with errors
                return ResponseUtil::respondRelative(
                    $this->twig,
                    'PagesPlugin',
                    'create',
                    [
                        'errors' => $errors,
                        'page' => [
                            'slug' => $slug,
                            'content' => $content,
                            'metadata' => $metadata,
                        ],
                    ]
                );
            }

            // Save the new page
            $this->pageRepository->save(new PageEntry($slug, '', $content, $metadata));

            // Redirect to the page list
            return new Response(302, ['Location' => PagePathUtil::generatePath('page_list')]);
        }

        // Render the creation form
        return ResponseUtil::respondRelative(
            $this->twig,
            'PagesPlugin',
            'create',
            []
        );
    }

    private function validatePageData(string $slug, string $content, array $metadata): array
    {
        $errors = [];

        if (empty($slug)) {
            $errors['slug'] = 'Slug is required.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $errors['slug'] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
        } elseif ($this->pageRepository->getPage($slug) !== null) {
            $errors['slug'] = 'A page with this slug already exists.';
        }

        if (!isset($metadata['title']) || empty($metadata['title'])) {
            $errors['title'] = 'Title is required.';
        }

        if (empty($content)) {
            $errors['content'] = 'Content is required.';
        }

        return $errors;
    }

    // private function generateUrl(string $routeName, array $parameters = []): string
    // {
    //     switch ($routeName) {
    //         case 'page_show':
    //             $slug = $parameters['slug'] ?? '';
    //             return '/pages/' . urlencode($slug);
    //         case 'page_edit':
    //             $slug = $parameters['slug'] ?? '';
    //             return '/pages/' . urlencode($slug) . '/edit';
    //         case 'page_list':
    //             return '/pages';
    //         case 'page_create':
    //             return '/pages/create';
    //         default:
    //             return '/';
    //     }
    // }

    #[Route(path: '/pages/{slug:[a-zA-Z0-9\-]+}', methods: ['GET'])]
    public function showPage(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';
    
        // Fetch the page content based on slug
        $page = $this->pageRepository->getPage($slug);
    
        if ($page === null) {
            return ResponseUtil::respondNegative($this->twig);
        }

        // $html = $this->twig->render('@PagesPlugin/page.html.twig', ['page' => $page]);
    
        // return new Response(200, [], $html);

        $parsedown = new KrautParsedown();
        $parsedown->setSafeMode(true); // Enable safe mode for security
        $contentHtml = $parsedown->text($page->getContent());

        // Pass the HTML content to the template
        // $page->setContent($contentHtml);

        // Render the template
        $html = $this->twig->render('@PagesPlugin/page.html.twig', ['page' => $page, 'contentHtml' => $contentHtml]);

        return new Response(200, [], $html);
    }
}