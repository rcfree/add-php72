<?php
// Router for PHP's built-in server: serve real files, otherwise hand off to CI.
$docroot = '/local/add/vendor/clinicle/web/public';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($docroot . $path);

// Local preview: show the login form at root until a CI session exists.
// /login (or /?login=1) always shows it, even when a session cookie exists.
$forceLogin = ($path === '/login' || ($path === '/' && isset($_GET['login'])));
if (($forceLogin || ($path === '/' && empty($_COOKIE['ci_session']))) &&
    is_file(__DIR__ . '/preview-login.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    readfile(__DIR__ . '/preview-login.html');
    return true;
}

if ($path !== '/' && $file !== false && strpos($file, $docroot) === 0 && is_file($file)) {
    return false;
}

chdir($docroot);
require $docroot . '/index.php';
