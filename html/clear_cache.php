<?php
// Temporary cache clear script — DELETE THIS FILE AFTER USE
// Access: https://lion-express.com/clear_cache.php

$cacheDir = __DIR__ . '/../var/cache';

function deleteDir($dir) {
    if (!is_dir($dir)) return 0;
    $count = 0;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $count += deleteDir($path);
            @rmdir($path);
        } else {
            @unlink($path);
            $count++;
        }
    }
    return $count;
}

$deleted = deleteDir($cacheDir);

echo "Cache cleared. $deleted files removed.<br>";
echo "Now delete this file: /html/clear_cache.php";
