(function ($) {
  "use strict";

  var $window = $(window);

  function hidePreloader() {
    $(".preloader").fadeOut(600);
  }

  $window.on("load", hidePreloader);
  setTimeout(hidePreloader, 300);

  $(function () {
    $(".data-cursor-text-play").attr("data-cursor-text", "Play");
    $(".data-cursor-opaque").attr("data-cursor", "-opaque");

    $(".image-anime.image-reveal").addClass("reveal");

    $(".stack").removeClass(
      "stack stack-row justify-start items-stretch stack-col",
    );

    /* ======================================================================================== */
    /* HOME DOCTOR SWIPER                                                                        */
    /* ======================================================================================== */

    var $doctorSectionHome = $(".row-1778944461520");

    if (
      $doctorSectionHome.length &&
      $doctorSectionHome.find(".doctor-swiper").length &&
      typeof Swiper !== "undefined"
    ) {
      new Swiper($doctorSectionHome.find(".doctor-swiper")[0], {
        slidesPerView: 1,
        spaceBetween: 20,
        grabCursor: true,
        speed: 450,

        navigation: {
          prevEl: $doctorSectionHome.find("button").eq(0)[0],
          nextEl: $doctorSectionHome.find("button").eq(1)[0],
        },

        breakpoints: {
          640: {
            slidesPerView: 2,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 5,
            spaceBetween: 20,
          },
        },

        observer: true,
        observeParents: true,
        resizeObserver: true,
        watchOverflow: true,
      });
    }

    /* ======================================================================================== */
    /* HOME TESTIMONIAL SWIPER                                                                   */
    /* ======================================================================================== */

    var $testimonialSectionHome = $(".row-1779014766554");

    if (
      $testimonialSectionHome.length &&
      $testimonialSectionHome.find(".testimonial-swiper").length &&
      typeof Swiper !== "undefined"
    ) {
      new Swiper($testimonialSectionHome.find(".testimonial-swiper")[0], {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        watchOverflow: true,

        autoplay: {
          delay: 3500,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        },

        pagination: {
          el: ".testimonial-pagination",
          clickable: true,
        },

        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 24,
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 28,
          },
        },
      });
    }

    /* ======================================================================================== */
    /* DOCTOR GRID SWIPER                                                                        */
    /* ======================================================================================== */

    var $doctorGridSection = $(".row-1779004873583");

    if (
      $doctorGridSection.length &&
      $(".doctor-grid.swiper").length &&
      typeof Swiper !== "undefined"
    ) {
      new Swiper(".doctor-grid.swiper", {
        loop: true,
        speed: 600,
        spaceBetween: 20,
        slidesPerView: 1,

        navigation: {
          prevEl: ".doctor-prev",
          nextEl: ".doctor-next",
        },

        breakpoints: {
          640: {
            slidesPerView: 2,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 5,
            spaceBetween: 20,
          },
        },
      });
    }

    /* ======================================================================================== */
    /* TESTIMONIAL LIST SWIPER                                                                   */
    /* ======================================================================================== */

    var $testimonialSwiperEl = $(".section-1782561049285 .testimonial-list");

    if (
      $testimonialSwiperEl.length &&
      typeof Swiper !== "undefined" &&
      !$testimonialSwiperEl.hasClass("swiper-initialized")
    ) {
      new Swiper($testimonialSwiperEl[0], {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 18,
        speed: 650,
        loop: false,
        autoplay: false,
        watchOverflow: false,
        observer: true,
        observeParents: true,
        resizeObserver: true,

        pagination: {
          el: ".section-1782561049285 .testimonial-pagination",
          clickable: true,
        },

        breakpoints: {
          768: {
            slidesPerView: 2,
            slidesPerGroup: 1,
            spaceBetween: 22,
          },
          1025: {
            slidesPerView: 4,
            slidesPerGroup: 1,
            spaceBetween: 24,
          },
        },
      });
    }

    /* ======================================================================================== */
    /* FAQ ACCORDION                                                                             */
    /* ======================================================================================== */

    $(".section-1782563529147 .faq-question").on("click", function () {
      var $button = $(this);
      var $item = $button.closest(".faq-item");
      var $list = $button.closest(".faq-list");

      if (!$item.length || !$list.length) {
        return;
      }

      $list.find(".faq-item").not($item).removeClass("is-active");
      $item.toggleClass("is-active");
    });
  });
})(jQuery);
