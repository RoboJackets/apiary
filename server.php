<?php

/**
 * Laravel - A PHP Framework For Web Artisans.
 *
 * @author Taylor Otwell <taylor@laravel.com>
 */

declare(strict_types=1);

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (! is_string($url)) {
    throw new Exception('Failed parsing url');
}

$uri = urldecode($url);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ('/' !== $uri && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
