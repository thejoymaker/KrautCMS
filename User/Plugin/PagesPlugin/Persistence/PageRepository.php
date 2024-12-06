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
        // $files = scandir($this->contentPath);
        // $files = array_filter($files, fn($file) => $file !== '.' && $file !== '..');
        // $files = array_slice($files, $pageNumber * $max, $max);

        // $pages = [];
        // foreach ($files as $file) {
        //     $slug = basename($file);
        //     $content = file_get_contents($this->contentPath . '/' . $file);
        //     $pages[] = new PageEntry($slug, $file, $content);
        // }
        $pagesList = $this->getPages();
        // $listResult = new PagesListResult($pagesList);

        return new PagesListResult($pagesList);
    }

    /**
     * list pages.
     * 
     * @return PageEntry[]
     */
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
                $pages[] = new PageEntry($slug, $filePath, $content);
            }
        }
        return $pages;
    }
}
?>