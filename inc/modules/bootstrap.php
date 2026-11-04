<?php

defined('ABSPATH') || exit;

$standalone_modules = array(
    'section-background-url.php',
    'image-url.php',
);

// Chỉ nạp công cụ xuất nội dung Tailwind trên môi trường local.
$theme_site_host = wp_parse_url(home_url(), PHP_URL_HOST);

if ($theme_site_host === 'localhost' || $theme_site_host === '127.0.0.1'
    || substr($theme_site_host, -6) === '.local' || substr($theme_site_host, -5) === '.test') {
    $standalone_modules[] = 'tailwind-database-content.php';
}

foreach ($standalone_modules as $standalone_module) {
    $standalone_module_file = __DIR__ . '/' . $standalone_module;

    if (file_exists($standalone_module_file)) {
        require_once $standalone_module_file;
    }
}
