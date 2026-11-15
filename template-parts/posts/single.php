<?php
/** Bài viết: tiêu đề, ngày cập nhật/chia sẻ, nội dung và tác giả. */
defined('ABSPATH') || exit;

if (have_posts()) :
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-post-1794670961990 !m-0 min-w-0'); ?>>
            <header class="mb-0 border-0 border-b border-solid border-[var(--border-ui)] pb-4">
                <h1 class="entry-title post-title-1794711386087">
                    <?php the_title(); ?>
                </h1>
            </header>

            <?php if (!post_password_required()) : ?>
                <div class="mb-6 !flex flex-wrap items-center justify-between gap-x-4 gap-y-3 border-0 border-b border-solid border-[var(--border-ui)] py-4 text-sm !text-slate-500">
                    <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>">
                        Nội dung được cập nhật vào ngày: <?php echo esc_html(get_the_modified_date('d/m/Y')); ?>
                    </time>

                    <?php $share_html = Vgv_Site_Post::get_share_html(); ?>
                    <?php if ($share_html !== '') : ?>
                        <div class="!flex min-w-0 flex-wrap items-center gap-2" aria-label="Chia sẻ bài viết">
                            <span>Chia sẻ:</span>
                            <?php echo $share_html; // HTML từ shortcode native, giữ hành vi chia sẻ của Flatsome. ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php get_template_part('template-parts/posts/content', 'single'); ?>
        </article>
        <?php
    endwhile;
else :
    get_template_part('no-results', 'index');
endif;
