<?php
declare(strict_types=1);
namespace User\Plugin\PagesPlugin\Persistence;

use Kraut\Plugin\Content\ContentProviderInterface;
use Kraut\Plugin\Content\ListResultInterface;
use Symfony\Component\Yaml\Yaml;

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
            $contentFile = $dir . '/content.md';
            $metaFile = $dir . '/meta.yml';
    
            if (file_exists($contentFile)) {
                $content = file_get_contents($contentFile);
    
                // Parse YAML metadata
                $metadata = [];
                if (file_exists($metaFile)) {
                    try {
                        $metadata = Yaml::parseFile($metaFile);
                    } catch (\Exception $e) {
                        // Handle parsing error
                        error_log("Error parsing YAML for page '$slug': " . $e->getMessage());
                    }
                }
    
                $pages[] = new PageEntry($slug, $contentFile, $content, $metadata);
            }
        }
    
        return $pages;
    }
    // private function getPages(): array
    // {
    //     $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
    //     $pages = [];

    //     foreach (glob($contentDir . '/*', GLOB_ONLYDIR) as $dir) {
    //         $slug = basename($dir);
    //         $contentFile = $dir . '/content.md';
    //         $titleFile = $dir . '/meta.yml';

    //         if (file_exists($contentFile)) {
    //             $content = file_get_contents($contentFile);
    //             $title = file_exists($titleFile) ? file_get_contents($titleFile) : '';
    //             $pages[] = new PageEntry($slug, $contentFile, $content, $title);
    //         }
    //     }

    //     return $pages;
    // }

    public function getPage(string $slug): ?PageEntry
    {
        return $this->getPageContent($slug);
    }

    private function getPageContent(string $slug): ?PageEntry
    {
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
        $safeSlug = basename($slug);
        $pageDir = $contentDir . '/' . $safeSlug;
        $contentFile = $pageDir . '/content.md';
        $metaFile = $pageDir . '/meta.yml';

        if (!file_exists($contentFile)) {
            return null;
        }

        // $content = file_get_contents($contentFile);
        // $title = file_exists($titleFile) ? file_get_contents($titleFile) : '';

        $metadata = [];
        if (file_exists($contentFile)) {
            $content = file_get_contents($contentFile);

            // Parse YAML metadata
            if (file_exists($metaFile)) {
                try {
                    $metadata = Yaml::parseFile($metaFile);
                } catch (\Exception $e) {
                    // Handle parsing error
                    error_log("Error parsing YAML for page '$slug': " . $e->getMessage());
                }
            }
        }
        return new PageEntry($safeSlug, $contentFile, $content, $metadata);
    }

    public function save(PageEntry $page): void
    {
        $this->savePageContent($page->getSlug(), $page->getContent(), $page->getMetadata());
    }

    private function savePageContent(string $slug, string $content, array $metadata): void
    {
        $contentDir = __DIR__ . '/../../../Content/PagesPlugin/pages';
        $safeSlug = basename($slug);
        $pageDir = $contentDir . '/' . $safeSlug;

        if (!is_dir($pageDir)) {
            mkdir($pageDir, 0777, true);
        }
        
        $metaFile = $pageDir . '/meta.yml';
        $yamlContent = Yaml::dump($metadata);
        file_put_contents($metaFile, $yamlContent);

        $contentFile = $pageDir . '/content.md';
        file_put_contents($contentFile, $content);
    }
}
?>