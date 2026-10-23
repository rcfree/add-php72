<?php
// Router for PHP's built-in server: serve real files, otherwise hand off to CI.
$docroot = '/local/add/vendor/clinicle/web/public';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($docroot . $path);

if ($path !== '/' && $file !== false && strpos($file, $docroot) === 0 && is_file($file)) {
    return false;
}

chdir($docroot);
require $docroot . '/index.php';
