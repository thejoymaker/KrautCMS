<?php
declare(strict_types=1);
namespace User\Plugin\PagesPlugin\Persistence;

use Kraut\Plugin\Content\ContentEntryInterface;

class PageEntry implements ContentEntryInterface
{
    private string $title;

    public function __construct(
        private string $slug,
        private string $filename,
        private string $content
    )
    {
        // Capitalize the first letters of the title words
        $this->title = ucwords(str_replace('-', ' ', $slug));
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAbsolutePath(): string
    {
        return '/pages/' . $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

?>