<?php
// System/ContentProvider/ListResultInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;

use Kraut\Plugin\ContentEntryInterface;

/**
 * Interface ListResultInterface
 *
 * This interface represents the result of a list operation.
 */
interface ListResultInterface
{
    /**
     * Returns an array of content entries.
     *
     * @return ContentEntryInterface[] An array of content entries.
     */
    public function getEntries(): array;

    /**
     * Returns the total number of entries available.
     *
     * @return int The total count of entries.
     */
    public function getTotalCount(): int;

    /**
     * Returns the number of entries in the current list.
     *
     * @return int The count of entries in the current list.
     */
    public function getCount(): int;

    /**
     * Returns the current page number (if pagination is used).
     *
     * @return int|null The current page number, or null if not paginated.
     */
    public function getPage(): ?int;

    /**
     * Returns the number of entries per page (if pagination is used).
     *
     * @return int|null The page size, or null if not paginated.
     */
    public function getPageSize(): ?int;
}
?>