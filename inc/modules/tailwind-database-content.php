<?php

/**
 * Export nội dung WordPress Database để Tailwind CSS quét class.
 *
 * Bao gồm:
 * - Flatsome UX Builder
 * - Trang và bài viết
 * - Sản phẩm WooCommerce
 * - UX Blocks
 * - Shortcode
 * - Contact Form 7
 * - HTML được lưu trong post_content
 */

defined('ABSPATH') || exit;

/**
 * Lấy đường dẫn thư mục lưu nội dung database.
 */
function get_tailwind_database_directory()
{
    return trailingslashit(get_stylesheet_directory()) . '.tailwind';
}

/**
 * Lấy đường dẫn file Tailwind quét nội dung database.
 */
function get_tailwind_database_file()
{
    return trailingslashit(get_tailwind_database_directory())
        . 'database-content.txt';
}

/**
 * Xuất toàn bộ post_content ra file văn bản.
 */
function generate_tailwind_database_content()
{
    global $wpdb;

    $directory_path = get_tailwind_database_directory();
    $file_path      = get_tailwind_database_file();

    if (!is_dir($directory_path)) {
        wp_mkdir_p($directory_path);
    }

    $contents = $wpdb->get_col(
        "
        SELECT post_content
        FROM {$wpdb->posts}
        WHERE post_content IS NOT NULL
        AND post_content != ''
        AND post_status NOT IN (
            'trash',
            'auto-draft',
            'inherit'
        )
        AND post_type NOT IN (
            'revision',
            'attachment',
            'nav_menu_item'
        )
        ORDER BY ID ASC
        "
    );

    if (!is_array($contents)) {
        $contents = [];
    }

    $contents = array_map(
        static function ($content) {
            if (!is_string($content)) {
                return '';
            }

            return html_entity_decode(
                $content,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        },
        $contents
    );

    $output = implode(
        PHP_EOL . PHP_EOL,
        $contents
    );

    $output = sprintf(
        "/* Generated: %s */%s%s",
        current_time('mysql'),
        PHP_EOL . PHP_EOL,
        $output
    );

    return file_put_contents(
        $file_path,
        $output,
        LOCK_EX
    ) !== false;
}

/**
 * Cập nhật file sau khi lưu nội dung WordPress.
 */
function update_tailwind_database_content_after_save($post_id)
{
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    generate_tailwind_database_content();
}

add_action(
    'save_post',
    'update_tailwind_database_content_after_save',
    100
);

/**
 * Cập nhật khi xóa, đưa vào thùng rác hoặc khôi phục nội dung.
 */
function refresh_tailwind_database_content()
{
    generate_tailwind_database_content();
}

add_action(
    'deleted_post',
    'refresh_tailwind_database_content'
);

add_action(
    'trashed_post',
    'refresh_tailwind_database_content'
);

add_action(
    'untrashed_post',
    'refresh_tailwind_database_content'
);

/**
 * Tạo file lần đầu nếu chưa tồn tại.
 */
function maybe_generate_tailwind_database_content()
{
    if (!file_exists(get_tailwind_database_file())) {
        generate_tailwind_database_content();
    }
}

add_action(
    'admin_init',
    'maybe_generate_tailwind_database_content'
);
