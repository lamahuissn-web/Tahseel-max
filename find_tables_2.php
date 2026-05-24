<?php

$path = 'c:\xampp8.2\htdocs\My-Projects\tahseel\resources\views';
$dir = new RecursiveDirectoryIterator($path);
$iterator = new RecursiveIteratorIterator($dir);

$files_found = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());

        // Check for <table> tag
        if (stripos($content, '<table') !== false) {
            // Check if 'table-responsive' is NOT present in the file
            // This is a rough heuristic. A file might have one responsive table and one non-responsive one.
            // But if it has NO table-responsive class at all, it's a prime candidate.
            if (stripos($content, 'table-responsive') === false) {
                echo $file->getPathname() . "\n";
            }
        }
    }
}
