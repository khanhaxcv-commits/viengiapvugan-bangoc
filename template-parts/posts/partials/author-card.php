<?php
/** Thẻ tác giả: dữ liệu hồ sơ WordPress, nhãn kiểm duyệt hiển thị mặc định. */
defined('ABSPATH') || exit;

$author_id = (int) get_the_author_meta('ID');
$author_name = get_the_author_meta('display_name', $author_id);
$author_bio = get_the_author_meta('description', $author_id);
$author_url = get_author_posts_url($author_id);
?>
<aside class="mt-6 rounded-xl border border-solid border-purple-200 bg-[var(--surface-bg-soft)] p-4 text-base leading-7 !text-slate-700 sm:p-6" aria-label="Thông tin tác giả">
    <header class="!flex items-center gap-4">
        <div class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-full border-[3px] border-solid border-white bg-white [&_img]:!m-0 [&_img]:h-full [&_img]:w-full [&_img]:rounded-full [&_img]:object-cover">
            <?php echo get_avatar($author_id, 72, '', $author_name); ?>
        </div>
        <div class="min-w-0">
            <p class="!m-0 text-sm !text-slate-500">Tác giả</p>
            <h2 class="!m-0 break-words text-lg font-bold capitalize !text-slate-900">
                <?php echo esc_html($author_name); ?>
            </h2>
            <p class="!m-0 !flex items-center gap-1.5 text-sm !text-purple-700">
                <i class="fa-solid fa-circle-check shrink-0 text-base" aria-hidden="true"></i>
                <span>Đã kiểm duyệt nội dung</span>
            </p>
        </div>
    </header>

    <?php if (trim(wp_strip_all_tags($author_bio)) !== '') : ?>
        <div class="mt-3 break-words [&_p]:mb-3 [&_p:last-child]:mb-0">
            <?php echo wp_kses_post(wpautop($author_bio)); ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 border-0 border-t border-solid border-purple-200 pt-4">
        <a href="<?php echo esc_url($author_url); ?>" class="font-semibold !text-purple-700 hover:!text-purple-900 hover:underline focus-visible:outline-purple-700">
            Xem chi tiết thông tin tác giả
        </a>
    </div>
</aside>
