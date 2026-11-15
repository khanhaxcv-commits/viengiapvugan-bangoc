<?php
/**
 * Shared module: breadcrumbs
 * Version: 2.0.0
 * Public API: ishen_shared_get_breadcrumb_html($args = array())
 */

defined('ABSPATH') || exit;

/** One separator markup shared by every breadcrumb provider. */
function ishen_shared_get_breadcrumb_separator($class = 'divider')
{
    return '<span class="' . esc_attr($class) . '"><i class="fa-classic fa-light fa-slash-forward" aria-hidden="true"></i></span>';
}

/** Return the supported context without requiring WooCommerce to be active. */
function ishen_shared_get_breadcrumb_context()
{
    if (function_exists('is_shop') && is_shop()) {
        return 'shop';
    }
    if (function_exists('is_product_category') && is_product_category()) {
        return 'product_category';
    }
    if (is_singular('product')) {
        return 'single_product';
    }
    if (is_category()) {
        return 'post_category';
    }
    if (is_author()) {
        return 'author';
    }
    if (is_singular('post')) {
        return 'single_post';
    }
    if (is_page() && !is_front_page()) {
        return 'page';
    }
    return '';
}

/** Build term ancestors in order, skipping invalid terms and URLs. */
function ishen_shared_get_breadcrumb_term_items($term, $link_current = false)
{
    if (!$term instanceof WP_Term) {
        return array();
    }

    $items = array();
    $ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
    foreach ($ancestors as $ancestor_id) {
        $ancestor = get_term($ancestor_id, $term->taxonomy);
        if (!$ancestor instanceof WP_Term) {
            continue;
        }
        $url = get_term_link($ancestor);
        if (!is_wp_error($url)) {
            $items[] = array($ancestor->name, $url);
        }
    }

    $url = $link_current ? get_term_link($term) : '';
    $items[] = array($term->name, is_wp_error($url) ? '' : $url);
    return $items;
}

/** Native fallback. WooCommerce retains its term selection and trail filters. */
function ishen_shared_get_breadcrumb_items($context, $term = null, $home_label = 'Trang chủ')
{
    if (in_array($context, array('single_product', 'product_category', 'shop'), true)) {
        if (!class_exists('WC_Breadcrumb')) {
            return array();
        }
        $trail = new WC_Breadcrumb();
        if ($home_label !== '') {
            $trail->add_crumb($home_label, apply_filters('woocommerce_breadcrumb_home_url', home_url('/')));
        }
        return $trail->generate();
    }

    if (!in_array($context, array('single_post', 'post_category', 'page', 'author'), true)) {
        return array();
    }
    $items = $home_label !== '' ? array(array($home_label, home_url('/'))) : array();

    if ($context === 'author') {
        $author = get_queried_object();
        if (!$author instanceof WP_User) {
            return array();
        }
        $items[] = array('Tác giả: ' . $author->display_name, '');
        return $items;
    }

    if ($context === 'post_category') {
        $term = $term ?: get_queried_object();
        return $term instanceof WP_Term && $term->taxonomy === 'category'
            ? array_merge($items, ishen_shared_get_breadcrumb_term_items($term))
            : array();
    }

    $post_id = get_queried_object_id();
    if ($context === 'single_post') {
        $categories = get_the_category($post_id);
        if (!empty($categories)) {
            $items = array_merge($items, ishen_shared_get_breadcrumb_term_items($categories[0], true));
        }
    } else {
        foreach (array_reverse(get_post_ancestors($post_id)) as $ancestor_id) {
            $items[] = array(get_the_title($ancestor_id), get_permalink($ancestor_id));
        }
    }

    $items[] = array(get_the_title($post_id), '');
    return $items;
}

/**
 * Return breadcrumb HTML. This function does not echo or register display hooks.
 * HTML wrappers/separators are trusted theme configuration, not visitor input.
 */
function ishen_shared_get_breadcrumb_html($args = array())
{
    $args = wp_parse_args($args, array(
        'context' => 'auto',
        'term' => null,
        'items' => null,
        'class' => 'breadcrumbs',
        'separator' => null,
        'home_label' => 'Trang chủ',
        'use_rank_math' => true,
        'wrap_before' => null,
        'wrap_after' => '</nav>',
        'before' => '',
        'after' => '',
        'current_before' => '<span>',
        'current_after' => '</span>',
        'show_current' => true,
    ));
    $context = $args['context'] === 'auto' ? ishen_shared_get_breadcrumb_context() : $args['context'];
    if ($args['items'] === null && !in_array($context, array(
        'single_post', 'post_category', 'single_product', 'product_category', 'shop', 'page', 'author',
    ), true)) {
        return '';
    }
    $wrap_before = $args['wrap_before'] === null
        ? '<nav class="' . esc_attr($args['class']) . '" aria-label="Breadcrumb">'
        : $args['wrap_before'];
    $separator = $args['separator'] === null ? ishen_shared_get_breadcrumb_separator() : $args['separator'];
    $is_woocommerce = in_array($context, array('single_product', 'product_category', 'shop'), true);
    $term_matches_query = !$args['term'] || (
        $args['term'] instanceof WP_Term && (int) $args['term']->term_id === get_queried_object_id()
    );

    if ($args['items'] === null && $context !== '' && !$is_woocommerce && $term_matches_query
        && $args['use_rank_math'] && function_exists('rank_math_get_breadcrumbs')) {
        $rank_math_args = array(
            'wrap_before' => $wrap_before,
            'wrap_after' => $args['wrap_after'],
            'separator' => $separator,
        );
        $html = trim((string) rank_math_get_breadcrumbs($rank_math_args));
        if ($html !== '') {
            return $html;
        }
    }

    $items = $args['items'] === null
        ? ishen_shared_get_breadcrumb_items($context, $args['term'], $args['home_label'])
        : $args['items'];
    if (!is_array($items) || !$items) {
        return '';
    }

    $items = array_values($items);
    $parts = array();
    $last = count($items) - 1;
    foreach ($items as $index => $item) {
        if (!is_array($item) || !isset($item[0]) || ($index === $last && !$args['show_current'])) {
            continue;
        }
        $label = esc_html(wp_strip_all_tags((string) $item[0]));
        $url = isset($item[1]) && !is_wp_error($item[1]) ? (string) $item[1] : '';
        $content = $url !== '' && $index !== $last
            ? '<a href="' . esc_url($url) . '">' . $label . '</a>'
            : $args['current_before'] . $label . $args['current_after'];
        $parts[] = $args['before'] . $content . $args['after'];
    }
    if (!$parts) {
        return '';
    }
    return $wrap_before . implode($separator, $parts) . $args['wrap_after'];
}
