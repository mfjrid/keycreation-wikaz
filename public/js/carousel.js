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
        const slides = carouselEl.querySelectorAll('.swiper-slide');
        const swiper = new Swiper('.wikaz-carousel', {
            // Core
            direction: 'vertical',
            loop: slides.length > 1,
            speed: 1000, // Smoother transition
            grabCursor: false,

            // Autoplay
            autoplay: settings.autoplay ? {
                delay: settings.speed,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            } : false,

            // Mousewheel
            mousewheel: {
                invert: false,
                sensitivity: 1,
                releaseOnEdges: false,
                forceToAxis: true,
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
                },
                touchStart: function (s, e) {
                    this.startX = e.pageX || e.touches[0].pageX;
                    this.startY = e.pageY || e.touches[0].pageY;
                },
                touchEnd: function (s, e) {
                    const endX = e.pageX || e.changedTouches[0].pageX;
                    const endY = e.pageY || e.changedTouches[0].pageY;

                    const diffX = endX - this.startX;
                    const diffY = endY - this.startY;
                    const threshold = 50;

                    // Detect horizontal swipe even in vertical mode
                    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > threshold) {
                        if (diffX > 0) {
                            this.slidePrev();
                        } else {
                            this.slideNext();
                        }
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
