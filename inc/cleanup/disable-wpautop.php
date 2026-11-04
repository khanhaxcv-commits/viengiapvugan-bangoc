<?php

/**
 * Disable WordPress auto paragraph.
 *
 * Use this file only for projects that fully control HTML output.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
    remove_filter('comment_text', 'wpautop');
}, 20);