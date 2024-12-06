<?php
declare(strict_types=1);
namespace User\Plugin\PagesPlugin\Persistence;

use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\Content\ListResultInterface;

class PageRepository implements ContentProviderInterface {
    public function __construct(
    )
    {
    }

    public function list(?int $max = 100, ?int $pageNumber = null): ListResultInterface
    {
        $pagesList = $this->getPages();
        return new PagesListResult($pagesList);
    }

    /**
     * list pages.
     * 
     * @return PageEntry[]
     */
    private function getPages(): array
    {
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
        $pages = [];

        foreach (glob($contentDir . '/*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);
            $contentFile = $dir . '/content.txt';
            $titleFile = $dir . '/meta.txt';

            if (file_exists($contentFile)) {
                $content = file_get_contents($contentFile);
                $title = file_exists($titleFile) ? file_get_contents($titleFile) : '';
                $pages[] = new PageEntry($slug, $contentFile, $content, $title);
            }
        }

        return $pages;
    }

    public function getPage(string $slug): ?PageEntry
    {
        return $this->getPageContent($slug);
    }

    private function getPageContent(string $slug): ?PageEntry
    {
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
        $safeSlug = basename($slug);
        $pageDir = $contentDir . '/' . $safeSlug;
        $contentFile = $pageDir . '/content.txt';
        $titleFile = $pageDir . '/meta.txt';

        if (!file_exists($contentFile)) {
            return null;
        }

        $content = file_get_contents($contentFile);
        $title = file_exists($titleFile) ? file_get_contents($titleFile) : '';

        return new PageEntry($safeSlug, $contentFile, $content, $title);
    }

    public function save(PageEntry $page): void
    {
        $this->savePageContent($page->getSlug(), $page->getContent(), $page->getTitle());
    }

    private function savePageContent(string $slug, string $content, string $title): void
    {
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
        $safeSlug = basename($slug);
        $pageDir = $contentDir . '/' . $safeSlug;

        if (!is_dir($pageDir)) {
            mkdir($pageDir, 0777, true);
        }

        $contentFile = $pageDir . '/content.txt';
        $titleFile = $pageDir . '/meta.txt';

        file_put_contents($contentFile, $content);
        file_put_contents($titleFile, $title);
    }
}
?>