<?php

/**
 * Blog Category Archive
 *
 * Handles blog category archive hero, category image field, breadcrumbs,
 * Flatsome default title removal, category layout and category assets.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Blog_Category_Archive')) {
    class Blog_Category_Archive
    {
        const META_KEY = '_blog_category_hero_image_id';
        const NONCE_ACTION = 'save_blog_category_hero_image';
        const NONCE_NAME = 'blog_category_hero_image_nonce';

        public static function init()
        {
            add_action('wp', array(__CLASS__, 'load_category_dependencies'), 1);

            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'), 30);

            add_action('pre_get_posts', array(__CLASS__, 'restrict_child_category_query'));

            add_action('wp', array(__CLASS__, 'hide_default_archive_title'));

            add_action('flatsome_after_header', array(__CLASS__, 'render_hero'), 6);

            add_filter('theme_mod_blog_layout', array(__CLASS__, 'use_full_width_layout'));

            add_action('category_add_form_fields', array(__CLASS__, 'add_admin_field'));
            add_action('category_edit_form_fields', array(__CLASS__, 'edit_admin_field'));

            add_action('created_category', array(__CLASS__, 'save_admin_field'));
            add_action('edited_category', array(__CLASS__, 'save_admin_field'));

            add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));

            add_shortcode('blog_category_hero_content', array(__CLASS__, 'hero_content_shortcode'));
        }

        /**
         * Load helpers used only by blog category archives.
         */
        public static function load_category_dependencies()
        {
            if (!is_category()) {
                return;
            }

            $pagination_file = __DIR__ . '/blog-category-pagination.php';

            if (file_exists($pagination_file)) {
                require_once $pagination_file;
            }
        }

        /**
         * Keep child category archives scoped to that exact category.
         *
         * Parent archives retain the WordPress default behavior and may include
         * posts from descendant categories.
         */
        public static function restrict_child_category_query($query)
        {
            if (
                is_admin() ||
                !$query->is_main_query() ||
                !$query->is_category()
            ) {
                return;
            }

            $term = $query->get_queried_object();

            if (
                !$term instanceof WP_Term ||
                $term->taxonomy !== 'category' ||
                (int) $term->parent <= 0
            ) {
                return;
            }

            $query->set('tax_query', array(
                array(
                    'taxonomy'         => 'category',
                    'field'            => 'term_id',
                    'terms'            => array((int) $term->term_id),
                    'include_children' => false,
                ),
            ));
        }

        /**
         * Enqueue frontend CSS/JS for blog category archive.
         */
        public static function enqueue_assets()
        {
            if (!is_category()) {
                return;
            }

            $theme_dir = get_stylesheet_directory();
            $theme_uri = get_stylesheet_directory_uri();

            $css_path = $theme_dir . '/assets/css/blog-category.css';

            if (file_exists($css_path)) {
                wp_enqueue_style(
                    'blog-category-css',
                    $theme_uri . '/assets/css/blog-category.css',
                    array(),
                    filemtime($css_path)
                );
            }

            $js_path = $theme_dir . '/assets/js/blog-category.js';

            if (file_exists($js_path)) {
                wp_enqueue_script(
                    'blog-category-js',
                    $theme_uri . '/assets/js/blog-category.js',
                    array('jquery'),
                    filemtime($js_path),
                    true
                );
            }
        }

        /**
         * Use full-width layout for post category archive.
         */
        public static function use_full_width_layout($value)
        {
            if (is_category()) {
                return '';
            }

            return $value;
        }

        /**
         * Hide Flatsome default archive title to avoid duplicated H1.
         */
        public static function hide_default_archive_title()
        {
            if (!is_category()) {
                return;
            }

            if (function_exists('flatsome_archive_title')) {
                remove_action('flatsome_before_blog', 'flatsome_archive_title', 15);
            }
        }

        /**
         * Enqueue WordPress media uploader and admin inline script.
         */
        public static function enqueue_admin_assets($hook)
        {
            if (!in_array($hook, array('edit-tags.php', 'term.php'), true)) {
                return;
            }

            $screen = get_current_screen();

            if (!$screen || $screen->taxonomy !== 'category') {
                return;
            }

            wp_enqueue_media();
            wp_enqueue_script('jquery');

            wp_add_inline_script('jquery', self::get_admin_media_script());
        }

        /**
         * Admin media uploader script.
         */
        private static function get_admin_media_script()
        {
            return "
                jQuery(function($) {
                    $(document).on('click', '.blog-category-hero-image__upload', function(e) {
                        e.preventDefault();

                        var button = $(this);
                        var field = button.closest('.blog-category-hero-image');
                        var frame = wp.media({
                            title: 'Chọn ảnh hero danh mục',
                            button: {
                                text: 'Dùng ảnh này'
                            },
                            multiple: false
                        });

                        frame.on('select', function() {
                            var attachment = frame.state().get('selection').first().toJSON();
                            var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

                            field.find('.blog-category-hero-image__id').val(attachment.id);
                            field.find('.blog-category-hero-image__preview').html(
                                '<img src=\"' + imageUrl + '\" alt=\"\" style=\"max-width:260px;height:auto;display:block;margin-top:10px;border-radius:6px;\" />'
                            );
                            field.find('.blog-category-hero-image__remove').show();
                        });

                        frame.open();
                    });

                    $(document).on('click', '.blog-category-hero-image__remove', function(e) {
                        e.preventDefault();

                        var button = $(this);
                        var field = button.closest('.blog-category-hero-image');

                        field.find('.blog-category-hero-image__id').val('');
                        field.find('.blog-category-hero-image__preview').empty();
                        button.hide();
                    });
                });
            ";
        }

        /**
         * Render shared image field UI.
         */
        private static function render_image_field($selected_image_id = 0)
        {
            $selected_image_id = absint($selected_image_id);
            $image_url = $selected_image_id ? wp_get_attachment_image_url($selected_image_id, 'medium') : '';
?>
            <div class="blog-category-hero-image">
                <input
                    type="hidden"
                    class="blog-category-hero-image__id"
                    name="blog_category_hero_image_id"
                    value="<?php echo esc_attr($selected_image_id); ?>">

                <button type="button" class="button blog-category-hero-image__upload">
                    Chọn ảnh
                </button>

                <button
                    type="button"
                    class="button blog-category-hero-image__remove"
                    style="<?php echo $image_url ? '' : 'display:none;'; ?>">
                    Xóa ảnh
                </button>

                <div class="blog-category-hero-image__preview">
                    <?php if ($image_url) : ?>
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            alt=""
                            style="max-width:260px;height:auto;display:block;margin-top:10px;border-radius:6px;">
                    <?php endif; ?>
                </div>
            </div>
        <?php
        }

        /**
         * Add image field on category create screen.
         */
        public static function add_admin_field()
        {
            wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
            <div class="form-field term-blog-category-hero-image-wrap">
                <label>Ảnh hero danh mục</label>

                <?php self::render_image_field(); ?>

                <p>
                    Ảnh này sẽ hiển thị ở phần hero đầu trang danh mục bài viết.
                </p>
            </div>
        <?php
        }

        /**
         * Add image field on category edit screen.
         */
        public static function edit_admin_field($term)
        {
            $selected_image_id = (int) get_term_meta($term->term_id, self::META_KEY, true);

            wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
            <tr class="form-field term-blog-category-hero-image-wrap">
                <th scope="row">
                    <label>Ảnh hero danh mục</label>
                </th>
                <td>
                    <?php self::render_image_field($selected_image_id); ?>

                    <p class="description">
                        Ảnh này sẽ hiển thị ở phần hero đầu trang danh mục bài viết.
                    </p>
                </td>
            </tr>
        <?php
        }

        /**
         * Save category hero image ID.
         */
        public static function save_admin_field($term_id)
        {
            $nonce = isset($_POST[self::NONCE_NAME])
                ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]))
                : '';

            if (!$nonce || !wp_verify_nonce($nonce, self::NONCE_ACTION)) {
                return;
            }

            if (!current_user_can('manage_categories') && !current_user_can('manage_options')) {
                return;
            }

            $image_id = isset($_POST['blog_category_hero_image_id'])
                ? absint(wp_unslash($_POST['blog_category_hero_image_id']))
                : 0;

            if ($image_id > 0) {
                update_term_meta($term_id, self::META_KEY, $image_id);
            } else {
                delete_term_meta($term_id, self::META_KEY);
            }
        }

        /**
         * Get category hero image URL.
         */
        public static function get_hero_image_url($term_id)
        {
            $image_id = (int) get_term_meta($term_id, self::META_KEY, true);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

            if ($image_url) {
                return $image_url;
            }

            return get_stylesheet_directory_uri() . '/assets/images/blog-category-default.jpg';
        }

        /**
         * Render category hero after Flatsome header.
         */
        public static function render_hero()
        {
            if (!is_category()) {
                return;
            }

            $term = get_queried_object();

            if (!$term || empty($term->term_id) || is_wp_error($term)) {
                return;
            }

            // Breadcrumb is rendered inside the archive content heading.

            /*
             * Legacy category hero UI.
             *
             * Retained in the template for reference but intentionally disabled.
             */
            if (false) :
                $hero_image_url = self::get_hero_image_url((int) $term->term_id);
                $description = term_description((int) $term->term_id, 'category');
        ?>
                <section
                    class="blog-category-hero"
                    style="--blog-category-hero-image: url('<?php echo esc_url($hero_image_url); ?>');">
                    <div class="blog-category-hero__overlay"></div>

                    <div class="blog-category-hero__inner container">
                        <div class="blog-category-hero__content">
                            <?php echo self::get_breadcrumb_html($term); ?>

                            <h1 class="blog-category-hero__title">
                                <?php echo esc_html(single_cat_title('', false)); ?>
                            </h1>

                            <?php if ($description) : ?>
                                <div class="blog-category-hero__description">
                                    <?php echo wp_kses_post($description); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
        <?php
            endif;
        }

        /**
         * Render a compact breadcrumb for category archives.
         */
        public static function get_simple_breadcrumb_html($term)
        {
            $items = array(
                array(
                    'label' => 'Trang chủ',
                    'url'   => home_url('/'),
                ),
            );

            $ancestors = array_reverse(get_ancestors((int) $term->term_id, 'category'));

            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_category($ancestor_id);

                if (!$ancestor || is_wp_error($ancestor)) {
                    continue;
                }

                $ancestor_url = get_category_link($ancestor);

                if (is_wp_error($ancestor_url)) {
                    continue;
                }

                $items[] = array(
                    'label' => $ancestor->name,
                    'url'   => $ancestor_url,
                );
            }

            $items[] = array(
                'label' => $term->name,
                'url'   => '',
            );

            ob_start();
        ?>
            <nav
                class="mb-1 flex max-w-full items-center gap-1.5 overflow-x-auto whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-purple-700 sm:text-sm"
                aria-label="Breadcrumb">
                <?php foreach ($items as $index => $item) : ?>
                    <?php if ($index > 0) : ?>
                        <i
                            class="fa-light fa-chevron-right shrink-0 text-xs text-purple-300<?php echo $index > 1 ? ' hidden sm:inline-block' : ''; ?>"
                            aria-hidden="true"></i>
                    <?php endif; ?>

                    <?php if ($item['url'] !== '') : ?>
                        <a
                            href="<?php echo esc_url($item['url']); ?>"
                            class="shrink-0 transition-colors hover:text-purple-900<?php echo $index > 1 ? ' hidden sm:inline' : ''; ?>">
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    <?php else : ?>
                        <span
                            class="truncate text-purple-900<?php echo $index > 1 ? ' hidden sm:inline' : ''; ?>"
                            aria-current="page">
                            <?php echo esc_html($item['label']); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php
            return ob_get_clean();
        }

        /**
         * Get breadcrumb HTML.
         */
        public static function get_breadcrumb_html($term)
        {
            if (function_exists('rank_math_get_breadcrumbs')) {
                $breadcrumb = trim(rank_math_get_breadcrumbs(array(
                    'wrap_before' => '<nav class="breadcrumbs blog-category-hero__breadcrumbs" aria-label="Breadcrumb">',
                    'wrap_after'  => '</nav>',
                )));

                if ($breadcrumb !== '') {
                    return $breadcrumb;
                }
            }

            $items = array(
                '<a href="' . esc_url(home_url('/')) . '">Trang chủ</a>',
            );

            $ancestors = array_reverse(get_ancestors((int) $term->term_id, 'category'));

            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_category($ancestor_id);

                if (!$ancestor || is_wp_error($ancestor)) {
                    continue;
                }

                $items[] = '<a href="' . esc_url(get_category_link($ancestor)) . '">' . esc_html($ancestor->name) . '</a>';
            }

            $items[] = '<span>' . esc_html($term->name) . '</span>';

            return '<nav class="breadcrumbs blog-category-hero__breadcrumbs" aria-label="Breadcrumb">' . implode('<span class="divider">/</span>', $items) . '</nav>';
        }

        /**
         * Shortcode for custom hero content if needed.
         *
         * Usage:
         * [blog_category_hero_content]
         * [blog_category_hero_content show_breadcrumb="yes" show_description="yes" desc_words="32"]
         */
        public static function hero_content_shortcode($atts)
        {
            if (!is_category()) {
                return '';
            }

            $term = get_queried_object();

            if (!$term || empty($term->term_id) || is_wp_error($term)) {
                return '';
            }

            $atts = shortcode_atts(array(
                'desc_words'       => 32,
                'show_breadcrumb'  => 'yes',
                'show_description' => 'yes',
            ), $atts, 'blog_category_hero_content');

            $description = term_description((int) $term->term_id, 'category');
            $description = $description
                ? wp_trim_words(wp_strip_all_tags($description), absint($atts['desc_words']), '...')
                : '';

            ob_start();
        ?>
            <div class="blog-category-hero-content">
                <?php if ($atts['show_breadcrumb'] === 'yes') : ?>
                    <?php echo self::get_breadcrumb_html($term); ?>
                <?php endif; ?>

                <h1 class="blog-category-hero-content__title">
                    <?php echo esc_html(single_cat_title('', false)); ?>
                </h1>

                <?php if ($atts['show_description'] === 'yes' && $description !== '') : ?>
                    <p class="blog-category-hero-content__description">
                        <?php echo esc_html($description); ?>
                    </p>
                <?php endif; ?>
            </div>
<?php

            return ob_get_clean();
        }
    }

    Blog_Category_Archive::init();
}
