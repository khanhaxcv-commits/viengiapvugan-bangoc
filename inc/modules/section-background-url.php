<?php
/**
 * External background images for the native Section element, including its preview.
 */

defined('ABSPATH') || exit;

function cvn_section_background_urls($atts)
{
    $urls = array();
    foreach (array('bg_url', 'bg_url_mobile') as $key) {
        $url = isset($atts[$key]) ? trim((string) $atts[$key]) : '';
        $urls[$key] = preg_match('~^https?://~i', $url)
            ? esc_url_raw($url, array('http', 'https')) : '';
    }
    return $urls;
}

function cvn_section_background_url_render($output, $tag, $atts)
{
    if ($tag !== 'section' || !$output) {
        return $output;
    }

    $urls = cvn_section_background_urls($atts);
    $desktop = $urls['bg_url'] ?: $urls['bg_url_mobile'];
    if (!$desktop) {
        return $output;
    }

    $picture = '<picture>';
    if ($urls['bg_url_mobile']) {
        $picture .= '<source media="(max-width: 849px)" srcset="' . esc_url($urls['bg_url_mobile']) . '">';
    }
    $picture .= '<img class="bg" src="' . esc_url($desktop) . '" alt="" decoding="async" loading="eager" fetchpriority="high"></picture>';

    // Replace only the outer Section's image; retain overlays, videos and nested Sections.
    $output = preg_replace_callback(
        '~(<div\b[^>]*class="section-bg(?: [^"]*)?"[^>]*>)(?:\s*<img\b[^>]*>)?~',
        function ($match) use ($picture) {
            return $match[1] . $picture;
        },
        $output,
        1,
        $count
    );

    return $count ? preg_replace('/<section\b/', '<section data-section-url-bg="true"', $output, 1) : $output;
}
add_filter('do_shortcode_tag', 'cvn_section_background_url_render', 10, 3);

function cvn_section_background_url_builder($data)
{
    $fields = array(
        'bg_url' => array(
            'type'        => 'textfield',
            'heading'     => 'Link ảnh PC',
            'description' => 'Dán link ảnh trực tiếp. Có link sẽ ưu tiên hơn ảnh trong Select media.',
            'default'     => '',
        ),
        'bg_url_mobile' => array(
            'type'        => 'textfield',
            'heading'     => 'Link ảnh mobile',
            'description' => 'Dùng dưới 850px. Để trống sẽ dùng ảnh PC.',
            'default'     => '',
        ),
    );
    $background = array();
    foreach ($data['options']['background_options']['options'] as $key => $option) {
        $background[$key] = $option;
        if ($key === 'bg') {
            $background += $fields;
        }
    }
    $data['options']['background_options']['options'] = $background;
    $data['styles']['section-background-url-css'] = get_stylesheet_directory_uri() . '/assets/css/components/section-background-url.css';

    $template = $data['template'];
    $template = preg_replace(
        '/<section\b/',
        '<section ng-attr-data-section-url-bg="{{ (shortcode.options.bgUrl || shortcode.options.bgUrlMobile) ? \'true\' : undefined }}"',
        $template,
        1
    );
    $template = str_replace(
        'ng-if="shortcode.options.bg"',
        'ng-if="shortcode.options.bg && !shortcode.options.bgUrl && !shortcode.options.bgUrlMobile"',
        $template
    );
    $picture = <<<'HTML'
<picture ng-if="shortcode.options.bgUrl || shortcode.options.bgUrlMobile">
  <source ng-if="shortcode.options.bgUrlMobile" media="(max-width: 849px)" ng-srcset="{{ shortcode.options.bgUrlMobile }}">
  <img class="bg" ng-src="{{ shortcode.options.bgUrl || shortcode.options.bgUrlMobile }}" ng-style="{ 'objectPosition': shortcode.options.bgPos }" alt="">
</picture>
HTML;
    $data['template'] = preg_replace_callback(
        '~<div\b[^>]*class="section-bg(?: [^"]*)?"[^>]*>~',
        function ($match) use ($picture) {
            return $match[0] . $picture;
        },
        $template,
        1
    );

    return $data;
}
add_filter('ux_builder_shortcode_data_section', 'cvn_section_background_url_builder');

function cvn_section_background_url_styles()
{
    $file = '/assets/css/components/section-background-url.css';

    wp_enqueue_style(
        'section-background-url-css',
        get_stylesheet_directory_uri() . $file,
        array('flatsome-main'),
        filemtime(get_stylesheet_directory() . $file)
    );
}
add_action('wp_enqueue_scripts', 'cvn_section_background_url_styles', 30);
