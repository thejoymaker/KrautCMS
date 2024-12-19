<?php

declare(strict_types=1);

namespace User\Plugin\PagesPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use User\Plugin\PagesPlugin\Util\PagePathUtil;

class PageRoutingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [PagePathUtil::class, 'generatePath']),
        ];
    }
}


?>