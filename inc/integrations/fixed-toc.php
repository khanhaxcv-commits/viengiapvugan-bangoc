<?php
/** Đồng bộ mục lục Fixed TOC với giao diện và header của bài viết. */
defined('ABSPATH') || exit;

add_filter('fixedtoc_header_title', function ($title) {
    return is_singular('post') ? 'Mục lục bài viết' : $title;
});

add_filter('fixedtoc_background_opacity', function ($opacity) {
    return is_singular('post') ? 1 : $opacity;
});

add_action('wp_enqueue_scripts', function () {
    if (!is_singular('post') || !wp_script_is('fixedtoc-js', 'enqueued')) {
        return;
    }

    enqueue_css_file('vgv-fixed-toc', 'pages/fixed-toc.css', array('fixedtoc-style', 'single-post-css'));

    // Chạy sau dữ liệu localize, trước plugin; không thay đổi cấu hình trong database.
    wp_add_inline_script('fixedtoc-js', 'Object.assign(window.fixedtocOption, ' . wp_json_encode(array(
        'fixedMenu' => '.header-wrapper',
        'scrollOffset' => 16,
        'fixedOffsetY' => 12,
        'fixedPosition' => 'top-right',
        'contentsFixedHeight' => 420,
        'isClickableHeader' => '1',
    )) . ');', 'before');

    enqueue_script_file('vgv-fixed-toc', 'fixed-toc.js', array('jquery', 'fixedtoc-js'));
}, 30);
