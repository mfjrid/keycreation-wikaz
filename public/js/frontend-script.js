/**
 * Wikaz Frontend JavaScript (Product Page)
 */

(function ($) {
    'use strict';

    /**
     * Initialize
     */
    function init() {
        // Initial stock replacement for simple products or default variable state
        if (wikazProductData.currentStock !== '') {
            const $stockEls = $(wikazProductData.stockSelector);
            $stockEls.each(function () {
                const $el = $(this);
                // Only replace if it contains "In Stock" or looks like a stock label
                if ($el.text().toLowerCase().includes('in stock') || $el.hasClass('in-stock')) {
                    $el.text(wikazProductData.currentStock + ' ' + wikazProductData.stockLabel);
                }
            });
        }

        // Handle variation stock updates
        $(document).on('found_variation', 'form.variations_form', function (event, variation) {
            const $stockEl = $(wikazProductData.stockSelector);

            if (variation.is_in_stock && variation.max_qty) {
                const message = variation.max_qty + ' ' + wikazProductData.stockLabel;
                $stockEl.text(message).show();
            }
        });

        // Hide view count element if CSS didn't catch it
        if (wikazProductData.viewCountSelector) {
            $(wikazProductData.viewCountSelector).hide();
        }
    }

    // Initialize when DOM is ready
    $(document).ready(init);

})(jQuery);
