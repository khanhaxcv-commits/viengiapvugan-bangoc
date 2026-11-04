<?php
/**
 * External image URLs for the native Image element and its Builder preview.
 */

defined('ABSPATH') || exit;

/**
 * Image uses server-rendered previews, so its frontend filter also serves Builder.
 */
function cvn_ux_image_url_builder($data)
{
    $fields = array(
        'image_url' => array(
            'type'        => 'textfield',
            'heading'     => 'Link ảnh PC',
            'description' => 'Dán link ảnh trực tiếp. Có link sẽ ưu tiên hơn ảnh đã chọn phía trên.',
            'default'     => '',
        ),
        'image_url_mobile' => array(
            'type'        => 'textfield',
            'heading'     => 'Link ảnh mobile',
            'description' => 'Dùng dưới 850px. Để trống sẽ dùng ảnh PC.',
            'default'     => '',
        ),
    );
    $options = array();
    foreach ($data['options'] as $key => $option) {
        $options[$key] = $option;
        if ($key === 'id') {
            $options += $fields;
        }
    }
    $data['options'] = $options;

    return $data;
}
add_filter('ux_builder_shortcode_data_ux_image', 'cvn_ux_image_url_builder');

function cvn_ux_image_url_value($atts, $key)
{
    $url = isset($atts[$key]) && is_string($atts[$key]) ? trim($atts[$key]) : '';

    return preg_match('~^https?://~i', $url)
        ? esc_url_raw($url, array('http', 'https')) : '';
}

function cvn_ux_image_url_render($output, $tag, $atts)
{
    if ($tag !== 'ux_image' || !is_array($atts)) {
        return $output;
    }

    $desktop = cvn_ux_image_url_value($atts, 'image_url');
    $mobile = cvn_ux_image_url_value($atts, 'image_url_mobile');
    if (!$desktop && !$mobile) {
        return $output;
    }

    // Native Image returns a placeholder when no media ID is selected.
    // Reuse its renderer so width, height, links, overlays and animation survive.
    if (stripos($output, '<img') === false) {
        $desktop = $desktop ?: $mobile;
        $render_atts = $atts;
        $render_atts['id'] = $desktop;
        $render_atts['lightbox_caption'] = '';
        if (isset($render_atts['caption']) && $render_atts['caption'] === 'true') {
            $render_atts['caption'] = '';
        }
        $output = ux_image($render_atts);
    }

    $output = preg_replace_callback(
        '~<img\b[^>]*>~i',
        function ($match) use ($desktop, $mobile) {
            $image = new WP_HTML_Tag_Processor($match[0]);
            $image->next_tag();
            if ($desktop) {
                $image->set_attribute('src', $desktop);
                // Media-library candidates and lazy-load URLs must not override the PC URL.
                foreach (array('srcset', 'sizes', 'width', 'height', 'data-src', 'data-srcset', 'data-sizes', 'data-lazy-src', 'data-lazy-srcset') as $attribute) {
                    $image->remove_attribute($attribute);
                }
            }
            $picture = '<picture data-image-url="true">';
            if ($mobile) {
                $picture .= '<source media="(max-width: 849px)" srcset="' . esc_url($mobile) . '">';
            }

            return $picture . $image->get_updated_html() . '</picture>';
        },
        $output,
        1
    );

    // Keep explicit links intact; a native lightbox should open the custom PC image.
    if ($desktop && empty($atts['link']) && !empty($atts['lightbox'])) {
        $html = new WP_HTML_Tag_Processor($output);
        if ($html->next_tag(array('tag_name' => 'A', 'class_name' => 'image-lightbox'))) {
            $html->set_attribute('href', $desktop);
            $output = $html->get_updated_html();
        }
    }

    return $output;
}
add_filter('do_shortcode_tag', 'cvn_ux_image_url_render', 10, 3);
