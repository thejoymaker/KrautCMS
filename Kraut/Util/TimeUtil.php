<?php
declare(strict_types=1);

namespace Kraut\Util;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class TimeUtil
{
    /**
     * Get the maximum file modification time of a resource either directory or file are accepted.
     * 
     * @param string $resource the resource to get the maximum file modification time
     */
    public static function maxFileMTime(string $resource): int
    {
        if(is_dir($resource)) {
            $directory = new RecursiveDirectoryIterator($resource);
            $iterator = new RecursiveIteratorIterator($directory);
            $maxFileTime = 0;
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                $maxFileTime = max($maxFileTime, filemtime($file->getPathname()));
            }
            return $maxFileTime;
        } else if(is_file($resource)) {
            return filemtime($resource);
        } else {
            throw new \Exception('Invalid resource');
        }
    }
}

?>