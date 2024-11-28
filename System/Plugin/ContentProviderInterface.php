<?php
// System/ContentProvider/ContentProviderServiceInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;

/**
 * Interface ContentProviderInterface
 *
 * This interface represents a content provider.
 * Content providers are responsible for retrieving and managing 
 * content entries.
 */
interface ContentProviderInterface
{
    /**
     * Retrieves a list of content entries.
     *
     * @param int|null $max The maximum number of entries to retrieve. Defaults to 100.
     * @param int|null $offset The offset from which to start retrieving entries. Defaults to null, meaning start from the beginning.
     * @return ListResultInterface The result of the list operation, containing the content entries and metadata.
     */
    public function list(?int $max = 100, ?int $offset = null): ListResultInterface;
}
?>