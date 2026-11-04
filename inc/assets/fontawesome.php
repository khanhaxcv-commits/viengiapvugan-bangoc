<?php

/**
 * Enqueue FontAwesome Pro v7
 * 
 * Load FontAwesome Pro icon library with webfonts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue FontAwesome Pro v7
 * 
 * Includes:
 * - fontawesome.css (base styles)
 * - solid.css (solid icons)
 * - regular.css (regular icons)
 * - light.css (light icons)
 * 
 * Customize by changing the CSS files loaded
 */
function enqueue_fontawesome_pro()
{
    $theme_uri  = get_stylesheet_directory_uri();
    $theme_path = get_stylesheet_directory();
    $fa_dir     = $theme_path . '/assets/fontawesome-pro-v7';
    $fa_uri     = $theme_uri . '/assets/fontawesome-pro-v7';

    // Check if FontAwesome directory exists
    if (!is_dir($fa_dir)) {
        return;
    }

    // Load main FontAwesome styles (required)
    wp_enqueue_style(
        'fontawesome-pro',
        $fa_uri . '/css/fontawesome.css',
        array(),
        null,
        'all'
    );

    // Load solid style variant
    $solid_css = $fa_dir . '/css/solid.css';
    if (file_exists($solid_css)) {
        wp_enqueue_style(
            'fontawesome-pro-solid',
            $fa_uri . '/css/solid.css',
            array('fontawesome-pro'),
            filemtime($solid_css),
            'all'
        );
    }

    // Load regular style variant
    $regular_css = $fa_dir . '/css/regular.css';
    if (file_exists($regular_css)) {
        wp_enqueue_style(
            'fontawesome-pro-regular',
            $fa_uri . '/css/regular.css',
            array('fontawesome-pro'),
            filemtime($regular_css),
            'all'
        );
    }

    // Load light style variant
    $light_css = $fa_dir . '/css/light.css';
    if (file_exists($light_css)) {
        wp_enqueue_style(
            'fontawesome-pro-light',
            $fa_uri . '/css/light.css',
            array('fontawesome-pro'),
            filemtime($light_css),
            'all'
        );
    }

    // Load brands style variant
    $brands_css = $fa_dir . '/css/brands.css';
    if (file_exists($brands_css)) {
        wp_enqueue_style(
            'fontawesome-pro-brands',
            $fa_uri . '/css/brands.css',
            array('fontawesome-pro'),
            filemtime($brands_css),
            'all'
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_fontawesome_pro');
