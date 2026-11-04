<?php

/**
 * Enqueue External Assets
 *
 * Load CDN vendor/library assets.
 *
 * Load order:
 *
 * 1. bootstrap.bundle.min.js
 * ↓
 * 2. swiper-bundle.min.css
 * ↓
 * 3. swiper-bundle.min.js
 * ↓
 * 4. gsap.min.js
 * ↓
 * 5. ScrollTrigger.min.js
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('enqueue_external_style')) {
    function enqueue_external_style($handle, $src, $deps = array(), $version = null, $media = 'all')
    {
        wp_enqueue_style(
            $handle,
            $src,
            $deps,
            $version,
            $media
        );
    }
}

if (!function_exists('enqueue_external_script')) {
    function enqueue_external_script($handle, $src, $deps = array(), $version = null, $in_footer = true)
    {
        wp_enqueue_script(
            $handle,
            $src,
            $deps,
            $version,
            $in_footer
        );
    }
}

if (!function_exists('enqueue_external_assets')) {
    function enqueue_external_assets()
    {
        /**
         * 1. bootstrap.bundle.min.js
         *
         * CDN:
         * Bootstrap v5.3.3
         */
        enqueue_external_script(
            'bootstrap-js',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            array(),
            '5.3.3'
        );

        /**
         * 2. swiper-bundle.min.css
         *
         * CDN:
         * Swiper v11.0.5
         */
        enqueue_external_style(
            'swiper-css',
            'https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.css',
            array(),
            '11.0.5'
        );

        /**
         * 3. swiper-bundle.min.js
         *
         * CDN:
         * Swiper v11.0.5
         */
        enqueue_external_script(
            'swiper-js',
            'https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.js',
            array(),
            '11.0.5'
        );

        /**
         * 4. gsap.min.js
         *
         * CDN:
         * GSAP v3.7.1
         */
        enqueue_external_script(
            'gsap-js',
            'https://cdn.jsdelivr.net/npm/gsap@3.7.1/dist/gsap.min.js',
            array(),
            '3.7.1'
        );

        /**
         * 5. ScrollTrigger.min.js
         *
         * CDN:
         * GSAP ScrollTrigger v3.7.1
         */
        enqueue_external_script(
            'scrolltrigger-js',
            'https://cdn.jsdelivr.net/npm/gsap@3.7.1/dist/ScrollTrigger.min.js',
            array('gsap-js'),
            '3.7.1'
        );
    }
}

add_action('wp_enqueue_scripts', 'enqueue_external_assets', 20);
