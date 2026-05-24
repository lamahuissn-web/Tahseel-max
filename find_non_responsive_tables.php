<?php

function scanDirRecursive($dir, &$results = [])
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

        if (!is_dir($path)) {
            if (str_ends_with($path, '.blade.php')) {
                $results[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            scanDirRecursive($path, $results);
        }
    }

    return $results;
}

$files = scanDirRecursive('c:\xampp8.2\htdocs\My-Projects\tahseel\resources\views');
$foundFiles = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Check if file has a <table> tag
    if (stripos($content, '<table') !== false) {
        // Check if it lacks "table-responsive" class
        if (stripos($content, 'table-responsive') === false) {
            $foundFiles[] = $file;
        }
    }
}

echo "Files with tables but no 'table-responsive' class:\n";
foreach ($foundFiles as $file) {
    echo $file . "\n";
}
