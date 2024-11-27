<?php
// System/ContentProvider/ContentProviderServiceInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;

interface ContentProviderInterface
{
    /**
     * returns all entries from the content provider
     *
     * @return ListResultInterface
     */
    public function list(): ListResultInterface;
}
?>