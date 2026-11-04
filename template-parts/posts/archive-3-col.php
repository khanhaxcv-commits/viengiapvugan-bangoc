<?php

/**
 * Posts category archive.
 *
 * Render all blog categories in the left sidebar and posts from the current
 * category in a responsive four-column Tailwind grid.
 */

$get_primary_category = function ($post_id) {
    if (class_exists('Blog_Post') && method_exists('Blog_Post', 'get_primary_category')) {
        return Blog_Post::get_primary_category($post_id);
    }

    $categories = get_the_category($post_id);

    if (empty($categories) || is_wp_error($categories)) {
        return null;
    }

    $category_ids = wp_list_pluck($categories, 'term_id');
    $primary_ids = array(
        (int) get_post_meta($post_id, 'rank_math_primary_category', true),
        (int) get_post_meta($post_id, '_yoast_wpseo_primary_category', true),
    );

    foreach ($primary_ids as $primary_id) {
        if ($primary_id > 0 && in_array($primary_id, $category_ids, true)) {
            $primary_category = get_category($primary_id);

            if ($primary_category && !is_wp_error($primary_category)) {
                return $primary_category;
            }
        }
    }

    return $categories[0];
};

$current_category = get_queried_object();
$current_category_id = $current_category instanceof WP_Term
    ? (int) $current_category->term_id
    : 0;

$all_categories = get_categories(array(
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
));

$category_ids = array_fill_keys(
    array_map('intval', wp_list_pluck($all_categories, 'term_id')),
    true
);
$categories_by_parent = array();

foreach ($all_categories as $category) {
    $parent_id = (int) $category->parent;

    // Keep a category visible if its parent is not part of the filtered list.
    if ($parent_id > 0 && !isset($category_ids[$parent_id])) {
        $parent_id = 0;
    }

    $categories_by_parent[$parent_id][] = $category;
}

if (!empty($categories_by_parent[0])) {
    $root_category_priorities = array(
        'danh-cho-bac-si'     => 0,
        'danh-cho-benh-nhan' => 1,
        'chua-phan-loai'      => 3,
    );
    $default_root_priority = 2;
    $root_original_positions = array_flip(
        array_map('intval', wp_list_pluck($categories_by_parent[0], 'term_id'))
    );

    usort($categories_by_parent[0], function ($category_a, $category_b) use (
        $root_category_priorities,
        $default_root_priority,
        $root_original_positions
    ) {
        $priority_a = $root_category_priorities[sanitize_title($category_a->name)]
            ?? $default_root_priority;
        $priority_b = $root_category_priorities[sanitize_title($category_b->name)]
            ?? $default_root_priority;

        if ($priority_a !== $priority_b) {
            return $priority_a <=> $priority_b;
        }

        return $root_original_positions[(int) $category_a->term_id]
            <=> $root_original_positions[(int) $category_b->term_id];
    });
}

$render_category_tree = function ($parent_id = 0, $depth = 0, $is_mobile = false) use (
    &$render_category_tree,
    $categories_by_parent,
    $current_category_id
) {
    if (empty($categories_by_parent[$parent_id])) {
        return;
    }

    $list_classes = $depth === 0
        ? 'blog-category-tree m-0 list-none space-y-1 p-0'
        : 'blog-category-children m-0 list-none';
    ?>
    <ul class="<?php echo esc_attr($list_classes); ?>">
        <?php foreach ($categories_by_parent[$parent_id] as $category) : ?>
            <?php
            $category_link = get_category_link($category->term_id);

            if (is_wp_error($category_link)) {
                continue;
            }

            $category_id = (int) $category->term_id;
            $is_active   = $category_id === $current_category_id;
            $is_child    = $depth > 0;
            $font_class  = $is_active || !$is_child ? 'font-semibold' : 'font-medium';
            $state_class = $is_active
                ? 'bg-purple-700 text-white hover:!text-white'
                : ($is_mobile
                    ? 'text-slate-700 active:bg-purple-50 active:text-purple-800'
                    : 'text-slate-700 hover:bg-purple-50 hover:text-purple-800');
            ?>
            <li class="blog-category-branch m-0">
                <a
                    href="<?php echo esc_url($category_link); ?>"
                    class="<?php echo esc_attr(
                                'group flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors ' .
                                    ($is_mobile ? 'min-h-11 ' : '') .
                                    $font_class . ' ' .
                                    $state_class
                            ); ?>"
                    <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
                    <span class="flex min-w-0 items-center gap-2">
                        <?php if ($is_child) : ?>
                            <span class="blog-category-connector" aria-hidden="true"></span>
                        <?php endif; ?>

                        <span class="min-w-0 truncate">
                            <?php echo esc_html($category->name); ?>
                        </span>
                    </span>

                    <span
                        class="<?php echo esc_attr(
                                    'inline-flex min-w-7 shrink-0 items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold ' .
                                        ($is_active
                                            ? 'bg-white/20 text-white'
                                            : 'bg-slate-100 text-slate-600 group-hover:bg-purple-100 group-hover:text-purple-800')
                                ); ?>">
                        <?php echo esc_html(number_format_i18n((int) $category->count)); ?>
                    </span>
                </a>

                <?php $render_category_tree($category_id, $depth + 1, $is_mobile); ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
};

if (have_posts()) : ?>
    <div class="mx-auto grid w-full max-w-[1440px] items-start gap-8 pt-4 sm:py-8 lg:grid-cols-[240px_minmax(0,1fr)] xl:grid-cols-[270px_minmax(0,1fr)]">
        <aside
            class="blog-category-sidebar !hidden lg:sticky lg:top-[110px] lg:!block"
            aria-labelledby="blog-category-list-title">
            <h2
                id="blog-category-list-title"
                class="blog-category-sidebar__title">
                <span class="blog-category-sidebar__title-icon" aria-hidden="true">
                    <i class="fa-light fa-grid-2"></i>
                </span>
                Danh mục bài viết
            </h2>

            <?php if (!empty($all_categories)) : ?>
                <nav aria-label="Danh mục bài viết">
                    <?php $render_category_tree(); ?>
                </nav>
            <?php else : ?>
                <p class="m-0 text-sm text-slate-500">Chưa có danh mục bài viết.</p>
            <?php endif; ?>
        </aside>

        <section class="min-w-0" aria-labelledby="blog-category-posts-title">
            <details class="blog-category-mobile-nav group mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm lg:!hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl px-4 py-3.5 text-left [&::-webkit-details-marker]:!hidden">
                    <span class="min-w-0">
                        <span class="block text-xs font-bold uppercase tracking-wider text-purple-700">
                            Danh mục bài viết
                        </span>
                        <span class="mt-0.5 block truncate text-base font-bold text-slate-900">
                            <?php echo esc_html(single_cat_title('', false)); ?>
                        </span>
                    </span>

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-50 text-purple-700 transition-transform duration-200 group-open:rotate-180">
                        <i class="fa-light fa-chevron-down text-lg" aria-hidden="true"></i>
                    </span>
                </summary>

                <div class="blog-category-mobile-panel border-t border-slate-200 px-3 pb-3 pt-2">
                    <?php if (!empty($all_categories)) : ?>
                        <nav
                            class="max-h-[55vh] overflow-y-auto overscroll-contain py-1"
                            aria-label="Chọn danh mục bài viết">
                            <?php $render_category_tree(0, 0, true); ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </details>

            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <?php if (
                        class_exists('Blog_Category_Archive') &&
                        method_exists('Blog_Category_Archive', 'get_simple_breadcrumb_html') &&
                        $current_category instanceof WP_Term
                    ) : ?>
                        <?php echo Blog_Category_Archive::get_simple_breadcrumb_html($current_category); ?>
                    <?php endif; ?>

                    <h1 id="blog-category-posts-title" class="m-0 text-2xl font-bold text-slate-900 sm:text-3xl">
                        <?php echo esc_html(single_cat_title('', false)); ?>
                    </h1>
                </div>

                <?php if ($current_category instanceof WP_Term) : ?>
                    <span class="text-sm font-medium text-slate-500">
                        <?php
                        printf(
                            esc_html__('%s bài viết', 'flatsome-child'),
                            esc_html(number_format_i18n((int) $current_category->count))
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-x-5 sm:gap-y-8 xl:grid-cols-4">
                <?php while (have_posts()) : ?>
                    <?php
                    the_post();

                    $primary_category = $get_primary_category(get_the_ID());

                    if (
                        $current_category instanceof WP_Term &&
                        (int) $current_category->parent > 0 &&
                        has_category($current_category->term_id, get_the_ID())
                    ) {
                        $primary_category = $current_category;
                    }

                    $primary_category_link = $primary_category
                        ? get_category_link($primary_category->term_id)
                        : '';
                    ?>

                    <article <?php post_class('group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md', get_the_ID()); ?>>
                        <div class="relative overflow-hidden bg-slate-100">
                            <a
                                href="<?php the_permalink(); ?>"
                                class="block aspect-[16/9] overflow-hidden"
                                aria-label="<?php the_title_attribute(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php
                                    the_post_thumbnail('large', array(
                                        'class'   => 'h-full w-full object-cover transition-transform duration-300 group-hover:scale-105',
                                        'loading' => 'lazy',
                                    ));
                                    ?>
                                <?php else : ?>
                                    <span class="flex h-full w-full items-center justify-center bg-slate-100 px-4 text-center text-sm text-slate-400">
                                        Chưa có ảnh đại diện
                                    </span>
                                <?php endif; ?>
                            </a>

                            <?php /*
                                if ($primary_category) : ?>
                                    <?php if (!is_wp_error($primary_category_link)) : ?>
                                        <a
                                            href="<?php echo esc_url($primary_category_link); ?>"
                                            class="absolute bottom-3 left-3 max-w-[calc(100%-1.5rem)] truncate rounded-md bg-purple-700 px-2.5 py-1 text-xs font-bold text-white shadow-sm transition-colors hover:bg-purple-800">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="absolute bottom-3 left-3 rounded-md bg-purple-700 px-2.5 py-1 text-xs font-bold text-white">
                                            <?php echo esc_html($primary_category->name); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif;
                                */ ?>
                        </div>

                        <div class="flex flex-1 flex-col p-3">
                            <h3 class="mb-1 min-h-0 line-clamp-2 text-base font-bold leading-snug text-slate-900 sm:mb-2 sm:min-h-[3.1rem] sm:text-lg">
                                <a
                                    href="<?php the_permalink(); ?>"
                                    class="transition-colors hover:text-purple-700">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <div class="mb-1 min-w-0 text-xs text-slate-500 sm:mb-2">
                                <span class="line-clamp-1">
                                    Đăng bởi
                                    <a
                                        href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                        class="font-semibold text-slate-700 hover:text-purple-700">
                                        <?php the_author(); ?>
                                    </a>
                                </span>

                                <?php /*
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo esc_html(get_the_date('d/m/Y')); ?>
                                    </time>
                                    */ ?>
                            </div>

                            <p class="m-0 line-clamp-2 text-sm leading-6 text-slate-600 sm:line-clamp-3">
                                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 22, '...')); ?>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php if (function_exists('blog_category_posts_pagination')) : ?>
                <?php blog_category_posts_pagination(); ?>
            <?php endif; ?>
        </section>
    </div>
<?php else : ?>
    <?php get_template_part('template-parts/posts/content', 'none'); ?>
<?php endif; ?>
