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
    // Product Manager elements
    const $pmProductList = $('#wikaz-pm-product-list');
    const $pmModal = $('#wikaz-pm-modal');
    const $pmForm = $('#wikaz-pm-form');

    // Media uploader instance
    let mediaUploader;
    let pmAttrPromise;
    let pmSessionSuffix = '';

    /**
     * Initialize
     */
    function init() {
        initSortable();
        bindEvents();
        initColorPicker();
    }

    function initColorPicker() {
        if ($.fn.wpColorPicker) {
            $('.wikaz-color-picker').wpColorPicker();
        }
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

        // Save slide
        $slideForm.on('submit', saveSlide);

        // Save settings
        $settingsForm.on('submit', saveSettings);

        // Media upload (Delegated & Broadened)
        $(document).on('click', '#wikaz-select-image, #wikaz-image-preview', selectImage);
        $(document).on('click', '#wikaz-remove-image', removeImage);
        $(document).on('click', '#wikaz-select-video, #wikaz-video-preview', selectVideo);
        $(document).on('click', '#wikaz-remove-video', removeVideo);
        $(document).on('change input', '#wikaz-background-video', debounce(updateVideoPreview, 500));

        // Media Type Toggle
        $('#wikaz-slide-form input[name="media_type"]').on('change', toggleMediaType);

        // Link Source Toggle
        $('#wikaz-slide-form input[name="link_source"]').on('change', toggleLinkSource);

        // Product search (Delegated)
        $(document).on('input', '#wikaz-product-search, #hs-product-search', debounce(searchProducts, 300));
        $('#wikaz-product-results, #hs-product-results').on('click', '.product-result-item', selectItem);

        // Post search (Delegated)
        $(document).on('input', '#wikaz-post-search, #hs-post-search', debounce(searchPosts, 300));
        $('#wikaz-post-results, #hs-post-results').on('click', '.product-result-item', selectItem);
        $(document).on('click', '.remove-product', removeItem);

        // Marquee events
        $('#add-marquee-item').on('click', addMarqueeItem);
        $(document).on('click', '.remove-marquee-item', removeMarqueeItem);
        $('#wikaz-marquee-form').on('submit', saveMarquee);

        // Close product results on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wikaz-product-search-wrap').length) {
                $('.wikaz-product-results').removeClass('active');
            }
        });

        // Product Manager Events
        $('#wikaz-add-pm-product').on('click', () => openPMProductModal());
        $('#wikaz-pm-search').on('input', debounce(() => loadPMProducts(1), 500));
        $(document).on('click', '.pm-cat-tab', function () {
            $('.pm-cat-tab').removeClass('active');
            $(this).addClass('active');
            loadPMProducts(1);
        });
        $(document).on('click', '.wikaz-pm-edit', function () { openPMProductModal($(this).data('id')); });
        $(document).on('click', '.wikaz-pm-delete', function () { deletePMProduct($(this).data('id'), $(this)); });
        $(document).on('change', '.pm-term-item input', generateVariationMatrix);
        $pmModal.find('.wikaz-modal-close, .wikaz-modal-cancel').on('click', closePMModal);
        $pmForm.on('submit', savePMProduct);
        $(document).on('click', '#pm-image-preview', selectPMImage);
        $(document).on('click', '#pm-rsfv-video-preview', selectRsfvVideo);
        $(document).on('click', '#pm-rsfv-poster-preview', selectRsfvPoster);
        $('#pm-add-gallery-item').on('click', selectPMGalleryImages);
        $(document).on('click', '.pm-gallery-remove', function () { $(this).parent().remove(); updateGalleryIDs(); });

        // Initial Load
        if ($pmProductList.length) {
            loadPMProducts();
            pmAttrPromise = loadPMAttributes();
        }

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
        $('#wikaz-background-video').val('');
        $('#wikaz-product-id').val('');
        $('#wikaz-post-id').val('');
        $('#wikaz-selected-product, #wikaz-selected-post').hide();
        $('#wikaz-product-search, #wikaz-post-search').val('').show();
        $('.wikaz-product-results').removeClass('active').empty();
        $('#wikaz-image-preview img').hide().attr('src', '');
        $('#wikaz-image-preview .wikaz-image-placeholder').show();
        $('#wikaz-remove-image').hide();
        $('#wikaz-video-preview video').hide().attr('src', '');
        $('#wikaz-video-preview .wikaz-video-placeholder').show();
        $('#wikaz-remove-video').hide();

        // Reset Media Type to Image
        $('input[name="media_type"][value="image"]').prop('checked', true).trigger('change');

        $('#wikaz-title').val('');
        $('#wikaz-subtitle').val('');
        $('#wikaz-description').val('');
        $('#wikaz-is-active').prop('checked', true);
        $('input[name="layout"][value="full-bg"]').prop('checked', true);
    }

    /**
     * Toggle Media Type
     */
    function toggleMediaType() {
        const $form = $('#wikaz-slide-form');
        const type = $form.find('input[name="media_type"]:checked').val();
        $form.find('.wikaz-media-section').hide();
        $form.find('#section-media-' + type).show();
    }

    /**
     * Toggle Link Source
     */
    function toggleLinkSource() {
        const source = $(this).val();
        if (source === 'post') {
            $('#wikaz-post-search-group').show();
            $('#wikaz-product-search-group').hide();
            $('#wikaz-button-text').val('Read More');
        } else {
            $('#wikaz-post-search-group').hide();
            $('#wikaz-product-search-group').show();
            $('#wikaz-button-text').val('Shop Now');
        }
    }

    /**
     * Populate form with slide data
     */
    function populateForm(data) {
        $('#wikaz-slide-id').val(data.id);
        $('#wikaz-title').val(data.title);
        $('#wikaz-subtitle').val(data.subtitle);
        $('#wikaz-description').val(data.description);
        $('#wikaz-button-text').val(data.button_text);
        $('#wikaz-button-url').val(data.button_url);
        $('#wikaz-is-active').prop('checked', data.is_active == 1);
        if (data.layout) {
            $(`input[name="layout"][value="${data.layout}"]`).prop('checked', true);
        }

        // Media Type Detection
        let mediaType = data.media_type || (data.background_video ? 'video' : 'image');
        $('input[name="media_type"][value="' + mediaType + '"]').prop('checked', true).trigger('change');

        if (data.background_image) {
            $('#wikaz-background-image').val(data.background_image);
            $('#wikaz-image-preview img').attr('src', data.background_image).show();
            $('#wikaz-image-preview .wikaz-image-placeholder').hide();
            $('#wikaz-remove-image').show();
        }

        if (data.background_video) {
            $('#wikaz-background-video').val(data.background_video).trigger('change');
            $('#wikaz-video-preview video').attr('src', data.background_video).show();
            $('#wikaz-video-preview .wikaz-video-placeholder').hide();
            $('#wikaz-remove-video').show();
        } else {
            $('#wikaz-video-preview video').hide().attr('src', '');
            $('#wikaz-video-preview .wikaz-video-placeholder').show();
            $('#wikaz-remove-video').hide();
        }

        // Link Source Detection
        let linkSource = data.link_source || (data.post_id ? 'post' : 'product');
        $(`input[name="link_source"][value="${linkSource}"]`).prop('checked', true).trigger('change');

        if (linkSource === 'product' && (data.product_id || data.product)) {
            const product = data.product || {};
            $('#wikaz-product-id').val(data.product_id || product.id);
            $('#wikaz-product-search').hide().parent().find('label').hide();
            $('#wikaz-selected-product').show()
                .find('img').attr('src', product.image || '');
            $('#wikaz-selected-product .product-name').text(product.title || 'Product ID: ' + (data.product_id || product.id));
        } else if (linkSource === 'post' && (data.post_id || data.post)) {
            const post = data.post || {};
            $('#wikaz-post-id').val(data.post_id || post.id);
            $('#wikaz-post-search').hide().parent().find('label').hide();
            $('#wikaz-selected-post').show()
                .find('img').attr('src', post.image || '');
            $('#wikaz-selected-post .product-name').text(post.title || 'Post ID: ' + (data.post_id || post.id));
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
                post_id: $('#wikaz-post-id').val(),
                title: $('#wikaz-title').val(),
                subtitle: $('#wikaz-subtitle').val(),
                description: $('#wikaz-description').val(),
                background_image: $('#wikaz-background-image').val(),
                background_video: $('#wikaz-background-video').val(),
                media_type: $('input[name="media_type"]:checked').val(),
                link_source: $('input[name="link_source"]:checked').val(),
                layout: $('input[name="layout"]:checked').val(),
                button_text: $('#wikaz-button-text').val(),
                button_url: $('#wikaz-button-url').val(),
                is_active: $('#wikaz-is-active').is(':checked') ? 1 : 0
            },
            success: function (response) {
                if (response.success) {
                    showNotification('Slide saved successfully!', 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    const message = response.data && response.data.message ? response.data.message : wikazAdmin.strings.error;
                    showNotification(message, 'error');
                }
            },
            error: function (xhr, status, error) {
                console.error('Save error:', error);
                showNotification(wikazAdmin.strings.error, 'error');
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
                    showNotification('Slide deleted successfully!', 'success');
                    $item.slideUp(300, function () {
                        $(this).remove();
                        if (!$slidesList.find('.wikaz-slide-item').length) {
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    });
                } else {
                    showNotification('Error deleting slide', 'error');
                }
            },
            error: function () {
                showNotification('Error deleting slide', 'error');
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
     * Select video from media library
     */
    function selectVideo(e) {
        e.preventDefault();

        let videoUploader = wp.media({
            title: 'Select Background Video',
            button: { text: 'Use this video' },
            library: { type: 'video' },
            multiple: false
        });

        videoUploader.on('select', function () {
            const attachment = videoUploader.state().get('selection').first().toJSON();
            const videoUrl = attachment.url;
            $('#wikaz-background-video').val(videoUrl).trigger('change');
        });

        videoUploader.open();
    }

    /**
     * Remove selected video
     */
    function removeVideo(e) {
        e.preventDefault();
        $('#wikaz-background-video').val('').trigger('change');
    }

    /**
     * Update video preview based on input URL
     */
    function updateVideoPreview() {
        const url = $('#wikaz-background-video').val().trim();
        const $preview = $('#wikaz-video-preview');
        const $placeholder = $preview.find('.wikaz-video-placeholder');

        // Clear existing preview content (except placeholder)
        $preview.find('video, iframe').remove();

        if (!url) {
            $placeholder.show();
            $('#wikaz-remove-video').hide();
            return;
        }

        $('#wikaz-remove-video').show();
        $placeholder.hide();

        let embedHtml = '';
        let match;

        // YouTube
        if (match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i)) {
            const videoId = match[1];
            const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=0&controls=1&showinfo=0&modestbranding=1`;
            embedHtml = `<iframe src="${embedUrl}" style="width:100%; height:100%; aspect-ratio:16/9;" frameborder="0" allowfullscreen></iframe>`;
        }
        // TikTok
        else if (match = url.match(/tiktok\.com\/.*\/video\/(\d+)/i)) {
            const videoId = match[1];
            const embedUrl = `https://www.tiktok.com/embed/v2/${videoId}`;
            embedHtml = `<iframe src="${embedUrl}" style="width:100%; height:100%; aspect-ratio:9/16;" frameborder="0" allowfullscreen></iframe>`;
        }
        // Local / Standard Video
        else {
            embedHtml = `<video src="${url}" style="width:100%; height:100%;" controls></video>`;
        }

        $preview.prepend(embedHtml);
    }

    /**
     * Search products
     */
    function searchProducts() {
        const $input = $(this);
        const search = $input.val();
        const isHeaderSlider = $input.attr('id') === 'hs-product-search';
        const $results = isHeaderSlider ? $('#hs-product-results') : $('#wikaz-product-results');

        if (search.length < 2) {
            $results.removeClass('active').empty();
            return;
        }

        // Show loading state
        $results.addClass('active').html('<div class="wikaz-search-loading"><span class="dashicons dashicons-update spin"></span> ' + wikazAdmin.strings.searching + '</div>');

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
                            <div class="product-result-item" data-id="${product.id}" data-title="${product.title}" data-image="${product.image}" data-url="${product.url}" data-type="product" data-origin="${isHeaderSlider ? 'hs' : 'wikaz'}">
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
                    $results.html('<div class="wikaz-search-no-results">No products found</div>');
                }
            }
        });
    }

    /**
     * Select product or post
     */
    /**
     * Select product or post
     */
    function selectItem() {
        const $item = $(this);
        const id = $item.data('id');
        const title = $item.data('title');
        const image = $item.data('image');
        const url = $item.data('url');
        const type = $item.data('type'); // product or post
        const origin = $item.data('origin') || 'wikaz'; // 'wikaz' or 'hs'

        const prefix = origin === 'hs' ? '#hs' : '#wikaz';

        if (type === 'post') {
            $(prefix + '-post-id').val(id);
            $(prefix + '-post-search').hide();
            $(prefix + '-post-results').removeClass('active');
            $(prefix + '-selected-post').show().find('img').attr('src', image);
            $(prefix + '-selected-post .product-name').text(title);
        } else {
            $(prefix + '-product-id').val(id);
            $(prefix + '-product-search').hide();
            $(prefix + '-product-results').removeClass('active');
            $(prefix + '-selected-product').show().find('img').attr('src', image);
            $(prefix + '-selected-product .product-name').text(title);
        }

        // Auto-fill title and URL based on origin
        if (origin === 'hs') {
            // Header Slider uses name attributes within form
            const $form = $('#wikaz-header-slide-form');
            if (!$form.find('[name="title"]').val()) {
                $form.find('[name="title"]').val(title);
            }
            // Always replace URL
            $form.find('[name="button_url"]').val(url);

            // Also update button text if empty or system default
            const currentBtnText = $form.find('[name="button_text"]').val();
            if (!currentBtnText || currentBtnText === 'Shop Now' || currentBtnText === 'Read More') {
                $form.find('[name="button_text"]').val(type === 'post' ? 'Read More' : 'Shop Now');
            }
        } else {
            // Home Carousel uses specific IDs
            if (!$('#wikaz-title').val()) {
                $('#wikaz-title').val(title);
            }
            // Always replace URL
            $('#wikaz-button-url').val(url);

            // Also update button text if empty or system default
            const currentBtnText = $('#wikaz-button-text').val();
            if (!currentBtnText || currentBtnText === 'Shop Now' || currentBtnText === 'Read More') {
                $('#wikaz-button-text').val(type === 'post' ? 'Read More' : 'Shop Now');
            }
        }
    }

    /**
     * Remove selected item
     */
    function removeItem(e) {
        e.preventDefault();
        const $btn = $(this);
        const type = $btn.data('type') === 'post' ? 'post' : 'product';

        // Determine origin by checking parent container ID
        const parentId = $btn.closest('div[id$="-selected-product"], div[id$="-selected-post"]').attr('id');
        const origin = parentId.indexOf('hs-') === 0 ? 'hs' : 'wikaz';
        const prefix = origin === 'hs' ? '#hs' : '#wikaz';

        if (type === 'post') {
            $(prefix + '-post-id').val('');
            $(prefix + '-selected-post').hide();
            $(prefix + '-post-search').val('').show();
        } else {
            $(prefix + '-product-id').val('');
            $(prefix + '-selected-product').hide();
            $(prefix + '-product-search').val('').show();
        }
    }

    /**
     * Search Posts
     */
    function searchPosts() {
        const $input = $(this);
        const search = $input.val();
        const isHeaderSlider = $input.attr('id') === 'hs-post-search';
        const $results = isHeaderSlider ? $('#hs-post-results') : $('#wikaz-post-results');

        if (search.length < 2) {
            $results.removeClass('active').empty();
            return;
        }

        // Show loading state
        $results.addClass('active').html('<div class="wikaz-search-loading"><span class="dashicons dashicons-update spin"></span> ' + wikazAdmin.strings.searching + '</div>');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_search_posts',
                nonce: wikazAdmin.nonce,
                search: search
            },
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(post => {
                        html += `
                            <div class="product-result-item" data-id="${post.id}" data-title="${post.title}" data-image="${post.image}" data-url="${post.url}" data-type="post" data-origin="${isHeaderSlider ? 'hs' : 'wikaz'}">
                                <img src="${post.image}" alt="">
                                <span>${post.title}</span>
                            </div>
                        `;
                    });
                    $results.html(html).addClass('active');
                } else {
                    $results.html('<div class="wikaz-search-no-results">No posts found</div>');
                }
            }
        });
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
                    showNotification('Settings saved successfully!', 'success');
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + wikazAdmin.strings.saved);
                    setTimeout(function () {
                        $btn.html(originalText).prop('disabled', false);
                    }, 2000);
                } else {
                    showNotification('Error saving settings', 'error');
                }
            },
            error: function () {
                showNotification(wikazAdmin.strings.error, 'error');
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Add new marquee item row
     */
    function addMarqueeItem() {
        const $container = $('#marquee-items-container');
        const index = $container.find('.marquee-item-row').length;

        const html = `
                <div class="marquee-item-row" data-index="${index}">
                <div class="wikaz-form-group">
                    <label>Text</label>
                    <input type="text" name="marquee_items[${index}][text]" value="" class="widefat" placeholder="Scrolling text...">
                </div>
                <div class="wikaz-form-group">
                    <label>Link</label>
                    <input type="text" name="marquee_items[${index}][link]" value="" class="widefat" placeholder="https://...">
                </div>
                <button type="button" class="button remove-marquee-item" title="Remove">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div >
                        `;

        $container.append(html);
    }

    /**
     * Remove marquee item row
     */
    function removeMarqueeItem() {
        $(this).closest('.marquee-item-row').remove();

        // Re-index remaining rows
        $('#marquee-items-container .marquee-item-row').each(function (i) {
            $(this).attr('data-index', i);
            $(this).find('input').each(function () {
                const name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, `[${i}]`));
            });
        });
    }

    /**
     * Save marquee settings
     */
    function saveMarquee(e) {
        e.preventDefault();

        const $form = $(this);
        const $btn = $('#save-marquee');
        const originalText = $btn.html();
        const $spinner = $form.find('.spinner');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        const formData = {
            action: 'wikaz_save_marquee',
            nonce: wikazAdmin.nonce,
            marquee_items: []
        };

        $form.find('.marquee-item-row').each(function () {
            formData.marquee_items.push({
                text: $(this).find('input[name*="[text]"]').val(),
                link: $(this).find('input[name*="[link]"]').val()
            });
        });

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    showNotification('Marquee saved successfully!', 'success');
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + wikazAdmin.strings.saved);
                    setTimeout(function () {
                        $btn.html(originalText).prop('disabled', false);
                    }, 2000);
                } else {
                    showNotification(wikazAdmin.strings.error, 'error');
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function () {
                showNotification(wikazAdmin.strings.error, 'error');
                $btn.html(originalText).prop('disabled', false);
            },
            complete: function () {
                $spinner.removeClass('is-active');
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

    /**
     * Product Manager: Load Products
     */
    function loadPMProducts(page = 1) {
        const $loader = $('#wikaz-pm-loader');
        $loader.show();

        const category = $('.pm-cat-tab.active').data('category') || 'all';

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_pm_products',
                nonce: wikazAdmin.nonce,
                search: $('#wikaz-pm-search').val(),
                category: category,
                page: page
            },
            success: function (response) {
                if (response.success) {
                    renderPMProductList(response.data.products);
                    renderPMPagination(response.data.total_pages, page);
                }
            },
            complete: () => $loader.hide()
        });
    }

    function renderPMProductList(products) {
        let html = '';
        if (!products.length) {
            html = `<tr><td colspan="8" style="text-align:center;">No products found.</td></tr>`;
        } else {
            products.forEach(p => {
                const isVideo = (url) => {
                    if (!url) return false;
                    const ext = url.split('.').pop().toLowerCase().split(/[?#]/)[0];
                    return ['mp4', 'webm', 'ogg', 'mov'].includes(ext);
                };

                let thumbHtml = `<img src="${p.image}" alt="">`;
                if (p.video_url) {
                    if (isVideo(p.video_url)) {
                        thumbHtml = `<video src="${p.video_url}" muted loop autoplay style="width:45px;height:45px;object-fit:cover;border-radius:8px;"></video>`;
                    } else if (p.rsfv_source === 'embed') {
                        // For embed, just show a video icon or the main image. RSFV doesn't easily give us the thumbnail URL here without more PHP work.
                        thumbHtml = `<div style="width:45px;height:45px;background:#f0f0f1;border-radius:8px;display:flex;align-items:center;justify-content:center;position:relative;">
                            <img src="${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;opacity:0.6;">
                            <span class="dashicons dashicons-video-alt3" style="position:absolute;color:#fff;text-shadow:0 0 3px rgba(0,0,0,0.5);"></span>
                        </div>`;
                    }
                }

                html += `
                        <tr>
                        <td class="column-thumb">${thumbHtml}</td>
                        <td><strong>${p.name}</strong></td>
                        <td><code>${p.sku || '-'}</code></td>
                        <td><span class="pm-status-badge status-${p.status}">${p.status.toUpperCase()}</span></td>
                        <td><small>${p.type.toUpperCase()}</small></td>
                        <td>Rp ${p.price || 0}</td>
                        <td>${p.stock !== null ? p.stock : '∞'}</td>
                        <td class="column-actions">
                            <button type="button" class="button button-small wikaz-pm-edit" data-id="${p.id}" title="Edit"><span class="dashicons dashicons-edit"></span></button>
                            <button type="button" class="button button-small wikaz-pm-delete" data-id="${p.id}" title="Delete" style="color:#a00;"><span class="dashicons dashicons-trash"></span></button>
                        </td>
                    </tr>
                        `;
            });
        }
        $pmProductList.html(html);
    }

    function renderPMPagination(totalPages, currentPage) {
        if (totalPages <= 1) { $('#wikaz-pm-pagination').empty(); return; }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button type="button" class="button ${i === currentPage ? 'button-primary' : ''} pm-page-btn" data-page="${i}">${i}</button>`;
        }
        $('#wikaz-pm-pagination').html(html).find('.pm-page-btn').on('click', function () {
            loadPMProducts($(this).data('page'));
        });
    }

    /**
     * Product Manager: Load Attributes
     */
    function loadPMAttributes() {
        const $container = $('#pm-attributes-container');
        return $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: { action: 'wikaz_get_pm_attributes', nonce: wikazAdmin.nonce },
            success: function (response) {
                if (response.success && response.data.length) {
                    let html = '';
                    response.data.forEach(attr => {
                        html += `
                        <div class="pm-attribute-row" data-slug="${attr.slug}">
                                <span class="pm-attribute-label">${attr.label}</span>
                                <div class="pm-terms-grid">
                                    ${attr.terms.map(term => {
                            const isColor = attr.type === 'color';
                            const style = isColor ? `style="background-color: ${term.color || '#ffffff'}"` : '';
                            return `
                                        <label class="pm-term-item ${isColor ? 'is-color' : ''}" title="${term.name}">
                                            <input type="checkbox" name="attr_${attr.slug}[]" value="${term.slug}" data-name="${term.name}">
                                            <span ${style}>${isColor ? '' : term.name}</span>
                                        </label>
                                    `;
                        }).join('')}
                                </div>
                            </div>
                        `;
                    });
                    $container.html(html);
                }
            }
        });
    }

    /**
     * Product Manager: Modal Logic
     */
    function openPMProductModal(productId = 0) {
        resetPMForm();
        initSelect2($pmModal);
        if (productId > 0) {
            pmSessionSuffix = productId.toString();
            $('#wikaz-pm-modal-title').text('Edit Product');
            $('#pm-product-id').val(productId);

            $('.pm-gallery-item').remove(); // Ensure clean start

            // Show loading state
            $pmForm.addClass('wikaz-loading');

            $.when(pmAttrPromise).done(function () {
                $.ajax({
                    url: wikazAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wikaz_get_pm_product',
                        nonce: wikazAdmin.nonce,
                        product_id: productId
                    },
                    success: function (response) {
                        if (response.success) {
                            const p = response.data;
                            $('#pm-product-name').val(p.name);
                            $('#pm-product-sku').val(p.sku);
                            $('#pm-product-price').val(p.price);
                            $('#pm-product-short-description').val(p.short_description || '');
                            $('#pm-product-description').val(p.description || '');
                            $('#pm-product-category').val(p.categories ? p.categories.map(String) : []).trigger('change');
                            $('#pm-product-tags').val(p.tags ? p.tags.map(String) : []).trigger('change');
                            $('#pm-product-status').val(p.status || 'draft');

                            // Featured Video (RSFV) Population
                            const rsfvSource = p.rsfv_source || 'self';
                            $(`input[name="pm_rsfv_source"][value="${rsfvSource}"]`).prop('checked', true).trigger('change');

                            if (p.rsfv_video_id) {
                                $('#pm-rsfv-video-id').val(p.rsfv_video_id);
                                $('#pm-rsfv-video-preview video').attr('src', p.rsfv_video_url).show();
                                $('#pm-rsfv-video-preview .placeholder').hide();
                            } else {
                                $('#pm-rsfv-video-id').val('');
                                $('#pm-rsfv-video-preview video').hide().attr('src', '');
                                $('#pm-rsfv-video-preview .placeholder').show();
                            }

                            if (p.rsfv_poster_id) {
                                $('#pm-rsfv-poster-id').val(p.rsfv_poster_id);
                                $('#pm-rsfv-poster-preview img').attr('src', p.rsfv_poster_url).show();
                                $('#pm-rsfv-poster-preview .placeholder').hide();
                            } else {
                                $('#pm-rsfv-poster-id').val('');
                                $('#pm-rsfv-poster-preview img').hide().attr('src', '');
                                $('#pm-rsfv-poster-preview .placeholder').show();
                            }

                            if (p.rsfv_embed_url) {
                                $('#pm-rsfv-embed-url').val(p.rsfv_embed_url);
                            } else {
                                $('#pm-rsfv-embed-url').val('');
                            }

                            if (p.image_url) {
                                $('#pm-product-image-id').val(p.image_id);
                                $('#pm-image-preview img').attr('src', p.image_url).show();
                                $('#pm-image-preview .placeholder').hide();
                            }

                            // Populate Gallery
                            if (p.gallery_images) {
                                p.gallery_images.forEach(img => addGalleryThumbnail(img.id, img.url));
                                updateGalleryIDs();
                            }

                            // Populate Attributes
                            if (p.attributes) {
                                Object.keys(p.attributes).forEach(cleanSlug => {
                                    const options = p.attributes[cleanSlug];
                                    if (Array.isArray(options)) {
                                        options.forEach(optSlug => {
                                            $(`.pm-attribute-row[data-slug="${cleanSlug}"] input[value="${optSlug}"]`).prop('checked', true);
                                        });
                                    }
                                });
                            }

                            // Generate Matrix
                            generateVariationMatrix();

                            // Match variations by attributes
                            if (p.variations && p.variations.length > 0) {
                                p.variations.forEach(v => {
                                    // WooCommerce variation attributes can have various prefixes
                                    const cleanVarAttrs = {};
                                    Object.keys(v.attributes).forEach(k => {
                                        const ck = k.replace('attribute_pa_', '').replace('attribute_', '').replace('pa_', '');
                                        cleanVarAttrs[ck] = v.attributes[k];
                                    });

                                    $('#pm-variation-matrix-body tr').each(function () {
                                        const $row = $(this);
                                        const rowCombo = $row.data('combo');
                                        // Row combo is slug -> slug
                                        if (rowCombo && isMatch(rowCombo, cleanVarAttrs)) {
                                            $row.find('.pm-var-sku').val(v.sku);
                                            $row.find('.pm-var-price').val(v.price);
                                            $row.find('.pm-var-stock').val(v.stock);
                                        }
                                    });
                                });
                            }
                        }
                    },
                    complete: () => $pmForm.removeClass('wikaz-loading')
                });
            });
        } else {
            pmSessionSuffix = Math.random().toString(36).substring(2, 6).toUpperCase();
            $('#wikaz-pm-modal-title').text('Add New Product');

            // Auto-select active category if not "All"
            const $activeTab = $('.pm-cat-tab.active');
            if ($activeTab.length && $activeTab.data('category') !== 'all') {
                const activeCatId = $activeTab.data('id');
                if (activeCatId) {
                    $('#pm-product-category').val([activeCatId.toString()]).trigger('change');
                }
            }
        }
        $pmModal.addClass('active');
    }

    function closePMModal() { $pmModal.removeClass('active'); }

    function resetPMForm() {
        $pmForm[0].reset();
        $('#pm-product-id').val(0);
        $('#pm-product-image-id').val('');
        $('#pm-product-gallery-ids').val('');
        $('#pm-product-category, #pm-product-tags').val([]).trigger('change');
        $('#pm-product-status').val('draft');
        $('#pm-image-preview img').hide().attr('src', '');
        $('#pm-image-preview .placeholder').show();
        $('.pm-rsfv-section video, .pm-rsfv-section img').hide().attr('src', '');
        $('.pm-rsfv-section .placeholder').show();
        $('#pm-rsfv-video-id, #pm-rsfv-poster-id, #pm-rsfv-embed-url').val('');
        $('input[name="pm_rsfv_source"][value="self"]').prop('checked', true).trigger('change');
        $('.pm-gallery-item').remove();
        $('#pm-variation-matrix-wrap').hide();
        $('#pm-variation-matrix-body').empty();
        $('.pm-term-item input').prop('checked', false);
        $('#pm-product-price').val('0');
    }

    function selectPMImage() {
        if (wp.media.frames.pm_frame) { wp.media.frames.pm_frame.open(); return; }
        wp.media.frames.pm_frame = wp.media({ title: 'Select Product Image', button: { text: 'Use Image' }, multiple: false });
        wp.media.frames.pm_frame.on('select', function () {
            const attachment = wp.media.frames.pm_frame.state().get('selection').first().toJSON();
            $('#pm-product-image-id').val(attachment.id);
            $('#pm-image-preview img').attr('src', attachment.url).show();
            $('#pm-image-preview .placeholder').hide();
        });
        wp.media.frames.pm_frame.open();
    }

    function selectRsfvVideo(e) {
        e.preventDefault();
        if (wp.media.frames.pm_rsfv_video_frame) { wp.media.frames.pm_rsfv_video_frame.open(); return; }
        wp.media.frames.pm_rsfv_video_frame = wp.media({
            title: 'Select Featured Video',
            button: { text: 'Use Video' },
            library: { type: 'video' },
            multiple: false
        });
        wp.media.frames.pm_rsfv_video_frame.on('select', function () {
            const attachment = wp.media.frames.pm_rsfv_video_frame.state().get('selection').first().toJSON();
            $('#pm-rsfv-video-id').val(attachment.id);
            $('#pm-rsfv-video-preview video').attr('src', attachment.url).show();
            $('#pm-rsfv-video-preview .placeholder').hide();
        });
        wp.media.frames.pm_rsfv_video_frame.open();
    }

    function selectRsfvPoster(e) {
        e.preventDefault();
        if (wp.media.frames.pm_rsfv_poster_frame) { wp.media.frames.pm_rsfv_poster_frame.open(); return; }
        wp.media.frames.pm_rsfv_poster_frame = wp.media({
            title: 'Select Poster Image',
            button: { text: 'Use Image' },
            library: { type: 'image' },
            multiple: false
        });
        wp.media.frames.pm_rsfv_poster_frame.on('select', function () {
            const attachment = wp.media.frames.pm_rsfv_poster_frame.state().get('selection').first().toJSON();
            $('#pm-rsfv-poster-id').val(attachment.id);

            $('#pm-rsfv-poster-preview img').attr('src', attachment.url).show();
            $('#pm-rsfv-poster-preview .placeholder').hide();
        });
        wp.media.frames.pm_rsfv_poster_frame.open();
    }

    $(document).on('change', 'input[name="pm_rsfv_source"]', function () {
        const val = $(this).val();
        if (val === 'self') {
            $('.pm-rsfv-self').show();
            $('.pm-rsfv-embed').hide();
        } else {
            $('.pm-rsfv-self').hide();
            $('.pm-rsfv-embed').show();
        }
    });

    $(document).on('click', '#pm-rsfv-video-uploader', selectRsfvVideo);
    $(document).on('click', '#pm-rsfv-poster-uploader', selectRsfvPoster);

    function selectPMGalleryImages() {
        const frame = wp.media({
            title: 'Select Gallery Images',
            button: { text: 'Add to Gallery' },
            multiple: 'add'
        });
        frame.on('select', function () {
            const selection = frame.state().get('selection');
            selection.map(attachment => {
                const img = attachment.toJSON();
                addGalleryThumbnail(img.id, img.url);
            });
            updateGalleryIDs();
        });
        frame.open();
    }

    function addGalleryThumbnail(id, url) {
        if ($(`.pm-gallery-item[data-id="${id}"]`).length) return;
        const html = `
                        <div class="pm-gallery-item" data-id="${id}">
                            <img src="${url}">
                                <button type="button" class="pm-gallery-remove">&times;</button>
                            </div>
                    `;
        $('#pm-add-gallery-item').before(html);
    }

    function updateGalleryIDs() {
        const ids = $('.pm-gallery-item').map(function () { return $(this).data('id'); }).get();
        $('#pm-product-gallery-ids').val(ids.join(','));
    }

    /**
     * Product Manager: Variation Matrix Logic
     */
    function generateVariationMatrix() {
        const attributes = [];
        $('.pm-attribute-row').each(function () {
            const $row = $(this);
            const selected = [];
            $row.find('input:checked').each(function () {
                selected.push({ slug: $(this).val(), name: $(this).data('name') });
            });
            if (selected.length) attributes.push({ slug: $row.data('slug'), selected: selected });
        });

        // Scrape existing data to preserve it
        const savedData = {};
        $('#pm-variation-matrix-body tr').each(function () {
            const $row = $(this);
            const combo = $row.data('combo');
            if (combo) {
                const key = JSON.stringify(combo);
                savedData[key] = {
                    sku: $row.find('.pm-var-sku').val(),
                    price: $row.find('.pm-var-price').val(),
                    stock: $row.find('.pm-var-stock').val()
                };
            }
        });

        if (attributes.length < 1) {
            $('#pm-variation-matrix-wrap').hide();
            return;
        }

        // Generate combinations (Cartesian Product)
        const combinations = attributes.reduce((a, b) => a.flatMap(d => b.selected.map(e => ({ ...d, [b.slug]: e }))), [{}]);

        const $tableBody = $('#pm-variation-matrix-body');
        $tableBody.empty();

        const baseSku = $('#pm-product-sku').val() || 'SKU';
        const basePrice = $('#pm-product-price').val() || '0';

        combinations.forEach((combo, idx) => {
            const labels = Object.values(combo).map(v => v.name).join(' / ');
            const skuSuffix = Object.values(combo).map(v => v.slug.toUpperCase()).join('-');

            // Create a clean combo object for matching (slug -> slug)
            const cleanCombo = {};
            Object.keys(combo).forEach(k => cleanCombo[k] = combo[k].slug);
            const comboKey = JSON.stringify(cleanCombo);

            const preserved = savedData[comboKey] || null;
            const sku = `${baseSku}-${skuSuffix}`;
            const price = (preserved && preserved.price && preserved.price !== '0') ? preserved.price : basePrice;
            const stock = preserved ? preserved.stock : '0';

            const html = `
                <tr data-combo='${comboKey}'>
                    <td><strong>${labels}</strong></td>
                    <td><input type="text" class="pm-var-sku" data-idx="${idx}" value="${sku}" readonly style="background: #f0f0f1; cursor: not-allowed;"></td>
                    <td><input type="number" class="pm-var-price" data-idx="${idx}" value="${price}"></td>
                    <td><input type="number" class="pm-var-stock" data-idx="${idx}" value="${stock}"></td>
                </tr>
            `;
            $tableBody.append(html);
        });

        $('#pm-variation-matrix-wrap').show();
    }

    function isMatch(obj1, obj2) {
        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);
        if (keys1.length !== keys2.length) return false;
        return keys1.every(key => obj1[key] === obj2[key]);
    }

    function savePMProduct(e) {
        e.preventDefault();

        const $btn = $('#pm-save-btn');
        const $spinner = $('.pm-save-spinner');
        const originalHtml = $btn.html();

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        // Collect attributes selection for variable products
        const attributes = {};
        $('.pm-attribute-row').each(function () {
            const $row = $(this);
            const selected = $row.find('input:checked').map(function () { return $(this).val(); }).get();
            if (selected.length) attributes[$row.data('slug')] = selected;
        });

        // Collect variations from matrix
        const variations = [];
        const baseSku = $('#pm-product-sku').val();

        if (Object.keys(attributes).length > 0) {
            $('#pm-variation-matrix-body tr').each(function () {
                const $row = $(this);
                variations.push({
                    attributes: $row.data('combo'),
                    sku: $row.find('.pm-var-sku').val(),
                    price: $row.find('.pm-var-price').val(),
                    stock: $row.find('.pm-var-stock').val()
                });
            });
        }

        const formData = {
            action: 'wikaz_save_pm_product',
            nonce: wikazAdmin.nonce,
            product_id: $('#pm-product-id').val(),
            name: $('#pm-product-name').val(),
            sku: baseSku,
            price: $('#pm-product-price').val(),
            short_description: $('#pm-product-short-description').val(),
            description: $('#pm-product-description').val(),
            categories: $('#pm-product-category').val(),
            tags: $('#pm-product-tags').val(),
            rsfv_source: $('input[name="pm_rsfv_source"]:checked').val(),
            rsfv_video_id: $('#pm-rsfv-video-id').val(),
            rsfv_poster_id: $('#pm-rsfv-poster-id').val(),
            rsfv_embed_url: $('#pm-rsfv-embed-url').val(),
            image_id: $('#pm-product-image-id').val(),
            gallery_ids: $('#pm-product-gallery-ids').val(),
            status: $('#pm-product-status').val(),
            attributes: attributes,
            variations: variations
        };

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    closePMModal();
                    loadPMProducts();
                    showNotification('Product saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            complete: () => {
                $btn.prop('disabled', false).html(originalHtml);
                $spinner.removeClass('is-active');
            }
        });
    }

    function generateSkuFromName(name) {
        if (!name) return '';
        const base = name.trim().toUpperCase()
            .replace(/&/g, 'AND')
            .replace(/[^A-Z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .substring(0, 25);

        return pmSessionSuffix ? `${base}-${pmSessionSuffix}` : base;
    }

    $('#pm-product-name').on('input', function () {
        const autoSku = generateSkuFromName($(this).val());
        $('#pm-product-sku').val(autoSku);

        // If variations exist, we should update them too
        if ($('#pm-variation-matrix-wrap').is(':visible')) {
            generateVariationMatrix();
        }
    });

    $('#pm-product-price').on('input', function () {
        const newVal = $(this).val();
        // Update all variation prices to match the master price
        $('.pm-var-price').val(newVal);
    });

    function deletePMProduct(id, $btn) {
        if (!confirm('Delete this product permanently from WooCommerce?')) return;

        // Save original button content and show loading state
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner is-active" style="margin:0; float:none;"></span>');
        $btn.closest('tr').css('opacity', '0.5');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_delete_pm_product',
                nonce: wikazAdmin.nonce,
                product_id: id
            },
            success: function (response) {
                if (response.success) {
                    $btn.closest('tr').fadeOut(300, function () {
                        $(this).remove();
                    });
                    showNotification('Product deleted successfully!', 'success');
                    loadPMProducts();
                } else {
                    showNotification('Error deleting product', 'error');
                }
            },
            error: function (xhr, status, error) {
                showNotification('Error koneksi: ' + error, 'error');
            },
            complete: function () {
                // Restore button state if row still exists
                $btn.prop('disabled', false).html(originalHtml);
                $btn.closest('tr').css('opacity', '1');
            }
        });
    }

    /**
     * Master Data Manager Logic
     */
    const $masterTabs = $('.pm-tabs-wrapper .nav-tab');
    const $masterContents = $('.pm-tab-content');

    function initMasterData() {
        if (!$('.master-data-container').length) return;

        // Tab Switching
        $masterTabs.on('click', function (e) {
            e.preventDefault();
            const tab = $(this).data('tab');
            $masterTabs.removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $masterContents.removeClass('active');
            $('#tab-' + tab).addClass('active');

            // Load initial data for tab if needed
            if (tab === 'categories') loadMasterCategories();
            if (tab === 'tags') loadMasterTags();
            if (tab === 'attributes') loadMasterAttributes();
        });

        // Delegate all master events on the container for reliability
        const $container = $('.master-data-container');

        // Add Item Buttons
        $container.on('click', '.add-master-item', function (e) {
            e.preventDefault();
            const type = $(this).data('type');
            openMasterModal(type);
        });

        // Add Master Term
        $container.on('click', '.add-master-term', function (e) {
            e.preventDefault();
            const taxonomy = $(this).data('taxonomy');
            openMasterModal('term', 0, taxonomy);
        });

        // Edit Item
        $container.on('click', '.wikaz-edit-master', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const type = $(this).data('type');
            const taxonomy = $(this).data('taxonomy') || '';
            openMasterModal(type, id, taxonomy);
        });

        // Delete Item
        $container.on('click', '.wikaz-delete-master', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const id = $btn.data('id');
            const type = $btn.data('type');
            const count = parseInt($btn.data('count')) || 0;
            let taxonomy = $btn.data('taxonomy');

            // Build confirmation message
            let confirmMsg = 'Are you sure you want to delete this item?';
            if (count > 0) {
                if (type === 'term') {
                    confirmMsg = `⚠️ WARNING: This attribute is used by ${count} product variation(s)!\n\nDeleting this will affect those products. Are you sure you want to continue?`;
                } else if (type === 'category') {
                    confirmMsg = `⚠️ WARNING: This category contains ${count} product(s)!\n\nDeleting this will remove the category from those products. Are you sure you want to continue?`;
                } else if (type === 'tag') {
                    confirmMsg = `⚠️ WARNING: This tag is used by ${count} product(s)!\n\nDeleting this will remove the tag from those products. Are you sure you want to continue?`;
                }
            }

            if (!confirm(confirmMsg)) return;

            // Set taxonomy based on type if not already set
            if (type === 'category') taxonomy = 'product_cat';
            if (type === 'tag') taxonomy = 'product_tag';

            // Save original button content and show loading state
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner is-active" style="margin:0; float:none;"></span>');
            $btn.closest('tr').css('opacity', '0.5');

            console.log('Deleting:', { id, type, taxonomy });

            $.ajax({
                url: wikazAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wikaz_delete_master_item',
                    nonce: wikazAdmin.nonce,
                    id: id,
                    taxonomy: taxonomy
                },
                success: function (response) {
                    console.log('Delete response:', response);
                    if (response.success) {
                        $btn.closest('tr').fadeOut(300, function () {
                            $(this).remove();
                        });
                        showNotification('Item deleted successfully!', 'success');

                        // Reload the list based on type
                        if (type === 'category') loadMasterCategories();
                        if (type === 'tag') loadMasterTags();
                        if (type === 'term') {
                            const currentSlug = $('#master-attributes-type-list li.active').data('slug');
                            const attrType = $('#master-attributes-type-list li.active').data('type');
                            if (currentSlug) {
                                loadMasterTerms('pa_' + currentSlug, attrType);
                            }
                        }
                    } else {
                        showNotification('Error menghapus item: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Delete AJAX error:', { xhr, status, error });
                    showNotification('Error koneksi: ' + error, 'error');
                },
                complete: function () {
                    // Restore button state
                    $btn.prop('disabled', false).html(originalHtml);
                    $btn.closest('tr').css('opacity', '1');
                }
            });
        });

        // Initial Load (Categories by default)
        loadMasterCategories();
    }

    function loadMasterCategories() {
        const $list = $('#master-categories-list');
        $list.html('<tr><td colspan="5" align="center">Loading...</td></tr>');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_master_categories',
                nonce: wikazAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (response.data.length === 0) {
                        html = '<tr><td colspan="5" align="center">No categories found.</td></tr>';
                    } else {
                        response.data.forEach(cat => {
                            const countBadge = cat.count > 0
                                ? `<span class="count-badge" style="background:#0073aa; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px;">${cat.count}</span>`
                                : `<span class="count-badge" style="background:#ddd; color:#666; padding:2px 8px; border-radius:10px; font-size:11px;">0</span>`;
                            html += `
                        <tr>
                                    <td><img src="${cat.image || ''}" width="40" height="40" style="object-fit:cover; border-radius:4px;"></td>
                                    <td><strong>${cat.name}</strong></td>
                                    <td><code>${cat.slug}</code></td>
                                    <td>${countBadge}</td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="category" data-id="${cat.id}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="category" data-id="${cat.id}" data-count="${cat.count}"><span class="dashicons dashicons-trash"></span></button>
                                    </td>
                                </tr>
                        `;
                        });
                    }
                    $list.html(html);
                }
            }
        });
    }

    function loadMasterTags() {
        const $list = $('#master-tags-list');
        $list.html('<tr><td colspan="4" align="center">Loading...</td></tr>');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_master_tags',
                nonce: wikazAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (response.data.length === 0) {
                        html = '<tr><td colspan="4" align="center">No tags found.</td></tr>';
                    } else {
                        response.data.forEach(tag => {
                            const countBadge = tag.count > 0
                                ? `<span class="count-badge" style="background:#0073aa; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px;">${tag.count}</span>`
                                : `<span class="count-badge" style="background:#ddd; color:#666; padding:2px 8px; border-radius:10px; font-size:11px;">0</span>`;
                            html += `
                        <tr>
                                    <td><strong>${tag.name}</strong></td>
                                    <td><code>${tag.slug}</code></td>
                                    <td>${countBadge}</td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="tag" data-id="${tag.id}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="tag" data-id="${tag.id}" data-count="${tag.count}"><span class="dashicons dashicons-trash"></span></button>
                                    </td>
                                </tr>
                        `;
                        });
                    }
                    $list.html(html);
                }
            }
        });
    }

    function loadMasterAttributes(reselectSlug = null) {
        if (!reselectSlug) {
            reselectSlug = $('#master-attributes-type-list li.active').data('slug');
        }

        const $list = $('#master-attributes-type-list');
        $list.html('<li>Loading attributes...</li>');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_pm_attributes',
                nonce: wikazAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(attr => {
                        html += `
                        <li data-slug="${attr.slug}" data-label="${attr.label}" data-type="${attr.type}" data-id="${attr.id || 0}">
                                <div class="attr-info">
                                    <span>${attr.label}</span>
                                    <span class="attr-count">${attr.terms.length}</span>
                                </div>
                                <div class="attr-actions" style="display:flex; gap:5px;">
                                    <span class="dashicons dashicons-edit edit-attr-type" title="Edit Type" style="cursor:pointer; font-size:16px;"></span>
                                    <span class="dashicons dashicons-trash delete-attr-type" title="Delete Type" style="cursor:pointer; font-size:16px;"></span>
                                </div>
                            </li>
                        `;
                    });
                    $list.html(html);

                    if (reselectSlug) {
                        const $target = $list.find(`li[data-slug="${reselectSlug}"]`);
                        if ($target.length) {
                            $target.addClass('active');
                            $('#current-attribute-label').text($target.data('label'));
                            $('.add-master-term').show().data('taxonomy', 'pa_' + reselectSlug).data('attr-type', $target.data('type'));
                        }
                    }

                    // Attribute Side List Click
                    $list.find('li').on('click', function (e) {
                        if ($(e.target).closest('.attr-actions').length) return;

                        const slug = $(this).data('slug');
                        const label = $(this).data('label');
                        const type = $(this).data('type');
                        $list.find('li').removeClass('active');
                        $(this).addClass('active');
                        $('#current-attribute-label').text(label);
                        $('.add-master-term').show().data('taxonomy', 'pa_' + slug).data('attr-type', type);
                        loadMasterTerms('pa_' + slug, type);
                    });

                    // Edit Attribute Type
                    $list.find('.edit-attr-type').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const $li = $(this).closest('li');
                        openMasterModal('attribute_type', $li.data('id'));
                    });

                    // Delete Attribute Type
                    $list.find('.delete-attr-type').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const $li = $(this).closest('li');
                        if (!confirm('Delete this attribute type and all its values?')) return;

                        $.ajax({
                            url: wikazAdmin.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'wikaz_delete_master_attribute_type',
                                nonce: wikazAdmin.nonce,
                                id: $li.data('id')
                            },
                            success: function (response) {
                                if (response.success) {
                                    showNotification('Attribute type deleted successfully!', 'success');
                                    loadMasterAttributes();
                                } else {
                                    showNotification('Error: ' + response.data, 'error');
                                }
                            },
                            error: function () {
                                showNotification('Error deleting attribute type', 'error');
                            }
                        });
                    });
                }
            }
        });
    }

    $('.add-attribute-type').on('click', function () {
        openMasterModal('attribute_type');
    });

    function loadMasterTerms(taxonomy, attrType = 'select') {
        const $list = $('#master-terms-list');
        // Add 1 more for count column
        const baseColspan = (attrType === 'color') ? 5 : 4;
        $list.html(`<tr><td colspan="${baseColspan}" align="center">Loading values...</td></tr>`);

        // Update table header if color
        const $thead = $list.closest('table').find('thead tr');

        // Ensure count column exists
        if (!$thead.find('.col-count').length) {
            $thead.find('th:last').before('<th class="col-count" width="80">Products</th>');
        }

        if (attrType === 'color') {
            if (!$thead.find('.col-color').length) {
                $thead.find('th:first').after('<th class="col-color" width="60">Color</th>');
            }
        } else {
            $thead.find('.col-color').remove();
        }

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_master_terms',
                nonce: wikazAdmin.nonce,
                taxonomy: taxonomy
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (response.data.length === 0) {
                        html = `<tr><td colspan="${baseColspan}" align="center">No values found for this attribute.</td></tr>`;
                    } else {
                        response.data.forEach(term => {
                            let colorCell = '';
                            if (attrType === 'color') {
                                colorCell = `<td><span class="color-swatch" style="background-color:${term.color || '#fff'}; border:1px solid #ddd; width:24px; height:24px; display:block; border-radius:4px;" title="${term.color}"></span></td>`;
                            }
                            const countBadge = term.count > 0
                                ? `<span class="count-badge" style="background:#0073aa; color:#fff; padding:2px 8px; border-radius:10px; font-size:11px;">${term.count}</span>`
                                : `<span class="count-badge" style="background:#ddd; color:#666; padding:2px 8px; border-radius:10px; font-size:11px;">0</span>`;
                            html += `
                        <tr>
                                    <td><strong>${term.name}</strong></td>
                                    ${colorCell}
                                    <td><code>${term.slug}</code></td>
                                    <td>${countBadge}</td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="term" data-taxonomy="${taxonomy}" data-id="${term.id}" data-color="${term.color || ''}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="term" data-taxonomy="${taxonomy}" data-id="${term.id}" data-count="${term.count}"><span class="dashicons dashicons-trash"></span></button>
                                    </td>
                                </tr>
                        `;
                        });
                    }
                    $list.html(html);
                }
            }
        });
    }

    // Modal Handling
    const $masterModal = $('#wikaz-master-modal');
    const $masterForm = $('#wikaz-master-form');

    function openMasterModal(type, id = 0, taxonomy = '') {
        resetMasterForm();
        initSelect2($masterModal);
        $('#master-item-type').val(type);
        $('#master-item-id').val(id);
        $('#master-item-taxonomy').val(taxonomy);

        const attrType = $('.add-master-term').data('attr-type') || 'select';
        $('#master-item-attr-type').val(attrType);

        $('#master-attr-type-fields').hide();

        if (type === 'category') {
            $('#master-image-group, #master-parent-group').show();
            $('#wikaz-master-modal-title').text(id > 0 ? 'Edit Category' : 'Add New Category');
            loadParentCategories(id);
        } else if (type === 'tag') {
            $('#master-image-group, #master-parent-group').hide();
            // Tags don't have parents or images
            $('#wikaz-master-modal-title').text(id > 0 ? 'Edit Tag' : 'Add New Tag');
        } else if (type === 'attribute_type') {
            $('#master-image-group, #master-parent-group, #master-color-group').hide();
            $('#master-attr-type-fields').show();
            $('#wikaz-master-modal-title').text(id > 0 ? 'Edit Attribute Type' : 'Add New Attribute Type');
        } else {
            // It's a term (value)
            $('#master-image-group, #master-parent-group, #master-attr-type-fields').hide();

            // Show color picker if attribute type is color
            const attrType = $('.add-master-term').data('attr-type');
            if (attrType === 'color') {
                $('#master-color-group').show();
                if ($('#master-term-color').data('wpColorPicker')) {
                    $('#master-term-color').wpColorPicker('color', '#ffffff');
                }
            } else {
                $('#master-color-group').hide();
            }

            $('#wikaz-master-modal-title').text(id > 0 ? 'Edit Value' : 'Add New Value');
        }

        if (id > 0) {
            fetchMasterItem(type, id, taxonomy);
        }

        $masterModal.addClass('active');
    }

    function resetMasterForm() {
        $masterForm[0].reset();
        $('#master-item-id').val(0);
        $('#master-item-image-id').val('');
        $('#master-image-preview img').hide().attr('src', '');
        $('#master-image-preview .placeholder').show();
        $('#master-item-parent').val(0).trigger('change');
        $('#master-attribute-type').val('select');
        if ($('#master-term-color').data('wpColorPicker')) {
            $('#master-term-color').wpColorPicker('color', '#ffffff');
        }
    }

    function closeMasterModal() {
        $masterModal.removeClass('active');
    }

    $('.wikaz-modal-close').on('click', closeMasterModal);

    // Image Upload for Category
    $('#master-image-preview').on('click', function () {
        const frame = wp.media({
            title: 'Select Category Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#master-item-image-id').val(attachment.id);
            $('#master-image-preview img').attr('src', attachment.url).show();
            $('#master-image-preview .placeholder').hide();
        });

        frame.open();
    });

    function loadParentCategories(excludeId = 0) {
        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_master_categories',
                nonce: wikazAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    let options = '<option value="0">None</option>';
                    response.data.forEach(cat => {
                        if (cat.id != excludeId) {
                            options += `<option value="${cat.id}">${cat.name}</option>`;
                        }
                    });
                    $('#master-item-parent').html(options).trigger('change');
                }
            }
        });
    }

    function fetchMasterItem(type, id, taxonomy) {
        if (type === 'attribute_type') {
            const $li = $(`li[data-id="${id}"]`);
            $('#master-item-name').val($li.data('label'));
            $('#master-item-slug').val($li.data('slug'));
            $('#master-attribute-type').val($li.data('type') || 'select');
            return;
        }

        // Populate from the table row we clicked for speed
        const $row = $(`.wikaz-edit-master[data-id="${id}"][data-type="${type}"]`).closest('tr');
        $('#master-item-name').val($row.find('strong').text());
        $('#master-item-slug').val($row.find('code').text());

        if (type === 'term') {
            const $editBtn = $(`.wikaz-edit-master[data-id="${id}"][data-type="term"]`);
            const color = $editBtn.attr('data-color') || $editBtn.data('color');

            if (color && color !== '' && color !== '#ffffff') {
                // Use setTimeout to ensure color group is visible and picker is ready
                setTimeout(function () {
                    const $colorInput = $('#master-term-color');

                    // Set the value directly on the input first
                    $colorInput.val(color);

                    // Try to update via wpColorPicker if available
                    try {
                        if ($colorInput.data('wpColorPicker')) {
                            $colorInput.wpColorPicker('color', color);
                        }
                        // Also try iris directly (underlying color picker)
                        if ($colorInput.data('a8cIris')) {
                            $colorInput.iris('color', color);
                        }
                    } catch (e) {
                        console.log('Color picker update error:', e);
                    }

                    // Update the color preview button background
                    $colorInput.closest('.wp-picker-container').find('.wp-color-result').css('background-color', color);
                }, 100);
            }
        }

        if (type === 'category') {
            const img = $row.find('img').attr('src');
            if (img && !img.includes('placeholder')) {
                $('#master-image-preview img').attr('src', img).show();
                $('#master-image-preview .placeholder').hide();
            }
        }
    }

    $masterForm.on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#master-save-btn');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Saving...');

        const type = $('#master-item-type').val();
        const action = (type === 'attribute_type') ? 'wikaz_save_master_attribute_type' : 'wikaz_save_master_item';

        const formData = {
            action: action,
            nonce: wikazAdmin.nonce,
            id: $('#master-item-id').val(),
            type: type,
            name: $('#master-item-name').val(),
            slug: $('#master-item-slug').val(),
            taxonomy: $('#master-item-taxonomy').val(),
            parent: $('#master-item-parent').val(),
            image_id: $('#master-item-image-id').val(),
            type_attr: $('#master-attribute-type').val(), // Use different key to avoid clash with 'type'
            color: $('#master-term-color').val(),
        };

        // If it's an attribute type, override the 'type' field that WooCommerce expects
        if (type === 'attribute_type') {
            formData.type = formData.type_attr;
        }

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    closeMasterModal();
                    // Reload current tab
                    const activeTab = $('.pm-tabs-wrapper .nav-tab-active').data('tab');
                    if (activeTab === 'categories') loadMasterCategories();
                    if (activeTab === 'tags') loadMasterTags();
                    if (activeTab === 'attributes') {
                        const currentSlug = $('#master-attributes-type-list li.active').data('slug');
                        loadMasterAttributes(currentSlug);
                        if (type === 'term') {
                            const attrType = $('#master-item-attr-type').val();
                            loadMasterTerms(formData.taxonomy, attrType);
                        }
                    }
                    showNotification('Data saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            complete: () => {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });


    function initSelect2($modal) {
        if ($.fn.select2) {
            $modal.find('.pm-select2').each(function () {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $modal
                });
            });
        }
    }

    // Initialize when DOM ready
    $(document).ready(function () {
        init();
        initMasterData();
    });

    function showNotification(message, type = 'success') {
        // Remove existing notification if any
        $('.wikaz-notification').remove();

        const icon = type === 'success' ? 'dashicons-yes' : 'dashicons-warning';
        const html = `
            <div class="wikaz-notification ${type}">
                <span class="wikaz-notification-icon dashicons ${icon}"></span>
                <div class="wikaz-notification-content">${message}</div>
            </div>
        `;

        const $notif = $(html).appendTo('body');

        // Trigger animation
        setTimeout(() => $notif.addClass('active'), 100);

        // Auto remove
        setTimeout(() => {
            $notif.removeClass('active');
            setTimeout(() => $notif.remove(), 400);
        }, 5000);
    }

    /* ==========================================
       SIMPLE POST MODULE
       ========================================== */

    const $spList = $('#wikaz-sp-list');
    const $spModal = $('#wikaz-sp-modal');
    const $spForm = $('#wikaz-sp-form');

    // Init if on the page
    if ($('.simple-post-manager').length) {
        initSimplePost();
    }

    function initSimplePost() {
        // Initial load
        loadSimplePosts();
        loadPostCategories();

        // Bind events
        $('#wikaz-add-simple-post').on('click', openSimplePostModal);
        $('#wikaz-sp-search').on('input', debounce(() => loadSimplePosts(1), 500));

        // Modal events
        $spModal.find('.wikaz-modal-close, .wikaz-modal-cancel').on('click', closeSimplePostModal);
        $spForm.on('submit', saveSimplePost);

        // Edit/Delete
        $(document).on('click', '.wikaz-sp-edit', function (e) { e.preventDefault(); editSimplePost($(this).data('id')); });
        $(document).on('click', '.wikaz-sp-delete', function (e) { e.preventDefault(); deleteSimplePost($(this).data('id'), $(this)); });

        // Image Uploader
        $('#sp-image-preview').on('click', selectSPImage);

        // Init Summernote
        $('#sp-post-content').summernote({
            placeholder: 'Write your content here...',
            tabsize: 2,
            height: 300,
            styleTags: ['p'],
            enterHtml: '<br>',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            dialogsInBody: true,
            callbacks: {
                onImageUpload: function (files) {
                    uploadSummernoteImage(files[0]);
                }
            }
        });
    }

    function loadPostCategories() {
        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_post_taxonomies',
                nonce: wikazAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    const cats = response.data.categories;
                    let options = '<option value="">Select Category</option>';
                    cats.forEach(c => {
                        options += `<option value="${c.id}">${c.name}</option>`;
                    });
                    $('#sp-post-category').html(options);
                }
            }
        });
    }

    function uploadSummernoteImage(file) {
        const data = new FormData();
        data.append('action', 'wikaz_upload_summernote_image');
        data.append('nonce', wikazAdmin.nonce);
        data.append('file', file);

        $.ajax({
            data: data,
            type: "POST",
            url: wikazAdmin.ajaxUrl,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    $('#sp-post-content').summernote('insertImage', response.data);
                } else {
                    showNotification('Image upload failed: ' + (response.data || 'Unknown error'), 'error');
                }
            },
            error: function () {
                showNotification('Image upload error', 'error');
            }
        });
    }

    function loadSimplePosts(page = 1) {
        const $loader = $('#wikaz-sp-loader');
        $loader.show();

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_simple_posts',
                nonce: wikazAdmin.nonce,
                search: $('#wikaz-sp-search').val(),
                page: page
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    if (response.data.posts && response.data.posts.length > 0) {
                        response.data.posts.forEach(p => {
                            const img = p.image ? `<img src="${p.image}">` : '<span class="dashicons dashicons-format-image" style="font-size:24px; color:#ccc; width:auto; height:auto;"></span>';
                            html += `
                                <tr data-id="${p.id}">
                                    <td class="column-thumb">${img}</td>
                                    <td class="column-title">
                                        <strong>${p.title}</strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="#" class="wikaz-sp-edit" data-id="${p.id}">Edit</a> | </span>
                                            <span class="view"><a href="${p.url}" target="_blank">View</a> | </span>
                                            <span class="trash"><a href="#" class="wikaz-sp-delete" data-id="${p.id}" style="color: #a00;">Delete</a></span>
                                        </div>
                                    </td>
                                    <td class="column-date">${p.date}</td>
                                    <td class="column-actions">
                                        <button class="button button-small wikaz-sp-edit" data-id="${p.id}">Edit</button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4">No posts found.</td></tr>';
                    }
                    $spList.html(html);

                    // Render Pagination
                    renderPagination(response.data.total_pages, page);
                }
            },
            complete: function () {
                $loader.hide();
            }
        });
    }

    function renderPagination(totalPages, currentPage) {
        const $pagination = $('#wikaz-sp-pagination');
        let html = '';

        if (totalPages > 1) {
            if (currentPage > 1) {
                html += `<button class="button">&laquo; Prev</button> `;
            }

            for (let i = 1; i <= totalPages; i++) {
                const activeClass = i === currentPage ? 'button-primary' : 'button-secondary';
                html += `<button class="button ${activeClass}">${i}</button> `;
            }

            if (currentPage < totalPages) {
                html += `<button class="button">Next &raquo;</button>`;
            }
        }

        $pagination.html(html);

        // Re-bind pagination clicks to avoid using inline onclick which might fail with scope issues
        $pagination.find('button').off('click').on('click', function (e) {
            e.preventDefault();
            const page = $(this).text().includes('Prev') ? currentPage - 1 :
                $(this).text().includes('Next') ? currentPage + 1 :
                    parseInt($(this).text());
            loadSimplePosts(page);
        });
    }

    function openSimplePostModal() {
        $('#sp-post-id').val(0);
        $('#sp-post-title').val('');
        $('#sp-post-content').summernote('code', ''); // Clear summernote
        $('#sp-post-image-id').val('');
        $('#sp-image-preview img').hide().attr('src', '');
        $('#sp-image-preview .placeholder').show();

        // Reset Category and Tags
        $('#sp-post-category').val('');
        $('#sp-post-tags').val('');

        $('#wikaz-sp-modal-title').text('Add New Post');
        $spModal.addClass('active');
    }

    function closeSimplePostModal() {
        $spModal.removeClass('active');
    }

    function editSimplePost(id) {
        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_simple_post',
                nonce: wikazAdmin.nonce,
                post_id: id
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#sp-post-id').val(data.id);
                    $('#sp-post-title').val(data.title);
                    $('#sp-post-content').summernote('code', data.content);

                    // Set Category and Tags
                    $('#sp-post-category').val(data.category_id);
                    $('#sp-post-tags').val(data.tags);

                    if (data.image_id) {
                        $('#sp-post-image-id').val(data.image_id);
                        $('#sp-image-preview img').attr('src', data.image_url).show();
                        $('#sp-image-preview .placeholder').hide();
                    } else {
                        $('#sp-post-image-id').val('');
                        $('#sp-image-preview img').hide().attr('src', '');
                        $('#sp-image-preview .placeholder').show();
                    }

                    $('#wikaz-sp-modal-title').text('Edit Post');
                    $spModal.addClass('active');
                }
            }
        });
    }

    function saveSimplePost(e) {
        e.preventDefault();
        const $btn = $('#sp-save-btn');
        const $spinner = $('.sp-save-spinner');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_save_simple_post',
                nonce: wikazAdmin.nonce,
                post_id: $('#sp-post-id').val(),
                title: $('#sp-post-title').val(),
                content: $('#sp-post-content').summernote('code'),
                image_id: $('#sp-post-image-id').val(),
                category_id: $('#sp-post-category').val(),
                tags: $('#sp-post-tags').val()
            },
            success: function (response) {
                if (response.success) {
                    showNotification('Post saved successfully!', 'success');
                    closeSimplePostModal();
                    loadSimplePosts();
                } else {
                    showNotification('Error: ' + response.data, 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    }

    function deleteSimplePost(id, $btn) {
        if (!confirm(wikazAdmin.strings.confirmDelete)) return;

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_delete_simple_post',
                nonce: wikazAdmin.nonce,
                post_id: id
            },
            success: function (response) {
                if (response.success) {
                    loadSimplePosts();
                } else {
                    showNotification('Error deleting post', 'error');
                }
            }
        });
    }

    function selectSPImage() {
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
            // Check if context is SP manager
            if ($spModal.hasClass('active')) {
                $('#sp-post-image-id').val(attachment.id);
                $('#sp-image-preview img').attr('src', attachment.url).show();
                $('#sp-image-preview .placeholder').hide();
            } else {
                // ... logic for other modules if shared
                const imageUrl = attachment.sizes.large ? attachment.sizes.large.url : attachment.url;
                $('#wikaz-background-image').val(imageUrl);
                $('#wikaz-image-preview img').attr('src', imageUrl).show();
            }
        });

        mediaUploader.open();
    }

})(jQuery);
