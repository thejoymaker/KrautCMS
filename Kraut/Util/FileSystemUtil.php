<?php

// Kraut/Util/FileSystemUtil.php

declare(strict_types=1);

namespace Kraut\Util;



class FileSystemUtil
{

    public static function unlinkDir($dir):void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? self::unlinkDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public static function safeLog(string $message, string $logFile = '/path/to/your/logfile.log'): void
    {
        // Open the file in append mode
        $fileHandle = fopen($logFile, 'a');
        
        if ($fileHandle === false) {
            throw new \RuntimeException("Unable to open log file: $logFile");
        }
    
        try {
            // Acquire an exclusive lock
            if (flock($fileHandle, LOCK_EX)) {
                // Write the message to the file
                fwrite($fileHandle, $message . PHP_EOL);
                // Release the lock
                flock($fileHandle, LOCK_UN);
            } else {
                throw new \RuntimeException("Unable to acquire lock on log file: $logFile");
            }
        } finally {
            // Close the file handle
            fclose($fileHandle);
        }
    }
}