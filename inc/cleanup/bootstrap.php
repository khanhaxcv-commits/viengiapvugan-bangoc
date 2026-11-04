<?php

defined('ABSPATH') || exit;

$cleanup_modules = array(
    'disable-wpautop.php',
);

foreach ($cleanup_modules as $cleanup_module) {
    $cleanup_module_file = __DIR__ . '/' . $cleanup_module;

    if (file_exists($cleanup_module_file)) {
        require_once $cleanup_module_file;
    }
}
