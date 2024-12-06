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

    public function getPage(string $slug): ?PageEntry
    {
        return $this->getPageContent($slug);
    }

    private function getPageContent(string $slug): ?PageEntry
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
        // $title = ucwords(str_replace('-', ' ', $safeSlug));

        return new PageEntry($safeSlug, $filePath, $content);
    }
    // {
    //     // Define the path to your content files
    //     $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';

    //     // Sanitize the slug to prevent directory traversal
    //     $safeSlug = basename($slug);

    //     // Construct the file path
    //     $filePath = $contentDir . '/' . $safeSlug . '/content.txt';

    //     if (!file_exists($filePath)) {
    //             return null;
    //     }

    //     // Read the content from the file
    //     $content = file_get_contents($filePath);

    //     // Generate a title from the slug or include a title in the content file
    //     $title = ucwords(str_replace('-', ' ', $safeSlug));

    //     return [
    //         'title' => $title,
    //         'content' => $content,
    //     ];
    // }

    public function save(PageEntry $page): void
    {
        $this->savePageContent($page->getSlug(), $page->getContent());
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
}
?>