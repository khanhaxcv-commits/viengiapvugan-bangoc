/* global jQuery */
(() => {
  'use strict';

  const toc = document.getElementById('ftwp-container');
  if (!toc) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const applyMotion = () => {
    if (window.fixedtocOption) window.fixedtocOption.scrollDuration = reducedMotion.matches ? 0 : 500;
  };
  jQuery(toc).on('ftocReady.vgv', applyMotion);
  reducedMotion.addEventListener('change', applyMotion);
  applyMotion();

  const root = document.documentElement;
  const header = document.querySelector('.header-wrapper');
  const adminbar = document.getElementById('wpadminbar');
  const headerLinks = header ? [...header.querySelectorAll('.header-nav > li > a')] : [];
  let frame = 0;
  let correction = 0;

  // Header mobile có thể hiện lại khi cuộn ngược: luôn dành đủ chiều cao của nó.
  const obstruction = () => {
    const barStyle = adminbar && getComputedStyle(adminbar);
    const barBottom = barStyle && barStyle.position === 'fixed' && barStyle.display !== 'none'
      ? Math.max(0, adminbar.getBoundingClientRect().bottom) : 0;
    if (!header || getComputedStyle(header).display === 'none') return barBottom;
    const rect = header.getBoundingClientRect();
    // Tính cả nút/menu tràn xuống dưới wrapper ở các độ rộng sát breakpoint.
    const height = headerLinks.reduce((extent, link) => {
      const linkRect = link.getBoundingClientRect();
      return linkRect.height ? Math.max(extent, linkRect.bottom - rect.top) : extent;
    }, rect.height);
    return Math.max(barBottom + height, rect.top + height);
  };

  const updateOffset = () => {
    frame = 0;
    root.style.setProperty('--vgv-toc-top', `${Math.ceil(obstruction() + 12)}px`);
  };
  const scheduleOffset = () => {
    if (!frame) frame = requestAnimationFrame(updateOffset);
  };

  const getTarget = () => {
    let id;
    try { id = decodeURIComponent(location.hash.slice(1)); } catch (_) { return null; }
    const target = document.getElementById(id);
    return target && target.classList.contains('ftwp-heading') ? target : null;
  };

  const alignTarget = () => {
    const target = getTarget();
    if (!target) return;
    updateOffset();
    const delta = target.getBoundingClientRect().top - obstruction() - 16;
    if (Math.abs(delta) > 1) window.scrollBy({ top: delta, behavior: 'instant' });
  };

  // Plugin cuộn trước; hiệu chỉnh sau khi Flatsome đã chuyển sang header cố định.
  const scheduleCorrection = (delay = 120) => {
    clearTimeout(correction);
    correction = setTimeout(() => requestAnimationFrame(alignTarget), delay);
  };
  jQuery(toc).on('ftocAfterScrollToTarget.vgv', () => scheduleCorrection());
  window.addEventListener('hashchange', () => scheduleCorrection(700));
  window.addEventListener('scroll', scheduleOffset, { passive: true });
  window.addEventListener('resize', scheduleOffset);
  // Không kéo người đọc trở lại tiêu đề nếu họ chủ động cuộn tiếp.
  ['wheel', 'touchstart', 'keydown'].forEach((event) => {
    window.addEventListener(event, () => clearTimeout(correction), { passive: true });
  });
  if (header && 'ResizeObserver' in window) new ResizeObserver(scheduleOffset).observe(header);

  const trigger = document.getElementById('ftwp-trigger');
  if (trigger) {
    trigger.title = 'Mở mục lục bài viết';
    trigger.setAttribute('aria-label', 'Mở mục lục bài viết');
  }
  const minimize = document.getElementById('ftwp-header-minimize');
  if (minimize) minimize.setAttribute('aria-label', 'Thu gọn hoặc mở mục lục');

  const tocHeader = document.getElementById('ftwp-header');
  const contents = document.getElementById('ftwp-contents');
  const list = document.getElementById('ftwp-list');
  if (tocHeader && contents && list && minimize) {
    const $list = jQuery(list);
    const $contents = jQuery(contents);
    const syncExpanded = () => {
      const expanded = toc.classList.contains('ftwp-fixed-to-post')
        ? toc.classList.contains('ftwp-maximize')
        : $contents.data('colexp') !== 'collapse';
      minimize.setAttribute('aria-expanded', String(expanded));
    };
    minimize.setAttribute('aria-controls', list.id);

    // Chỉ thay chuyển động trong bài; bảng nổi tiếp tục dùng cơ chế đóng native.
    tocHeader.addEventListener('click', (event) => {
      if (toc.classList.contains('ftwp-fixed-to-post')) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      const expanded = $contents.data('colexp') === 'collapse';
      $contents.data('colexp', expanded ? 'expand' : 'collapse');
      minimize.classList.toggle('ftwp-icon-expand', expanded);
      minimize.classList.toggle('ftwp-icon-collapse', !expanded);
      syncExpanded();

      // slide chỉ thay chiều cao/padding dọc, không thu chiều ngang hoặc làm mờ chữ.
      $list.stop(true, false)[expanded ? 'slideDown' : 'slideUp']({
        duration: reducedMotion.matches ? 0 : 240,
        easing: 'swing',
        complete: () => {
          $contents.trigger(expanded ? 'ftocAfterExpandContents' : 'ftocAfterCollapseContents');
          if (window.fixedtoc) window.fixedtoc.reload();
        },
      });
    }, true);

    jQuery(toc).on('ftocReady.vgv ftoc_inPost.vgv ftoc_fixedToPost.vgv ftocAfterMinMax.vgv', () => {
      requestAnimationFrame(syncExpanded);
    });
    syncExpanded();
  }

  updateOffset();
  if (document.readyState === 'complete') scheduleCorrection();
  else window.addEventListener('load', () => scheduleCorrection(), { once: true });
  if (document.fonts) document.fonts.ready.then(() => scheduleCorrection());
})();
