(() => {
  'use strict';

  document.querySelectorAll('.wpcf7 form').forEach((form) => {
    // CF7 giữ lại lỗi cũ khi một trường tùy chọn bị xóa trắng; đồng bộ lại trạng thái này.
    form.addEventListener('input', (event) => {
      const field = event.target;
      if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement)
        || field.getAttribute('aria-required') === 'true'
        || field.value.trim() !== '' || field.getAttribute('aria-invalid') !== 'true') return;

      const wrap = field.closest('.wpcf7-form-control-wrap');
      if (!wrap) return;
      wrap.querySelectorAll('.wpcf7-not-valid-tip').forEach((tip) => tip.remove());
      field.classList.remove('wpcf7-not-valid');
      field.setAttribute('aria-invalid', 'false');

      const descriptions = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
      const retained = descriptions.filter((id) => {
        const error = document.getElementById(id);
        if (error && form.closest('.wpcf7').querySelector('.screen-reader-response')?.contains(error)) {
          error.remove();
          return false;
        }
        return true;
      });
      if (retained.length) field.setAttribute('aria-describedby', retained.join(' '));
      else field.removeAttribute('aria-describedby');
    });
  });
})();
