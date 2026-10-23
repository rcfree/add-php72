<?php
// Router for PHP's built-in server: serve real files, otherwise hand off to CI.
$docroot = '/local/add/vendor/clinicle/web/public';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($docroot . $path);

// Local preview: show the login form at root until a CI session exists.
if ($path === '/' && empty($_COOKIE['ci_session']) && is_file(__DIR__ . '/preview-login.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/preview-login.html');
    return true;
}

if ($path !== '/' && $file !== false && strpos($file, $docroot) === 0 && is_file($file)) {
    return false;
}

chdir($docroot);
require $docroot . '/index.php';
