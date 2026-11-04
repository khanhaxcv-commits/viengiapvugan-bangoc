<?php
// Add custom Theme Functions here

function shortcode_parent_category_posts() {
    ob_start();

    $parent_categories = get_categories(array(
        'parent' => 0,
        'hide_empty' => true,
    ));

    $parent_cat_ids = wp_list_pluck($parent_categories, 'term_id');
  	$current_post_id = get_the_ID(); // Lấy ID bài viết hiện tại

    $posts = get_posts(array(
    'category__in' => $parent_cat_ids,
    'posts_per_page' => 6,
    'post_status' => 'publish',
    'post__not_in' => array($current_post_id), // Loại trừ bài hiện tại
));

    if ($posts) {
        echo '<div class="parent-category-posts-grid">';
      	echo '<p style="font-weight:600">Bài viết mới nhất</p>';
        echo '<div class="post-grid">';
        foreach ($posts as $post) {
            setup_postdata($post);
            $thumbnail = get_the_post_thumbnail($post->ID, 'medium');
            $title = get_the_title($post);
            $permalink = get_permalink($post);
            $categories = get_the_category($post->ID);

            $category_names = array();
            foreach ($categories as $cat) {
                if ($cat->parent == 0) {
                    $category_names[] = esc_html($cat->name);
                }
            }

            echo '<div class="post-item">';
            echo '<a href="' . esc_url($permalink) . '" class="post-inner">';
                if ($thumbnail) {
                    echo '<div class="post-thumb">' . $thumbnail . '</div>';
                }
                echo '<div class="post-info">';
                    echo '<p class="post-title">' . esc_html($title) . '</p>';
                echo '</div>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div></div>';
        wp_reset_postdata();
    } else {
        echo '<p>No posts found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('parent_category_posts', 'shortcode_parent_category_posts');

add_filter('the_archive_description', 'hien_thi_mo_ta_danh_muc_custom', 20);

function hien_thi_mo_ta_danh_muc_custom($description) {
    if (is_category()) {
        $cat_id = get_queried_object_id();
        $allowed_ids = [12]; // ← Chỉ hiển thị cho các ID này

        if (in_array($cat_id, $allowed_ids)) {
            $description = '<div class="mo-ta-danh-muc">' . category_description() . '</div>';
        } else {
            $description = ''; // ← Ẩn mô tả với các danh mục khác
        }
    }
    return $description;
}




// Nạp các nhóm chức năng qua bootstrap.
$theme_includes = array(
    'inc/cleanup/bootstrap.php',
    'inc/assets/bootstrap.php',
    'inc/blog/bootstrap.php',
    'inc/modules/bootstrap.php',
    'inc/integrations/bootstrap.php',
);

foreach ($theme_includes as $theme_include) {
    $theme_file = get_stylesheet_directory() . '/' . $theme_include;

    if (file_exists($theme_file)) {
        require_once $theme_file;
    }
}
