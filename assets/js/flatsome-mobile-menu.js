(function ($) {
  "use strict";

  /* ========================================================================================
     FLATSOME MOBILE SIDEBAR MENU - Nested accordion
     Tận dụng button.toggle của Flatsome cấp 1, tự thêm button cho cấp sâu
  ======================================================================================== */

  function initMobileSidebarMenu() {
    var $mobileMenu = $("#main-menu.mobile-sidebar");

    if (!$mobileMenu.length) {
      return;
    }

    var $nav = $mobileMenu.find(".nav-sidebar").first();

    if (!$nav.length) {
      return;
    }

    var $parentItems = $nav.find("li").filter(function () {
      return $(this).children("ul.sub-menu").length > 0;
    });

    $parentItems.each(function () {
      var $item = $(this);
      var $submenu = $item.children("ul.sub-menu").first();
      var $link = $item.children("a").first();

      if (!$submenu.length || !$link.length) {
        return;
      }

      $item.addClass("menu-item-has-children");

      /*
       * Dọn custom toggle cũ nếu off-canvas render lại.
       * Chỉ xóa custom toggle, không xóa button.toggle mặc định của Flatsome.
       */
      $item.children("button.mobile-submenu-toggle").remove();

      var $toggle = $item.children("button.toggle").first();

      /*
       * Cấp sâu thường không có button.toggle.
       * Khi thiếu thì tạo button custom.
       */
      if (!$toggle.length) {
        $toggle = $(
          '<button type="button" class="mobile-submenu-toggle" aria-label="Toggle submenu" aria-expanded="false"></button>',
        );

        $toggle.insertBefore($submenu);
      }

      /*
       * Đồng bộ trạng thái ban đầu.
       * CSS chỉ cần li.is-open, button chỉ giữ aria-expanded.
       */
      var isOpen =
        $item.hasClass("is-open") ||
        $item.hasClass("active") ||
        $item.attr("aria-expanded") === "true" ||
        $toggle.attr("aria-expanded") === "true";

      $item.toggleClass("is-open", isOpen);
      $item.attr("aria-expanded", isOpen ? "true" : "false");
      $toggle.attr("aria-expanded", isOpen ? "true" : "false");

      /*
       * Không để inline style của Flatsome làm lệch logic CSS.
       */
      $submenu.removeAttr("style");

      /*
       * Bind click duy nhất cho từng toggle.
       */
      $toggle
        .off("click.flatsomeMobileMenu")
        .on("click.flatsomeMobileMenu", function (event) {
          event.preventDefault();
          event.stopPropagation();

          var nowOpen = !$item.hasClass("is-open");

          $item.toggleClass("is-open", nowOpen);
          $item.attr("aria-expanded", nowOpen ? "true" : "false");
          $toggle.attr("aria-expanded", nowOpen ? "true" : "false");
        });
    });
  }

  $(function () {
    if (!window.matchMedia("(max-width: 849px)").matches) {
      return;
    }
    initMobileSidebarMenu();

    /*
     * Off-canvas mobile của Flatsome đôi khi render sau DOM ready.
     */
    setTimeout(initMobileSidebarMenu, 300);
    setTimeout(initMobileSidebarMenu, 800);

    /*
     * Khi người dùng mở off-canvas, chạy lại để bắt DOM mới nếu Flatsome vừa clone/render.
     */
    $(document)
      .off("click.flatsomeMobileMenuInit")
      .on(
        "click.flatsomeMobileMenuInit",
        ".mobile-nav .icon-menu, .header-button a[href='#main-menu'], a[data-open='#main-menu']",
        function () {
          setTimeout(initMobileSidebarMenu, 120);
          setTimeout(initMobileSidebarMenu, 420);
        },
      );
  });
})(jQuery);
