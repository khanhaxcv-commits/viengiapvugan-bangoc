<?php

/**
 * Fonts
 *
 * Reusable Google Fonts loader for WordPress projects.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    $fonts = array(
        'site-font-body' => 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"',
        // 'site-font-heading' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
        // 'site-font-script' => 'https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap',
    );

    foreach ($fonts as $handle => $url) {
        wp_enqueue_style(
            $handle,
            esc_url_raw($url),
            array(),
            null
        );
    }
});

add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if ('preconnect' !== $relation_type) {
        return $urls;
    }

    $urls[] = array(
        'href' => 'https://fonts.googleapis.com',
    );

    $urls[] = array(
        'href' => 'https://fonts.gstatic.com',
        'crossorigin' => 'anonymous',
    );

    return $urls;
}, 10, 2);
