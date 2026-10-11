<?php

$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
) ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_folder = (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '';

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $base_folder);
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}