<?php
declare(strict_types=1);
namespace User\Plugin\PagesPlugin\Persistence;

use Kraut\Plugin\Content\ContentEntryInterface;

class PageEntry implements ContentEntryInterface
{
    public function __construct(
        private string $slug,
        private string $filename,
        private string $content,
        private array $metadata = [] 
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->metadata['title'] ?? 'Untitled';
    }

    public function getAbsolutePath(): string
    {
        return '/pages/' . $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

?>