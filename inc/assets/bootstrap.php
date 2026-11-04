<?php

defined('ABSPATH') || exit;

$theme_asset_modules = array(
    'fonts.php',
    'styles.php',
    'external.php',
    'scripts.php',
    'fontawesome.php',
);

foreach ($theme_asset_modules as $theme_asset_module) {
    $theme_asset_module_file = __DIR__ . '/' . $theme_asset_module;

    if (file_exists($theme_asset_module_file)) {
        require_once $theme_asset_module_file;
    }
}
