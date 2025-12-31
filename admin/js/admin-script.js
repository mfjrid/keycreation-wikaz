/**
 * Wikaz Admin JavaScript
 */

(function ($) {
    'use strict';

    // Cache DOM elements
    const $slidesList = $('#wikaz-slides-list');
    const $modal = $('#wikaz-slide-modal');
    const $slideForm = $('#wikaz-slide-form');
    const $settingsForm = $('#wikaz-settings-form');

    // Media uploader instance
    let mediaUploader;

    /**
     * Initialize
     */
    function init() {
        initSortable();
        bindEvents();
    }

    /**
     * Initialize sortable
     */
    function initSortable() {
        $slidesList.sortable({
            handle: '.wikaz-slide-handle',
            placeholder: 'wikaz-slide-placeholder',
            update: function (event, ui) {
                updateOrder();
            }
        });
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Add new slide
        $('#wikaz-add-slide').on('click', openNewSlideModal);

        // Edit slide
        $slidesList.on('click', '.wikaz-edit-slide', function () {
            const $item = $(this).closest('.wikaz-slide-item');
            openEditSlideModal($item.data('id'));
        });

        // Delete slide
        $slidesList.on('click', '.wikaz-delete-slide', function () {
            const $item = $(this).closest('.wikaz-slide-item');
            deleteSlide($item.data('id'), $item);
        });

        // Toggle active
        $slidesList.on('change', '.wikaz-toggle-active', function () {
            const $item = $(this).closest('.wikaz-slide-item');
            toggleSlide($item.data('id'), $(this).is(':checked'), $item);
        });

        // Modal close
        $('.wikaz-modal-close, .wikaz-modal-cancel').on('click', closeModal);
        $modal.on('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Save slide
        $slideForm.on('submit', saveSlide);

        // Save settings
        $settingsForm.on('submit', saveSettings);

        // Image upload
        $('#wikaz-select-image, #wikaz-image-preview').on('click', selectImage);
        $('#wikaz-remove-image').on('click', removeImage);

        // Product search
        $('#wikaz-product-search').on('input', debounce(searchProducts, 300));
        $('#wikaz-product-results').on('click', '.wikaz-product-item', selectProduct);
        $('#wikaz-selected-product .remove-product').on('click', removeProduct);

        // Close product results on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wikaz-product-search-wrap').length) {
                $('#wikaz-product-results').removeClass('active');
            }
        });
    }

    /**
     * Open modal for new slide
     */
    function openNewSlideModal() {
        resetForm();
        $('#wikaz-modal-title').text(wikazAdmin.strings.selectImage.replace('Select Background Image', 'Add New Slide'));
        $modal.addClass('active');
    }

    /**
     * Open modal for editing slide
     */
    function openEditSlideModal(slideId) {
        resetForm();
        $('#wikaz-modal-title').text('Edit Slide');

        // Get slide data via AJAX
        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_slide',
                nonce: wikazAdmin.nonce,
                slide_id: slideId
            },
            success: function (response) {
                if (response.success && response.data) {
                    populateForm(response.data);
                }
                $modal.addClass('active');
            }
        });
    }

    /**
     * Close modal
     */
    function closeModal() {
        $modal.removeClass('active');
    }

    /**
     * Reset form
     */
    function resetForm() {
        $slideForm[0].reset();
        $('#wikaz-slide-id').val(0);
        $('#wikaz-background-image').val('');
        $('#wikaz-product-id').val('');
        $('#wikaz-image-preview img').hide().attr('src', '');
        $('#wikaz-image-preview .wikaz-image-placeholder').show();
        $('#wikaz-remove-image').hide();
        $('#wikaz-selected-product').hide();
        $('#wikaz-product-search').val('').show();
        $('#wikaz-is-active').prop('checked', true);
        $('input[name="layout"][value="full-bg"]').prop('checked', true);
    }

    /**
     * Populate form with slide data
     */
    function populateForm(data) {
        $('#wikaz-slide-id').val(data.id);
        $('#wikaz-title').val(data.title);
        $('#wikaz-subtitle').val(data.subtitle);
        $('#wikaz-button-text').val(data.button_text);
        $('#wikaz-button-url').val(data.button_url);
        $('#wikaz-is-active').prop('checked', data.is_active == 1);
        if (data.layout) {
            $(`input[name="layout"][value="${data.layout}"]`).prop('checked', true);
        }

        if (data.background_image) {
            $('#wikaz-background-image').val(data.background_image);
            $('#wikaz-image-preview img').attr('src', data.background_image).show();
            $('#wikaz-image-preview .wikaz-image-placeholder').hide();
            $('#wikaz-remove-image').show();
        }

        if (data.product_id && data.product) {
            $('#wikaz-product-id').val(data.product_id);
            $('#wikaz-product-search').hide();
            $('#wikaz-selected-product').show()
                .find('img').attr('src', data.product.image);
            $('#wikaz-selected-product .product-name').text(data.product.title);
        }
    }

    /**
     * Save slide
     */
    function saveSlide(e) {
        e.preventDefault();

        const $btn = $('#wikaz-save-slide');
        const originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + wikazAdmin.strings.saving).prop('disabled', true);

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_save_slide',
                nonce: wikazAdmin.nonce,
                slide_id: $('#wikaz-slide-id').val(),
                product_id: $('#wikaz-product-id').val(),
                title: $('#wikaz-title').val(),
                subtitle: $('#wikaz-subtitle').val(),
                background_image: $('#wikaz-background-image').val(),
                layout: $('input[name="layout"]:checked').val(),
                button_text: $('#wikaz-button-text').val(),
                button_url: $('#wikaz-button-url').val(),
                is_active: $('#wikaz-is-active').is(':checked') ? 1 : 0
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    const message = response.data && response.data.message ? response.data.message : wikazAdmin.strings.error;
                    alert(message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Save error:', error);
                alert(wikazAdmin.strings.error);
            },
            complete: function () {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Delete slide
     */
    function deleteSlide(slideId, $item) {
        if (!confirm(wikazAdmin.strings.confirmDelete)) {
            return;
        }

        $item.addClass('wikaz-loading');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_delete_slide',
                nonce: wikazAdmin.nonce,
                slide_id: slideId
            },
            success: function (response) {
                if (response.success) {
                    $item.slideUp(300, function () {
                        $(this).remove();
                        if (!$slidesList.find('.wikaz-slide-item').length) {
                            location.reload();
                        }
                    });
                }
            }
        });
    }

    /**
     * Toggle slide active status
     */
    function toggleSlide(slideId, isActive, $item) {
        $item.toggleClass('inactive', !isActive);

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_toggle_slide',
                nonce: wikazAdmin.nonce,
                slide_id: slideId,
                is_active: isActive ? 1 : 0
            }
        });
    }

    /**
     * Update sort order
     */
    function updateOrder() {
        const order = [];
        $slidesList.find('.wikaz-slide-item').each(function () {
            order.push($(this).data('id'));
        });

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_update_order',
                nonce: wikazAdmin.nonce,
                order: order
            }
        });
    }

    /**
     * Select image from media library
     */
    function selectImage(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: wikazAdmin.strings.selectImage,
            button: { text: wikazAdmin.strings.useImage },
            multiple: false
        });

        mediaUploader.on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            const imageUrl = attachment.sizes.large ? attachment.sizes.large.url : attachment.url;

            $('#wikaz-background-image').val(imageUrl);
            $('#wikaz-image-preview img').attr('src', imageUrl).show();
            $('#wikaz-image-preview .wikaz-image-placeholder').hide();
            $('#wikaz-remove-image').show();
        });

        mediaUploader.open();
    }

    /**
     * Remove selected image
     */
    function removeImage(e) {
        e.preventDefault();
        $('#wikaz-background-image').val('');
        $('#wikaz-image-preview img').hide().attr('src', '');
        $('#wikaz-image-preview .wikaz-image-placeholder').show();
        $('#wikaz-remove-image').hide();
    }

    /**
     * Search products
     */
    function searchProducts() {
        const search = $('#wikaz-product-search').val();
        const $results = $('#wikaz-product-results');

        if (search.length < 2) {
            $results.removeClass('active').empty();
            return;
        }

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_search_products',
                nonce: wikazAdmin.nonce,
                search: search
            },
            success: function (response) {
                if (response.success && response.data.length) {
                    let html = '';
                    response.data.forEach(function (product) {
                        html += `
                            <div class="wikaz-product-item" data-id="${product.id}" data-title="${product.title}" data-image="${product.image}" data-url="${product.url}">
                                <img src="${product.image}" alt="">
                                <div class="wikaz-product-item-info">
                                    <strong>${product.title}</strong>
                                    <span>${product.price}</span>
                                </div>
                            </div>
                        `;
                    });
                    $results.html(html).addClass('active');
                } else {
                    $results.removeClass('active').empty();
                }
            }
        });
    }

    /**
     * Select product
     */
    function selectProduct() {
        const $item = $(this);
        const productId = $item.data('id');
        const productTitle = $item.data('title');
        const productImage = $item.data('image');
        const productUrl = $item.data('url');

        $('#wikaz-product-id').val(productId);
        $('#wikaz-product-search').hide();
        $('#wikaz-product-results').removeClass('active');

        $('#wikaz-selected-product').show()
            .find('img').attr('src', productImage);
        $('#wikaz-selected-product .product-name').text(productTitle);

        // Auto-fill title and URL if empty
        if (!$('#wikaz-title').val()) {
            $('#wikaz-title').val(productTitle);
        }
        if (!$('#wikaz-button-url').val()) {
            $('#wikaz-button-url').val(productUrl);
        }
    }

    /**
     * Remove selected product
     */
    function removeProduct(e) {
        e.preventDefault();
        $('#wikaz-product-id').val('');
        $('#wikaz-selected-product').hide();
        $('#wikaz-product-search').val('').show();
    }

    /**
     * Save settings
     */
    function saveSettings(e) {
        e.preventDefault();

        const $btn = $settingsForm.find('.button-primary');
        const originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update spin"></span> ' + wikazAdmin.strings.saving).prop('disabled', true);

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_save_settings',
                nonce: wikazAdmin.nonce,
                autoplay: $('#wikaz-autoplay').is(':checked') ? 1 : 0,
                speed: $('#wikaz-speed').val(),
                position: $('#wikaz-position').val(),
                header_transparent: $('#wikaz-header-transparent').is(':checked') ? 1 : 0
            },
            success: function (response) {
                if (response.success) {
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + wikazAdmin.strings.saved);
                    setTimeout(function () {
                        $btn.html(originalText).prop('disabled', false);
                    }, 2000);
                }
            },
            error: function () {
                alert(wikazAdmin.strings.error);
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Debounce helper
     */
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Initialize when DOM ready
    $(document).ready(init);

})(jQuery);
