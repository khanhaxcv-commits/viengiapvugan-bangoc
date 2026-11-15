<?php
/** Thanh breadcrumb ngay dưới header, cùng màu với trang danh mục. */
defined('ABSPATH') || exit;

if (empty($args['html'])) {
    return;
}
?>
<div class="post-breadcrumb-1794670961990 bg-[#EDF0F3] py-3">
    <div class="row row-large">
        <div class="col large-9 small-12 !pb-0">
            <?php echo $args['html']; // HTML đã được escape bởi bộ xử lý breadcrumb dùng chung. ?>
        </div>
        <div class="col large-3 !hidden !pb-0 min-[850px]:!block" aria-hidden="true"></div>
    </div>
</div>
