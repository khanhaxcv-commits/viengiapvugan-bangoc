<?php
/**
 * Shared library bootstrap.
 * Library version: 2.0.0
 * Keep module names and public APIs stable across projects.
 */

defined('ABSPATH') || exit;

$shared_modules = array(
    'breadcrumbs.php',
    'single-post-breadcrumb.php',
    'post-category-breadcrumb.php',
);

foreach ($shared_modules as $shared_module) {
    $shared_module_file = __DIR__ . '/' . $shared_module;

    if (file_exists($shared_module_file)) {
        require_once $shared_module_file;
    }
}
