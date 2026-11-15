<?php
/** Nội dung gốc và thẻ tác giả theo bố cục CopiersVN, dùng màu của website. */
defined('ABSPATH') || exit;
?>
<div class="entry-content single-page">
    <?php the_content(); ?>
    <?php wp_link_pages(); ?>
</div>

<?php if (!post_password_required()) : ?>
    <?php get_template_part('template-parts/posts/partials/author', 'card'); ?>
<?php endif; ?>
