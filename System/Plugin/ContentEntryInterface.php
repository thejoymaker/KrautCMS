<?php
// System/ContentProvider/ContentProviderServiceInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;
/**
 * Interface ContentEntryInterface
 *
 * This interface represents a single content entry.
 */
interface ContentEntryInterface
{
    /**
     * returns the title of the content entry
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * returns the absolute path of the content entry
     *
     * @return string
     */
    public function getAbsolutePath(): string;
}
?>