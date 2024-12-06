<?php
declare(strict_types=1);
namespace User\Plugin\PagesPlugin\Persistence;

use Kraut\Plugin\Content\ListResultInterface;

class PagesListResult implements ListResultInterface
{
    public function getEntries(): array
    {
        return $this->pages;
    }

    public function getCount(): int
    {
        return count($this->pages);
    }

    public function getPage(): int
    {
        return $this->pageNumber;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function __construct(
        /** @var PageEntry[] $pages */
        private array $pages = [],
        private int $pageNumber = 1,
        private int $pageSize = 10
    )
    {
    }

    public function getPages(): array
    {
        return $this->pages;
    }
}
?>