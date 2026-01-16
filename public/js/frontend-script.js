/**
 * Wikaz Frontend JavaScript (Product Page)
 */

(function ($) {
    'use strict';

    /**
     * Initialize
     */
    /**
     * Update stock display elements
     */
    function updateStockDisplay(count) {
        if (count === '' || count === undefined) return;

        const $stockEls = $(wikazProductData.stockSelector);
        $stockEls.each(function () {
            const $el = $(this);
            // Replace if it contains "In Stock", "available", or is the badge
            const text = $el.text().toLowerCase();
            if (text.includes('in stock') || text.includes(wikazProductData.stockLabel) || $el.hasClass('in-stock')) {
                $el.text(count + ' ' + wikazProductData.stockLabel);
            }
        });
    }

    /**
     * Initialize
     */
    function init() {
        // Initial stock replacement
        updateStockDisplay(wikazProductData.currentStock);

        // Handle variation stock updates
        $(document).on('found_variation', 'form.variations_form', function (event, variation) {
            if (variation.is_in_stock && variation.max_qty !== undefined) {
                updateStockDisplay(variation.max_qty);
            }
        });

        // Handle variation reset (show total stock again)
        $(document).on('reset_data', 'form.variations_form', function () {
            updateStockDisplay(wikazProductData.currentStock);
        });

        // Hide view count element if CSS didn't catch it
        if (wikazProductData.viewCountSelector) {
            $(wikazProductData.viewCountSelector).hide();
        }
    }

    // Initialize when DOM is ready
    $(document).ready(init);

})(jQuery);
