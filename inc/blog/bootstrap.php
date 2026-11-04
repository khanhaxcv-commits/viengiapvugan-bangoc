<?php

defined('ABSPATH') || exit;

$blog_modules = array(
    'blog-category-archive.php',
);

foreach ($blog_modules as $blog_module) {
    $blog_module_file = __DIR__ . '/' . $blog_module;

    if (file_exists($blog_module_file)) {
        require_once $blog_module_file;
    }
}
