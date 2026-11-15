<?php
/**
 * Shared module: single-post-breadcrumb
 * Version: 2.0.0
 * Public API: ishen_shared_single_post_breadcrumb()
 * Keep the function name, echo behavior and HTML classes unchanged.
 */

defined('ABSPATH') || exit;

function ishen_shared_single_post_breadcrumb()
{
    if (!function_exists('ishen_shared_get_breadcrumb_html')) {
        return;
    }

    echo ishen_shared_get_breadcrumb_html(array(
        'context' => 'single_post',
        'class' => 'cvn-single-breadcrumbs',
        'separator' => ishen_shared_get_breadcrumb_separator('cvn-single-breadcrumbs__divider'),
    ));
}
