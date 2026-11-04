<?php

/**
 * Third-party integration bootstrap.
 */

defined('ABSPATH') || exit;

$integration_modules = array(
    'all-in-one-wp-migration.php',
);

foreach ($integration_modules as $integration_module) {
    $integration_module_file = __DIR__ . '/' . $integration_module;

    if (file_exists($integration_module_file)) {
        require_once $integration_module_file;
    }
}
