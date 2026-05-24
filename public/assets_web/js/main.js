

(function () {
    'use strict';

    // :: Loading
    $(window).on('load', function () {
        $('.loading').fadeOut();
    });

    // :: Scroll Smooth To Go Section
    $('.move-section').on('click', function (e) {
        e.preventDefault();
        var anchorLink = $(this);
        $('html, body').stop().animate({
            scrollTop: $(anchorLink.attr('href')).offset().top + 1
        }, 1000);
    });

    // :: Add Class Active For ('.nav-bar')
    $(window).on('scroll', function () {
        if ($(window).scrollTop() > $('.all-navbar').height() + 30) {
            $('.nav-bar').addClass('active');
        } else {
            $('.nav-bar').removeClass('active');
        }
    });

    // :: Varables Navbar
    var headerBar = $('.nav-bar'),
        $navbarMenu = $('#open-nav-bar-menu'),
        $menuLink = $('.open-nav-bar'),
        $menuTriggerLink = $('.has-menu > a');

    // :: Add Class Active For $menuLink And $navbarMenu
    $menuLink.on('click', function (e) {
        e.preventDefault();
        $menuLink.toggleClass('active');
        $navbarMenu.toggleClass('active');
    });

    // :: Add Class Active For $menuTriggerLink
    $menuTriggerLink.on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.toggleClass('active').next('ul').toggleClass('active');
    });

    // :: Add Class Active To Search Box
    $('.search-icon.open').on('click', function () {
        $('.search-box').fadeIn();
    });
    $('.search-box, .search-box-icon.exit').on('click', function () {
        $('.search-box').fadeOut();
    });
    $('.search-box form').on('click', function (e) {
        e.stopPropagation();
    });

    // :: Open And Close Menu
    $('.menu-icon.open').on('click', function () {
        $('.menu-box').fadeIn().addClass('active');
    });
    $('.menu-box').on('click', function () {
        $(this).fadeOut().removeClass('active');
    });
    $('.menu-box-icon.exit').on('click', function () {
        $('.menu-box').fadeOut().removeClass('active');
    });
    $('.menu-box .inner-menu').on('click', function (e) {
        e.stopPropagation();
    });

    // :: OWL Carousel Js Header Hero
    $('.header-carousel').owlCarousel({
        loop: true,
        nav: true,
        margin: 30,
          autoplay: true,
        autoplayTimeout:3500,
 		   autoplayHoverPause: true,
        mouseDrag: true,
        touchDrag: true,
        navText: ['<i class="ar-icons-left-chevron"></i>', '<i class="ar-icons-right-chevron"></i>'],
        dotsClass: ['container','owl-dots'],
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 1
            },
            991: {
                items: 1
            }
        }
    });

    // :: Animation OWL Header
    $('.header-carousel').on('translate.owl.carousel', function () {
        $('.header .banner').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .top-handline').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .handline').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .about-website, .header.home-2 .pulse').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .header-list-features .item').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .btn-box').removeClass('animated fadeInUp').css('opacity', '0');
        $('.header .banner .btn-box a.btn-1:first-of-type').removeClass('animated rotateInDownLeft').css('opacity', '0');
        $('.header .banner .btn-box a.btn-1:last-of-type').removeClass('animated rotateInDownLeft').css('opacity', '0');
    });
    $('.header-carousel').on('translated.owl.carousel', function () {
        $('.header .banner').removeClass('animated fadeIn').css('opacity', '1');
        $('.header .banner .top-handline').addClass('animated fadeInUp').css('opacity', '1');
        $('.header .banner .handline').addClass('animated fadeInUp').css('opacity', '1');
        $('.header .banner .about-website, .header.home-2 .pulse').addClass('animated fadeInUp').css('opacity', '1');
		$('.header .banner .header-list-features .item').addClass('animated fadeInUp').css('opacity', '1');
        $('.header .banner .btn-box').addClass('animated fadeInUp').css('opacity', '1');
        $('.header .banner .btn-box a.btn-1:first-of-type').addClass('animated rotateInDownLeft').css('opacity', '1');
        $('.header .banner .btn-box a.btn-1:last-of-type').addClass('animated rotateInDownLeft').css('opacity', '1');
    });

    // :: OWL Carousel Js Features
    $('.about-us-features-carousel').owlCarousel({
        loop: true,
        nav: false,
        dots: false,
        autoplay: true,
        autoplayTimeout: 4000,
        smartSpeed: 1000,
        margin: 30,
        autoplayHoverPause: true,
        mouseDrag: true,
        touchDrag: true,
        responsive: {
            0: {
                items: 1
            },
            575: {
                items: 1
            },
            768: {
                items: 1
            },
            991: {
                items: 1
            }
        }
    });

    // :: OWL Carousel Js Departments Item
    $('.services-carousel').owlCarousel({
        loop: true,
        margin: 30,
        smartSpeed: 1000,
        autoplay: 2000,
        nav: true,
        autoplayHoverPause: true,
        mouseDrag: true,
        touchDrag: true,
        navText: ['<i class="ar-icons-left-chevron"></i>', '<i class="ar-icons-right-chevron"></i>'],
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            991: {
                items: 3
            },
            1199: {
                items: 3
            },
            1399: {
                items: 4
            }
        }
    });

    // :: NiceSelect Plugin
    $('select').niceSelect();

    // :: Counter Up Js
    $('.counter').counterUp({
        delay: 20,
        time: 1000
    });

    // :: OWL Carousel Js projects
    $('.projects-carousel').owlCarousel({
        loop: true,
        margin: 30,
        smartSpeed: 1000,
        autoplay: 2000,
        nav: true,
        autoplayHoverPause: true,
        mouseDrag: true,
        touchDrag: true,
        navText: ['<i class="ar-icons-left-chevron"></i>', '<i class="ar-icons-right-chevron"></i>'],
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            991: {
                items: 3
            },
            1199: {
                items: 3
            },
            1399: {
                items: 4
            }
        }
    });

    // :: Magnific Popup Plugin
    $('.projects-item .hover-box .projects-icon li a.popup').magnificPopup({
        type: 'image',
        projects: {
            enabled: true
        }
    });

    // :: OWL Carousel Js Testimonial
    $('.testimonial-carousel').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
        loop: true,
        nav: true,
          autoplay: true,
        autoplayTimeout:2000,
 		autoplayHoverPause: true,
		mouseDrag: true,
		touchDrag: true,
       margin:20,
        navText: ['<i class="ar-icons-left-chevron"></i>', '<i class="ar-icons-right-chevron"></i>'],
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            991: {
                items: 2
            }
        }
    });

    // :: OWL Carousel Js Testimonial
    $('.newsdetail-carousel').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
        loop: true,
        nav: false,
          autoplay: true,
        autoplayTimeout:2300,
 		autoplayHoverPause: true,
		mouseDrag: true,
		touchDrag: true,
       margin:20,
        navText: ['<i class="ar-icons-left-chevron"></i>', '<i class="ar-icons-right-chevron"></i>'],
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 1
            },
            991: {
                items: 1
            }
        }
    });
	// :: OWL Carousel Js Sponsors Carousel
	$('.sponsors-carousel').owlCarousel({
		loop: true,
 		nav: false,
		dots: true,
          autoplay: true,
        autoplayTimeout:1400,
 		autoplayHoverPause: true,
		mouseDrag: true,
		touchDrag: true,
        margin:20,
		responsive: {
			0: {
				items: 2
			},
			575: {
				items: 3
			},
			768: {
				items: 4
			},
			991: {
				items: 6
			}
		}
	});

	// :: Skills Data Value
    $(window).on('scroll', function () {
        $('.skills .skill-box .skill-line .line').each(function () {
            var toQuestionsAndSkills =
                $(this).offset().top + $(this).outerHeight();
            var goToBottom =
                $(window).scrollTop() + $(window).height();
            var widthValue = $(this).attr('data-value');
            if (goToBottom > toQuestionsAndSkills) {
                $(this).css({
                    width: widthValue,
                    transition: 'all 2s ease'
                });
            }
        });
    });

    // :: Add Class Active On Go To Header
    $(window).on('scroll', function () {
        if ($(window).scrollTop() > 0) {
            $('.scroll-up').addClass('active');
        } else {
            $('.scroll-up').removeClass('active');
        }
    });

	// :: Add Class Active To Gallery
    $('.projects-list .list-name-projects li').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
    });

}());

// :: Setup mouseenter On Department Item
$(document).ready(function () {
    $('.services.home-2 .services-item-img-box, .projects-item, .services.home-3 .item-box').on('mouseenter', function (e) {
        x = e.pageX - $(this).offset().left,
            y = e.pageY - $(this).offset().top;
        $(this).find('span').css({
            top: y,
            left: x
        });
    });
    $('.services.home-2 .services-item-img-box, .projects-item, .services.home-3 .item-box').on('mouseout', function (e) {
        x = e.pageX - $(this).offset().left,
            y = e.pageY - $(this).offset().top;
        $(this).find('span').css({
            top: y,
            left: x
        });
    });

});
