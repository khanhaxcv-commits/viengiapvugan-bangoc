<?php
/** Giữ form và xử lý bình luận native; bổ sung tiêu đề và trạng thái đóng. */
defined('ABSPATH') || exit;

if (post_password_required()) {
    return;
}
?>
<section class="mt-6 border-0 border-t border-solid border-[var(--border-ui)] pt-6 text-base leading-7 !text-slate-700 [&_.comment-respond]:!bg-transparent [&_.comment-respond]:!p-0 [&_textarea]:rounded-xl [&_textarea]:border [&_textarea]:border-solid [&_textarea]:border-slate-300 [&_textarea]:bg-white [&_textarea]:p-3 [&_.submit]:rounded-xl [&_.submit]:!bg-purple-700 [&_.submit]:px-5 [&_.submit]:py-3 [&_.submit]:font-semibold [&_.submit]:!text-white [&_.submit:hover]:!bg-purple-800" aria-labelledby="post-comments-title">
    <h2 id="post-comments-title" class="!mt-0 !mb-4 text-xl font-bold !text-slate-900 sm:text-2xl">Bình luận</h2>

    <?php if (!comments_open() && !get_comments_number()) : ?>
        <p class="!mb-0 mt-3 text-sm !text-slate-500">Bài viết hiện đang đóng bình luận.</p>
    <?php endif; ?>

    <?php include get_template_directory() . '/comments.php'; ?>
</section>
