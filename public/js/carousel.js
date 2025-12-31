/**
 * Wikaz Carousel JavaScript
 */

(function () {
    'use strict';

    /**
     * Initialize carousel when DOM is ready
     */
    function init() {
        const carouselEl = document.querySelector('.wikaz-carousel');

        if (!carouselEl) {
            return;
        }

        // Get settings from localized script
        const settings = window.wikazCarousel || {
            autoplay: true,
            speed: 5000
        };

        // Initialize Swiper
        const swiper = new Swiper('.wikaz-carousel', {
            // Core
            loop: true,
            speed: 800,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            // Autoplay
            autoplay: settings.autoplay ? {
                delay: settings.speed,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            } : false,

            // Navigation
            navigation: {
                nextEl: '.wikaz-nav-next',
                prevEl: '.wikaz-nav-prev',
            },

            // Pagination
            pagination: {
                el: '.wikaz-carousel-pagination',
                clickable: true,
            },

            // Keyboard
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },

            // Accessibility
            a11y: {
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide',
                firstSlideMessage: 'This is the first slide',
                lastSlideMessage: 'This is the last slide',
            },

            // Events
            on: {
                init: function () {
                    // Add loaded class for animations
                    carouselEl.classList.add('wikaz-carousel-loaded');
                },
                slideChange: function () {
                    // Reset animations on slide change
                    const activeSlide = this.slides[this.activeIndex];
                    if (activeSlide) {
                        resetAnimations(activeSlide);
                    }
                }
            }
        });

        // Pause autoplay when tab is not visible
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                swiper.autoplay.stop();
            } else if (settings.autoplay) {
                swiper.autoplay.start();
            }
        });
    }

    /**
     * Reset animations for slide elements
     */
    function resetAnimations(slide) {
        const animatedElements = slide.querySelectorAll('.wikaz-slide-subtitle, .wikaz-slide-title, .wikaz-slide-price, .wikaz-slide-button');

        animatedElements.forEach(function (el) {
            el.style.animation = 'none';
            el.offsetHeight; // Trigger reflow
            el.style.animation = null;
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
