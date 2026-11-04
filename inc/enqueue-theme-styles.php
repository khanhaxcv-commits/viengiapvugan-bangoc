<?php

/**
 * Enqueue Theme Styles
 *
 * Load child theme CSS files with proper dependency management.
 *
 * Flatsome parent stylesheet is loaded automatically by the parent theme.
 *
 * Child theme load order:
 *
 * 1. reset.css
 * ↓
 * 2. tailwind.css
 * ↓
 * 3. style.css
 * ↓
 * 4. global.css
 * ↓
 * 5. header.css
 * ↓
 * 6. flatsome-mobile-menu.css
 * ↓
 * 7. flatsome-desktop-menu.css
 * ↓
 * 8. footer.css
 * ↓
 * 9. page-specific CSS
 * ↓
 * 10. customize.css
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper: Enqueue CSS file with automatic versioning.
 *
 * Uses file modification time as version for cache busting.
 *
 * @param string $handle CSS handle.
 * @param string $file   Relative path to file in /assets/css/ folder.
 * @param array  $deps   CSS dependencies.
 * @param string $media  CSS media condition.
 *
 * @return bool True if file exists and was enqueued, false otherwise.
 */
function enqueue_css_file($handle, $file, $deps = array(), $media = 'all')
{
    $path = get_stylesheet_directory() . '/assets/css/' . $file;
    $uri  = get_stylesheet_directory_uri() . '/assets/css/' . $file;

    if (!file_exists($path)) {
        return false;
    }

    wp_enqueue_style(
        $handle,
        $uri,
        $deps,
        filemtime($path),
        $media
    );

    return true;
}

/**
 * Enqueue all child theme styles.
 *
 * Load reset, Tailwind, main child theme stylesheet, global styles,
 * menu styles, page-specific styles and final custom overrides.
 */
function enqueue_styles()
{
    $style_path = get_stylesheet_directory() . '/style.css';

    /*
     * 1. Reset CSS
     *
     * Chạy sau CSS chính của Flatsome.
     */
    $has_reset = enqueue_css_file(
        'reset-css',
        'reset.css',
        array('flatsome-main')
    );

    $reset_deps = $has_reset
        ? array('reset-css')
        : array('flatsome-main');

    /*
     * 2. Tailwind CSS
     *
     * Chạy ngay sau reset.css và trước style.css.
     */
    $has_tailwind = enqueue_css_file(
        'tailwind-css',
        'tailwind.css',
        $reset_deps
    );

    $tailwind_deps = $has_tailwind
        ? array('tailwind-css')
        : $reset_deps;

    /*
     * 3. Main child theme stylesheet
     *
     * style.css là nền tảng toàn site, chứa :root, token và biến màu.
     * Chạy sau Tailwind để có thể ghi đè khi cần.
     */
    wp_enqueue_style(
        'flatsome-child-style',
        get_stylesheet_uri(),
        $tailwind_deps,
        file_exists($style_path) ? filemtime($style_path) : null
    );

    /*
     * 4. Global CSS
     *
     * global.css chứa class chung và sử dụng biến từ style.css.
     */
    $has_global = enqueue_css_file(
        'global-css',
        'global.css',
        array('flatsome-child-style')
    );

    $global_deps = $has_global
        ? array('global-css')
        : array('flatsome-child-style');

    /*
     * 5. Header CSS
     */
    $has_header = enqueue_css_file(
        'header-css',
        'header.css',
        $global_deps
    );

    $header_deps = $has_header
        ? array('header-css')
        : $global_deps;

    /*
     * 6. Flatsome menu CSS
     *
     * Tách riêng menu mobile và desktop để dễ quản lý
     * và tái sử dụng cho các dự án Flatsome.
     */
    $layout_handles = array();

    if ($has_header) {
        $layout_handles[] = 'header-css';
    }

    if (
        enqueue_css_file(
            'flatsome-mobile-menu-css',
            'flatsome-mobile-menu.css',
            $header_deps,
            '(max-width: 849px)'
        )
    ) {
        $layout_handles[] = 'flatsome-mobile-menu-css';
    }

    if (
        enqueue_css_file(
            'flatsome-desktop-menu-css',
            'flatsome-desktop-menu.css',
            $header_deps,
            '(min-width: 850px)'
        )
    ) {
        $layout_handles[] = 'flatsome-desktop-menu-css';
    }

    /*
     * 7. Footer CSS
     */
    if (
        enqueue_css_file(
            'footer-css',
            'footer.css',
            $global_deps
        )
    ) {
        $layout_handles[] = 'footer-css';
    }


    /*
     * 9. Page-specific styles
     */
    $page_handles = array();

    if (is_front_page() || is_page('trang-chu-1')) {
        if (
            enqueue_css_file(
                'trang-chu-1-css',
                'trang-chu-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'trang-chu-1-css';
        }
    }

    if (is_page('doi-ngu-bac-si-1')) {
        if (
            enqueue_css_file(
                'doi-ngu-bac-si-1-css',
                'doi-ngu-bac-si-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'doi-ngu-bac-si-1-css';
        }
    }

    if (is_page('chi-tiet-bac-si') || is_page('bac-si-nguyen-van-minh')) {
        if (
            enqueue_css_file(
                'chi-tiet-bac-si-css',
                'chi-tiet-bac-si.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'chi-tiet-bac-si-css';
        }
    }

    if (is_page('chi-tiet-bac-si-1')) {
        if (
            enqueue_css_file(
                'chi-tiet-bac-si-1-css',
                'chi-tiet-bac-si-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'chi-tiet-bac-si-1-css';
        }
    }

    if (is_page('lien-he-1')) {
        if (
            enqueue_css_file(
                'lien-he-1-css',
                'lien-he-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'lien-he-1-css';
        }
    }

    if (is_page('co-so-vat-chat-1')) {
        if (
            enqueue_css_file(
                'co-so-vat-chat-1-css',
                'co-so-vat-chat-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'co-so-vat-chat-1-css';
        }
    }

    if (is_page('gioi-thieu-1')) {
        if (
            enqueue_css_file(
                'gioi-thieu-1-css',
                'gioi-thieu-1.css',
                $global_deps
            )
        ) {
            $page_handles[] = 'gioi-thieu-1-css';
        }
    }

    /*
     * 10. Customize CSS
     *
     * customize.css là file override cuối cùng.
     * File này chạy sau CSS layout và CSS theo từng trang.
     */
    $customize_deps = array_unique(
        array_merge(
            array('flatsome-child-style'),
            $global_deps,
            $layout_handles,
            $page_handles
        )
    );

    enqueue_css_file(
        'customize-css',
        'customize.css',
        $customize_deps
    );
}

add_action('wp_enqueue_scripts', 'enqueue_styles', 1);
