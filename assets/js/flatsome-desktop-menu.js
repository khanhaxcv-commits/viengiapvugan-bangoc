(function ($) {
  "use strict";

  var $window = $(window);

  /* ========================================================================================
     FLATSOME DESKTOP MAIN MENU - Nested submenu anti overflow
     Flatsome desktop dùng current-dropdown cho dropdown cấp 1
  ======================================================================================== */

  function initMainMenuNestedFlyout() {
    var $menu = $(".header-nav-main");

    if (!$menu.length) {
      return;
    }

    var submenuSelector = "ul.sub-menu, ul.nav-dropdown";
    var directionClasses = "is-left is-align-right";
    var safeGap = 16;

    function getSubmenu($item) {
      return $item.children(submenuSelector).first();
    }

    function resetSubmenuDirection($submenu) {
      $submenu.removeClass(directionClasses);
    }

    function isRightOverflow($submenu) {
      var rect = $submenu[0].getBoundingClientRect();

      return rect.right > window.innerWidth - safeGap;
    }

    function requestUpdate(callback) {
      if (window.requestAnimationFrame) {
        window.requestAnimationFrame(callback);
        return;
      }

      setTimeout(callback, 16);
    }

    function updateLevel2Direction($item) {
      var $submenu = getSubmenu($item);

      if (!$submenu.length) {
        return;
      }

      resetSubmenuDirection($submenu);

      if (isRightOverflow($submenu)) {
        $submenu.addClass("is-align-right");
      }
    }

    function updateNestedDirection($item) {
      var $submenu = getSubmenu($item);

      if (!$submenu.length) {
        return;
      }

      if (!$item.closest(".sub-menu, .nav-dropdown").length) {
        return;
      }

      resetSubmenuDirection($submenu);

      if (isRightOverflow($submenu)) {
        $submenu.addClass("is-left");
      }
    }

    function updateDropdownState($item, isOpen) {
      var $topLink = $item.children("a.nav-top-link").first();

      if (!$topLink.length || !getSubmenu($item).length) {
        return;
      }

      $item.toggleClass("current-dropdown", isOpen);
      $topLink.attr("aria-expanded", isOpen ? "true" : "false");
    }

    function prepareTopItems() {
      $menu.children("li").each(function () {
        var $item = $(this);
        var $topLink = $item.children("a.nav-top-link").first();

        if (!$topLink.length || !getSubmenu($item).length) {
          return;
        }

        var isOpen = $item.hasClass("current-dropdown");

        $topLink.attr({
          "aria-haspopup": "menu",
          "aria-expanded": isOpen ? "true" : "false",
        });
      });
    }

    prepareTopItems();

    $menu
      .off(".flatsomeDesktopMenu")
      .on("mouseenter.flatsomeDesktopMenu", "> li", function () {
        var $item = $(this);

        if (!getSubmenu($item).length) {
          return;
        }

        updateDropdownState($item, true);

        requestUpdate(function () {
          updateLevel2Direction($item);
        });
      })
      .on("mouseleave.flatsomeDesktopMenu", "> li", function () {
        updateDropdownState($(this), false);
      })
      .on("focusin.flatsomeDesktopMenu", "> li", function () {
        var $item = $(this);

        if (!getSubmenu($item).length) {
          return;
        }

        updateDropdownState($item, true);

        requestUpdate(function () {
          updateLevel2Direction($item);
        });
      })
      .on("focusout.flatsomeDesktopMenu", "> li", function () {
        var item = this;

        setTimeout(function () {
          if (!item.contains(document.activeElement)) {
            updateDropdownState($(item), false);
          }
        }, 0);
      })
      .on(
        "mouseenter.flatsomeDesktopMenu",
        ".sub-menu li, .nav-dropdown li",
        function () {
          var $item = $(this);

          requestUpdate(function () {
            updateNestedDirection($item);
          });
        },
      );

    $window
      .off("resize.flatsomeDesktopMenu")
      .on("resize.flatsomeDesktopMenu", function () {
        $menu.find(".is-left, .is-align-right").removeClass(directionClasses);
      });
  }

  $(function () {
    if (!window.matchMedia("(min-width: 850px)").matches) {
      return;
    }

    initMainMenuNestedFlyout();
  });
})(jQuery);
