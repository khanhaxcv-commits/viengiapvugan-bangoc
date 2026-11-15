<?php

/** Giao diện breadcrumb, chia sẻ và bình luận của bài viết. */
defined('ABSPATH') || exit;

class Vgv_Site_Post
{
    public static function init()
    {
        add_action('flatsome_after_header', array(__CLASS__, 'render_breadcrumb'), 6);
        add_filter('comments_template', array(__CLASS__, 'comments_template'));
    }

    public static function render_breadcrumb()
    {
        if (!is_singular('post') || !function_exists('ishen_shared_get_breadcrumb_html')) {
            return;
        }

        $html = ishen_shared_get_breadcrumb_html(array(
            'context' => 'single_post',
            'class' => 'm-0 !flex min-w-0 max-w-full items-center gap-2 overflow-hidden whitespace-nowrap text-sm font-medium !text-slate-700 [&_p]:m-0 [&_p]:!flex [&_p]:min-w-0 [&_p]:w-full [&_p]:items-center [&_p]:gap-2 [&_a]:min-w-0 [&_a]:truncate [&_a]:!text-purple-700 [&_a:first-child]:shrink-0 [&_a:hover]:!text-purple-900 [&_span]:min-w-0 [&_span]:truncate',
            'separator' => ishen_shared_get_breadcrumb_separator('shrink-0 !text-slate-400'),
            'current_before' => '<span class="min-w-0 truncate !text-slate-900" aria-current="page">',
        ));

        if ($html !== '') {
            get_template_part('template-parts/posts/partials/breadcrumb', null, array('html' => $html));
        }
    }

    public static function comments_template($template)
    {
        return is_singular('post')
            ? get_stylesheet_directory() . '/template-parts/posts/comments.php'
            : $template;
    }

    /** Giữ liên kết chia sẻ native Flatsome, đổi icon và hình thức theo mẫu CopiersVN. */
    public static function get_share_html()
    {
        if (!shortcode_exists('share')) {
            return '';
        }

        $html = do_shortcode('[share style="small" tooltip="false"]');

        if (!class_exists('WP_HTML_Tag_Processor')) {
            return $html;
        }

        $icons = array(
            'icon-facebook' => 'fa-brands fa-facebook-f',
            'icon-twitter' => 'fa-brands fa-twitter',
            'icon-x' => 'fa-brands fa-x-twitter',
            'icon-pinterest' => 'fa-brands fa-pinterest-p',
            'icon-linkedin' => 'fa-brands fa-linkedin-in',
            'icon-whatsapp' => 'fa-brands fa-whatsapp',
            'icon-envelop' => 'fa-light fa-envelope',
        );
        $processor = new WP_HTML_Tag_Processor($html);

        while ($processor->next_tag()) {
            if ($processor->has_class('social-icons')) {
                $processor->add_class('!flex flex-wrap items-center gap-2 [&.social-icons_a.icon:last-child]:!mt-0');
            }

            if ($processor->get_tag() === 'A') {
                if ($processor->has_class('show-for-medium')) {
                    $processor->add_class('min-[850px]:!hidden');
                }
                $processor->add_class('!m-0 !inline-flex h-10 w-10 min-h-0 shrink-0 items-center justify-center rounded-full !border !border-solid !border-[var(--border-ui)] !bg-[var(--surface-bg-soft)] !p-0 !text-slate-500 transition-colors hover:!border-purple-300 hover:!bg-purple-100 hover:!text-purple-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600');
            }

            if ($processor->get_tag() === 'I') {
                foreach ($icons as $original => $replacement) {
                    if ($processor->has_class($original)) {
                        $processor->remove_class($original);
                        foreach (explode(' ', $replacement) as $icon_class) {
                            $processor->add_class($icon_class);
                        }
                        break;
                    }
                }
                $processor->add_class('!text-sm');
            }
        }

        return $processor->get_updated_html();
    }
}

Vgv_Site_Post::init();
