<?php

/**
 * Enqueue Theme Scripts
 *
 * Load custom theme scripts from /assets/js/.
 *
 * Load order:
 *
 * 1. flatsome-mobile-menu.js
 * ↓
 * 2. flatsome-desktop-menu.js
 * ↓
 * 3. theme.js
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('enqueue_script_file')) {
    function enqueue_script_file($handle, $file, $deps = array(), $in_footer = true)
    {
        $path = get_stylesheet_directory() . '/assets/js/' . $file;
        $uri  = get_stylesheet_directory_uri() . '/assets/js/' . $file;

        if (!file_exists($path)) {
            return false;
        }

        wp_enqueue_script(
            $handle,
            $uri,
            $deps,
            filemtime($path),
            $in_footer
        );

        return true;
    }
}

if (!function_exists('get_available_script_deps')) {
    function get_available_script_deps($handles)
    {
        $deps = array();

        foreach ($handles as $handle) {
            if (
                wp_script_is($handle, 'registered') ||
                wp_script_is($handle, 'enqueued')
            ) {
                $deps[] = $handle;
            }
        }

        return array_unique($deps);
    }
}

if (!function_exists('enqueue_theme_scripts')) {
    function enqueue_theme_scripts()
    {
        $vendor_deps = get_available_script_deps(
            array(
                'jquery',
                'bootstrap-js',
                'swiper-js',
                'gsap-js',
                'scrolltrigger-js',
                'splittext-js',
                'wow-js',
                'validator-js',
                'isotope-js',
                'waypoints-js',
                'counterup-js',
                'magnific-popup-js',
                'ytplayer-js',
                'slicknav-js',
                'parallaxie-js',
                'smoothscroll-js',
                'magiccursor-js',
            )
        );

        /**
         * 1. flatsome-mobile-menu.js
         *
         * Location:
         * /assets/js/flatsome-mobile-menu.js
         */
        enqueue_script_file(
            'flatsome-mobile-menu-js',
            'flatsome-mobile-menu.js',
            array('jquery')
        );

        /**
         * 2. flatsome-desktop-menu.js
         *
         * Location:
         * /assets/js/flatsome-desktop-menu.js
         */
        enqueue_script_file(
            'flatsome-desktop-menu-js',
            'flatsome-desktop-menu.js',
            array('jquery')
        );

        $menu_deps = get_available_script_deps(
            array(
                'flatsome-mobile-menu-js',
                'flatsome-desktop-menu-js',
            )
        );

        /**
         * 3. theme.js
         *
         * Location:
         * /assets/js/theme.js
         */
        enqueue_script_file(
            'theme-js',
            'theme.js',
            array_unique(
                array_merge(
                    $vendor_deps,
                    $menu_deps
                )
            )
        );
    }
}

add_action('wp_enqueue_scripts', 'enqueue_theme_scripts', 40);
