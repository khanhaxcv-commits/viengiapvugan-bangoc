<?php

/**
 * Responsive pagination for blog category queries.
 *
 * Copy this file into another Flatsome child theme, require it from
 * functions.php, then call:
 *
 * blog_category_posts_pagination();
 * blog_category_posts_pagination($custom_query);
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('blog_category_posts_pagination')) {
    /**
     * Render responsive post pagination.
     *
     * @param WP_Query|null $query Query to paginate. Defaults to the main query.
     *
     * @return void
     */
    function blog_category_posts_pagination($query = null)
    {
        if (!$query instanceof WP_Query) {
            global $wp_query;
            $query = $wp_query;
        }

        $total = isset($query->max_num_pages)
            ? (int) $query->max_num_pages
            : 0;

        if ($total <= 1) {
            return;
        }

        $current = max(1, (int) get_query_var('paged'));
        $previous_url = $current > 1
            ? get_pagenum_link($current - 1)
            : '';
        $next_url = $current < $total
            ? get_pagenum_link($current + 1)
            : '';

        $number_links = paginate_links(array(
            'current'   => $current,
            'total'     => $total,
            'mid_size'  => 1,
            'end_size'  => 1,
            'prev_next' => false,
            'type'      => 'array',
        ));

        $render_button = static function ($url, $label, $icon_class, $icon_after = false, $desktop = false) {
            $is_disabled = $url === '';
            $tag = $is_disabled ? 'span' : 'a';

            $enabled_classes = $desktop
                ? 'inline-flex gap-1 h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:border-purple-300 hover:text-purple-700'
                : 'inline-flex min-h-11 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition-colors active:bg-purple-50';

            $disabled_classes = $desktop
                ? 'inline-flex h-10 cursor-not-allowed select-none items-center justify-center rounded-xl border border-slate-200 !bg-[#E4E7EC] px-3 text-sm font-semibold text-slate-400'
                : 'inline-flex min-h-11 cursor-not-allowed select-none items-center gap-1.5 rounded-xl border border-slate-200 !bg-[#E4E7EC] px-3 text-sm font-semibold text-slate-400';

            $attributes = $is_disabled
                ? ' aria-disabled="true"'
                : ' href="' . esc_url($url) . '"';

            $icon_html = '<i class="' . esc_attr($icon_class) . '" aria-hidden="true"></i>';
?>
            <<?php echo esc_attr($tag); ?>
                class="<?php echo esc_attr($is_disabled ? $disabled_classes : $enabled_classes); ?>"
                <?php echo $attributes; ?>>
                <?php if (!$icon_after) : ?>
                    <?php echo $icon_html; ?>
                <?php endif; ?>

                <?php echo esc_html($label); ?>

                <?php if ($icon_after) : ?>
                    <?php echo $icon_html; ?>
                <?php endif; ?>
            </<?php echo esc_attr($tag); ?>>
        <?php
        };
        ?>

        <nav
            class="mt-5 border-t border-slate-200 sm:mt-10"
            aria-label="Phân trang bài viết">
            <div class="flex items-center justify-between gap-3 sm:!hidden">
                <?php $render_button($previous_url, 'Trước', 'fa-light fa-arrow-left'); ?>

                <span class="shrink-0 text-sm font-semibold text-slate-600">
                    Trang <?php echo esc_html(number_format_i18n($current)); ?>
                    / <?php echo esc_html(number_format_i18n($total)); ?>
                </span>

                <?php $render_button($next_url, 'Sau', 'fa-light fa-arrow-right', true); ?>
            </div>

            <div class="!hidden flex-wrap items-center justify-center gap-2 sm:!flex">
                <?php $render_button($previous_url, 'Trước', 'fa-light fa-arrow-left', false, true); ?>

                <?php if (is_array($number_links)) : ?>
                    <?php foreach ($number_links as $number_link) : ?>
                        <?php
                        if (strpos($number_link, 'current') !== false) {
                            $number_link = str_replace(
                                'page-numbers current',
                                'inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-purple-700 px-3 text-sm font-bold text-white shadow-sm',
                                $number_link
                            );
                        } elseif (strpos($number_link, 'dots') !== false) {
                            $number_link = str_replace(
                                'page-numbers dots',
                                'inline-flex h-10 min-w-8 items-center justify-center text-sm font-semibold text-slate-400',
                                $number_link
                            );
                        } else {
                            $number_link = str_replace(
                                'page-numbers',
                                'inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:border-purple-300 hover:text-purple-700',
                                $number_link
                            );
                        }

                        echo wp_kses_post($number_link);
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php $render_button($next_url, 'Sau', 'fa-light fa-arrow-right', true, true); ?>
            </div>
        </nav>
<?php
    }
}
