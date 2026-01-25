/**
 * Wikaz Header Slider JavaScript
 */

(function () {
    'use strict';

    function initHeaderSliders() {
        const sliders = document.querySelectorAll('.wikaz-header-slider-instance');

        if (!sliders.length) return;

        sliders.forEach(slider => {
            const autoplay = slider.dataset.autoplay === '1';
            const speed = parseInt(slider.dataset.speed) || 5000;
            const uid = slider.dataset.uid;

            // Debug
            const slidesCount = slider.querySelectorAll('.swiper-slide').length;
            console.log('Wikaz Slider Init', uid, 'Slides:', slidesCount);

            // Use unique class for this instance to avoid conflicts
            new Swiper(slider, {
                // Core
                direction: 'horizontal', // Usually hero sliders are horizontal
                loop: slidesCount > 1,
                speed: 1000,
                grabCursor: true,

                // Autoplay
                autoplay: autoplay ? {
                    delay: speed,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                } : false,

                // Pagination
                pagination: {
                    el: `.wikaz-pagination-${uid}`,
                    clickable: true,
                },

                // Navigation (Optional, standard Swiper arrows if needed)
                navigation: {
                    nextEl: `.wikaz-button-next-${uid}`,
                    prevEl: `.wikaz-button-prev-${uid}`,
                },

                // NO Mousewheel hijacking
                mousewheel: false,

                // Keyboard
                keyboard: {
                    enabled: true,
                    onlyInViewport: true,
                },

                // Events
                on: {
                    init: function () {
                        slider.classList.add('wikaz-carousel-loaded');
                        // Update counter on init
                        const counter = document.querySelector(`.wikaz-counter-${uid} .counter-current`);
                        if (counter) counter.textContent = this.realIndex + 1;
                    },
                    slideChange: function () {
                        // Update slide counter
                        const counter = document.querySelector(`.wikaz-counter-${uid} .counter-current`);
                        if (counter) counter.textContent = this.realIndex + 1;

                        // Reset animations
                        const activeSlide = this.slides[this.activeIndex];
                        if (activeSlide) {
                            resetAnimations(activeSlide);
                        }
                    }
                }
            });
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
        document.addEventListener('DOMContentLoaded', initHeaderSliders);
    } else {
        initHeaderSliders();
    }

})();
