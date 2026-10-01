<?php

$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
) ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('BASE_URL', $protocol . $host);

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}