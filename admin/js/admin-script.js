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

        // Marquee events
        $('#add-marquee-item').on('click', addMarqueeItem);
        $(document).on('click', '.remove-marquee-item', removeMarqueeItem);
        $('#wikaz-marquee-form').on('submit', saveMarquee);

        // Close product results on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wikaz-product-search-wrap').length) {
                $('#wikaz-product-results').removeClass('active');
            }
        });

        // Product Manager Events
        $('#wikaz-add-pm-product').on('click', () => openPMProductModal());
        $('#wikaz-pm-search').on('input', debounce(() => loadPMProducts(1), 500));
        $(document).on('click', '.wikaz-pm-edit', function () { openPMProductModal($(this).data('id')); });
        $(document).on('click', '.wikaz-pm-delete', function () { deletePMProduct($(this).data('id')); });
        $(document).on('change', '.pm-term-item input', generateVariationMatrix);
        $pmModal.find('.wikaz-modal-close, .wikaz-modal-cancel').on('click', closePMModal);
        $pmForm.on('submit', savePMProduct);
        $('#pm-image-preview').on('click', selectPMImage);
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
            </div>
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
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + wikazAdmin.strings.saved);
                    setTimeout(function () {
                        $btn.html(originalText).prop('disabled', false);
                    }, 2000);
                } else {
                    alert(wikazAdmin.strings.error);
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function () {
                alert(wikazAdmin.strings.error);
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

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_get_pm_products',
                nonce: wikazAdmin.nonce,
                search: $('#wikaz-pm-search').val(),
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
            html = `<tr><td colspan="7" style="text-align:center;">No products found.</td></tr>`;
        } else {
            products.forEach(p => {
                html += `
                    <tr>
                        <td class="column-thumb"><img src="${p.image}" alt=""></td>
                        <td><strong>${p.name}</strong></td>
                        <td><code>${p.sku || '-'}</code></td>
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
            html += `<button type="button" class="button ${i === currentPage ? 'button-primary' : ''} pm-page-btn" data-page="${i}">${i}</button> `;
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
                                    ${attr.terms.map(term => `
                                        <label class="pm-term-item">
                                            <input type="checkbox" name="attr_${attr.slug}[]" value="${term.slug}" data-name="${term.name}">
                                            <span>${term.name}</span>
                                        </label>
                                    `).join('')}
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
                            $('#pm-product-video-url').val(p.video_url || '');

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
            $('#wikaz-pm-modal-title').text('Add New Product');
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
        $('#pm-image-preview img').hide().attr('src', '');
        $('#pm-image-preview .placeholder').show();
        $('.pm-gallery-item').remove();
        $('#pm-variation-matrix-wrap').hide();
        $('#pm-variation-matrix-body').empty();
        $('.pm-term-item input').prop('checked', false);
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

        if (attributes.length < 1) {
            $('#pm-variation-matrix-wrap').hide();
            return;
        }

        // Generate combinations (Cartesian Product)
        const combinations = attributes.reduce((a, b) => a.flatMap(d => b.selected.map(e => ({ ...d, [b.slug]: e }))), [{}]);

        const $tableBody = $('#pm-variation-matrix-body');
        $tableBody.empty();

        const baseSku = $('#pm-product-sku').val() || 'SKU';

        combinations.forEach((combo, idx) => {
            const labels = Object.values(combo).map(v => v.name).join(' / ');
            const skuSuffix = Object.values(combo).map(v => v.slug.toUpperCase()).join('-');

            // Create a clean combo object for matching (slug -> slug)
            const cleanCombo = {};
            Object.keys(combo).forEach(k => cleanCombo[k] = combo[k].slug);

            const html = `
                <tr data-combo='${JSON.stringify(cleanCombo)}'>
                    <td><strong>${labels}</strong></td>
                    <td><input type="text" class="pm-var-sku" data-idx="${idx}" value="${baseSku}-${skuSuffix}"></td>
                    <td><input type="number" class="pm-var-price" data-idx="${idx}" value="${$('#pm-product-price').val() || ''}"></td>
                    <td><input type="number" class="pm-var-stock" data-idx="${idx}" value="0"></td>
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
            // Re-generate combinations to ensure we have mapping
            const attrArray = Object.keys(attributes).map(slug => ({
                slug,
                selected: attributes[slug].map(s => ({ slug: s })) // Map to same structure as combinations generator
            }));

            // Simplified combination logic to match attributes array structure
            const combinations = Object.keys(attributes).reduce((acc, slug) => {
                const values = attributes[slug];
                if (acc.length === 0) return values.map(v => ({ [slug]: v }));
                return acc.flatMap(combo => values.map(v => ({ ...combo, [slug]: v })));
            }, []);

            $('#pm-variation-matrix-body tr').each(function (i) {
                const $row = $(this);
                variations.push({
                    attributes: combinations[i],
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
            video_url: $('#pm-product-video-url').val(),
            image_id: $('#pm-product-image-id').val(),
            gallery_ids: $('#pm-product-gallery-ids').val(),
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
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: () => {
                $btn.prop('disabled', false).html(originalHtml);
                $spinner.removeClass('is-active');
            }
        });
    }

    function deletePMProduct(id) {
        if (!confirm('Delete this product permanently from WooCommerce?')) return;

        $.ajax({
            url: wikazAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wikaz_delete_pm_product',
                nonce: wikazAdmin.nonce,
                product_id: id
            },
            success: function () {
                loadPMProducts();
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

            if (!confirm('Are you sure you want to delete this item?')) return;

            const $btn = $(this);
            const id = $btn.data('id');
            const type = $btn.data('type');
            const realType = $btn.data('type');
            let taxonomy = $btn.data('taxonomy');

            if (realType === 'category') taxonomy = 'product_cat';
            if (realType === 'tag') taxonomy = 'product_tag';

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
                    if (response.success) {
                        $btn.closest('tr').fadeOut();
                    } else {
                        alert('Error: ' + response.data);
                    }
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
                            html += `
                                <tr>
                                    <td><img src="${cat.image || ''}" width="40" height="40" style="object-fit:cover; border-radius:4px;"></td>
                                    <td><strong>${cat.name}</strong></td>
                                    <td><code>${cat.slug}</code></td>
                                    <td>${cat.count}</td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="category" data-id="${cat.id}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="category" data-id="${cat.id}"><span class="dashicons dashicons-trash"></span></button>
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
                            html += `
                                <tr>
                                    <td><strong>${tag.name}</strong></td>
                                    <td><code>${tag.slug}</code></td>
                                    <td>${tag.count}</td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="tag" data-id="${tag.id}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="tag" data-id="${tag.id}"><span class="dashicons dashicons-trash"></span></button>
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

    function loadMasterAttributes() {
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
                            <li data-slug="${attr.slug}" data-label="${attr.label}" data-id="${attr.id || 0}">
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

                    // Attribute Side List Click
                    $list.find('li').on('click', function (e) {
                        if ($(e.target).closest('.attr-actions').length) return;

                        const slug = $(this).data('slug');
                        const label = $(this).data('label');
                        $list.find('li').removeClass('active');
                        $(this).addClass('active');
                        $('#current-attribute-label').text(label);
                        $('.add-master-term').show().data('taxonomy', 'pa_' + slug);
                        loadMasterTerms('pa_' + slug);
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
                                    loadMasterAttributes();
                                } else {
                                    alert('Error: ' + response.data);
                                }
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

    function loadMasterTerms(taxonomy) {
        const $list = $('#master-terms-list');
        $list.html('<tr><td colspan="3" align="center">Loading values...</td></tr>');

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
                        html = '<tr><td colspan="3" align="center">No values found for this attribute.</td></tr>';
                    } else {
                        response.data.forEach(term => {
                            html += `
                                <tr>
                                    <td><strong>${term.name}</strong></td>
                                    <td><code>${term.slug}</code></td>
                                    <td class="column-actions">
                                        <button type="button" class="button button-small wikaz-edit-master" data-type="term" data-taxonomy="${taxonomy}" data-id="${term.id}"><span class="dashicons dashicons-edit"></span></button>
                                        <button type="button" class="button button-small wikaz-delete-master" data-type="term" data-taxonomy="${taxonomy}" data-id="${term.id}"><span class="dashicons dashicons-trash"></span></button>
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
            $('#master-image-group, #master-parent-group').hide();
            $('#master-attr-type-fields').show();
            $('#wikaz-master-modal-title').text(id > 0 ? 'Edit Attribute Type' : 'Add New Attribute Type');
        } else {
            $('#master-image-group, #master-parent-group').hide();
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
            return;
        }

        // Populate from the table row we clicked for speed
        const $row = $(`.wikaz-edit-master[data-id="${id}"][data-type="${type}"]`).closest('tr');
        $('#master-item-name').val($row.find('strong').text());
        $('#master-item-slug').val($row.find('code').text());

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
        };

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
                        loadMasterAttributes();
                        if (type === 'term') loadMasterTerms(formData.taxonomy);
                    }
                } else {
                    alert('Error: ' + response.data);
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

})(jQuery);
