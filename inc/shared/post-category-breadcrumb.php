<?php
/**
 * Shared module: post-category-breadcrumb
 * Version: 2.0.0
 * Public API: ishen_shared_get_post_cat_breadcrumb_html($term)
 */

defined('ABSPATH') || exit;

function ishen_shared_get_post_cat_breadcrumb_html($term)
{
    if (!function_exists('ishen_shared_get_breadcrumb_html')) {
        return '';
    }

    return ishen_shared_get_breadcrumb_html(array(
        'context' => 'post_category',
        'term' => $term,
        'class' => 'breadcrumbs cvn-post-cat-breadcrumbs',
    ));
}
