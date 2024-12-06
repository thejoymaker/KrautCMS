<?php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PageRoutingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'generatePath']),
        ];
    }

    /**
     * Generates a URL path based on a route name and parameters.
     *
     * @param string $routeName  The name of the route.
     * @param array  $parameters An associative array of parameters for the route.
     * @return string The generated URL path.
     */
    public function generatePath(string $routeName, array $parameters = []): string
    {
        // Implement your route generation logic here.
        // This is a simple example for demonstration purposes.

        $slug = $parameters['slug'] ?? '';
        switch ($routeName) {
            case 'page_show':
                return '/pages/' . urlencode($slug);
            case 'page_edit':
                return '/pages/' . urlencode($slug) . '/edit';
            case 'page_create':
                return '/pages/create';
            case 'page_list':
                return '/pages';
            default:
                return '/';
        }
    }
}


?>