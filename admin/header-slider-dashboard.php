<?php
/**
 * Header Sliders Admin Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wikaz-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-images-alt2"></span>
        <?php _e('Header Sliders', 'keycreation-wikaz'); ?>
    </h1>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container">
        
        <!-- SECTION 1: Sliders List -->
        <div id="wikaz-slider-manager">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <button type="button" class="button button-primary" id="wikaz-add-slider">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add New Slider', 'keycreation-wikaz'); ?>
                    </button>
                    <span class="spinner" id="wikaz-slider-spinner"></span>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="manage-column"><?php _e('Name', 'keycreation-wikaz'); ?></th>
                        <th class="manage-column"><?php _e('Shortcode', 'keycreation-wikaz'); ?></th>
                        <th class="manage-column"><?php _e('Slides', 'keycreation-wikaz'); ?></th>
                        <th class="manage-column"><?php _e('Settings', 'keycreation-wikaz'); ?></th>
                        <th class="manage-column"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                    </tr>
                </thead>
                <tbody id="wikaz-sliders-list-body">
                    <tr><td colspan="5" align="center">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- SECTION 2: Slides Manager (Hidden by default) -->
        <div id="wikaz-slide-manager" style="display:none;">
            <div class="wikaz-header-bar" style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <button type="button" class="button" id="wikaz-back-to-sliders">
                    <span class="dashicons dashicons-arrow-left-alt"></span> <?php _e('Back', 'keycreation-wikaz'); ?>
                </button>
                <h2 id="wikaz-current-slider-name" style="margin:0;"></h2>
                <button type="button" class="page-title-action" id="wikaz-add-header-slide">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php _e('Add New Slide', 'keycreation-wikaz'); ?>
                </button>
            </div>

            <input type="hidden" id="wikaz-current-slider-id" value="">

            <div class="wikaz-slides-section">
                <div class="wikaz-slides-list" id="wikaz-header-slides-list">
                    <!-- Slides loaded via AJAX -->
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL: Slider Settings -->
<div id="wikaz-slider-modal" class="wikaz-modal">
    <div class="wikaz-modal-content" style="max-width: 500px;">
        <div class="wikaz-modal-header">
            <h2 id="wikaz-slider-modal-title"><?php _e('New Slider', 'keycreation-wikaz'); ?></h2>
            <button type="button" class="wikaz-modal-close">&times;</button>
        </div>
        <form id="wikaz-slider-form">
            <input type="hidden" name="id" id="wikaz-slider-id" value="0">
            <div class="wikaz-modal-body">
                <div class="wikaz-form-group">
                    <label for="wikaz-slider-name"><?php _e('Slider Name', 'keycreation-wikaz'); ?></label>
                    <input type="text" id="wikaz-slider-name" name="name" required class="widefat" placeholder="e.g. About Page Header">
                </div>
                <div class="wikaz-form-group">
                    <label for="wikaz-slider-autoplay">
                        <input type="checkbox" id="wikaz-slider-autoplay" name="autoplay" value="1" checked>
                        <?php _e('Enable Autoplay', 'keycreation-wikaz'); ?>
                    </label>
                </div>
                <div class="wikaz-form-group">
                    <label for="wikaz-slider-speed"><?php _e('Autoplay Speed (ms)', 'keycreation-wikaz'); ?></label>
                    <input type="number" id="wikaz-slider-speed" name="speed" value="5000" min="1000" step="100">
                </div>
                <div class="wikaz-modal-footer">
                    <button type="submit" class="button button-primary"><?php _e('Save Slider', 'keycreation-wikaz'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Slide Editor (Reuse structure usually, but unique IDs) -->
<div id="wikaz-header-slide-modal" class="wikaz-modal">
    <div class="wikaz-modal-content">
        <div class="wikaz-modal-header">
            <h2><?php _e('Edit Slide', 'keycreation-wikaz'); ?></h2>
            <button type="button" class="wikaz-modal-close">&times;</button>
        </div>
        <form id="wikaz-header-slide-form">
            <input type="hidden" name="slide_id" id="wikaz-header-slide-id" value="0">
            <input type="hidden" name="slider_id" id="wikaz-header-slide-slider-id" value="">
            
            <div class="wikaz-modal-body">
                <!-- Media Type -->
                 <div class="wikaz-form-group">
                    <label><?php _e('Media Type', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-media-type-selector">
                        <label class="wikaz-type-option"><input type="radio" name="media_type" value="image" checked> <?php _e('Image', 'keycreation-wikaz'); ?></label>
                        <label class="wikaz-type-option"><input type="radio" name="media_type" value="video"> <?php _e('Video', 'keycreation-wikaz'); ?></label>
                    </div>
                </div>

                <!-- Background Image -->
                <div class="wikaz-image-upload wikaz-media-section" id="hs-section-media-image">
                    <div class="wikaz-image-preview" id="hs-image-preview">
                        <img src="" style="display:none; max-height:150px;">
                        <div class="wikaz-image-placeholder"><span class="dashicons dashicons-format-image"></span><p>Select Image</p></div>
                    </div>
                    <input type="hidden" name="background_image" id="hs-background-image">
                    <button type="button" class="button" id="hs-select-image">Select Image</button>
                    <button type="button" class="button" id="hs-remove-image" style="display:none; color:#a00;">Remove</button>
                </div>

                <!-- Background Video -->
                <div class="wikaz-video-upload wikaz-media-section" id="hs-section-media-video" style="display:none;">
                    <div class="wikaz-video-preview" id="hs-video-preview">
                        <video src="" style="display:none;" muted loop autoplay></video>
                        <div class="wikaz-video-placeholder"><span class="dashicons dashicons-video-alt3"></span><p><?php _e('Click to select video or paste URL', 'keycreation-wikaz'); ?></p></div>
                    </div>
                    <div class="wikaz-video-input-group" style="margin-top: 10px; display: flex; gap: 10px;">
                        <input type="text" name="background_video" id="hs-background-video" class="widefat" placeholder="<?php _e('Video URL (YouTube/Vimeo/MP4)', 'keycreation-wikaz'); ?>">
                        <button type="button" class="button" id="hs-select-video"><?php _e('Select File', 'keycreation-wikaz'); ?></button>
                    </div>
                    <button type="button" class="button button-link-delete" id="hs-remove-video" style="display:none; margin-top:5px; color:#a00;"><?php _e('Remove Video', 'keycreation-wikaz'); ?></button>
                </div>

                <!-- Layout -->
                <div class="wikaz-form-group" style="margin-top:15px;">
                    <label><?php _e('Slide Layout', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-layout-selector">
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="full-bg" checked>
                            <div class="layout-preview layout-full-bg" title="<?php _e('Full Background', 'keycreation-wikaz'); ?>">
                                <div class="preview-box"></div>
                            </div>
                        </label>
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="split-left">
                            <div class="layout-preview layout-split-left" title="<?php _e('Split (Image Left)', 'keycreation-wikaz'); ?>">
                                <div class="preview-box"></div>
                                <div class="preview-text"></div>
                            </div>
                        </label>
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="split-right">
                            <div class="layout-preview layout-split-right" title="<?php _e('Split (Image Right)', 'keycreation-wikaz'); ?>">
                                <div class="preview-text"></div>
                                <div class="preview-box"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Content -->
                <div class="wikaz-form-group"><label>Subtitle</label><input type="text" name="subtitle" class="widefat"></div>
                <div class="wikaz-form-group"><label>Title</label><input type="text" name="title" class="widefat"></div>
                <div class="wikaz-form-group"><label>Description</label><textarea name="description" class="widefat" rows="2"></textarea></div>
                
                <!-- Link Source -->
                <div class="wikaz-form-group" style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
                    <label><?php _e('Link Source', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-media-type-selector">
                        <label class="wikaz-media-option"><input type="radio" name="link_source" value="product" checked> <span class="dashicons dashicons-cart"></span> <?php _e('Product', 'keycreation-wikaz'); ?></label>
                        <label class="wikaz-media-option"><input type="radio" name="link_source" value="post"> <span class="dashicons dashicons-admin-post"></span> <?php _e('Article/Post', 'keycreation-wikaz'); ?></label>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="wikaz-form-group" id="hs-product-search-group">
                    <label><?php _e('Search Product Title (Optional)', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-product-search-wrap">
                        <input type="text" id="hs-product-search" class="widefat" placeholder="<?php _e('Search products...', 'keycreation-wikaz'); ?>" autocomplete="off">
                        <input type="hidden" name="product_id" id="hs-product-id">
                        <div class="wikaz-product-results" id="hs-product-results"></div>
                        <div class="wikaz-selected-product" id="hs-selected-product" style="display:none;">
                            <img src=""> <span class="product-name"></span> <button type="button" class="remove-product">&times;</button>
                        </div>
                    </div>
                </div>

                <!-- Post Search -->
                <div class="wikaz-form-group" id="hs-post-search-group" style="display:none;">
                    <label><?php _e('Search Article Title (Optional)', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-product-search-wrap">
                        <input type="text" id="hs-post-search" class="widefat" placeholder="<?php _e('Search articles...', 'keycreation-wikaz'); ?>" autocomplete="off">
                        <input type="hidden" name="post_id" id="hs-post-id">
                        <div class="wikaz-product-results" id="hs-post-results"></div>
                        <div class="wikaz-selected-product" id="hs-selected-post" style="display:none;">
                            <img src=""> <span class="product-name"></span> <button type="button" class="remove-product" data-type="post">&times;</button>
                        </div>
                    </div>
                </div>

                <!-- Button -->
                <div class="wikaz-form-row">
                    <div class="wikaz-form-group"><label>Button Text</label><input type="text" name="button_text" class="widefat" value="Shop Now"></div>
                    <div class="wikaz-form-group"><label>Button URL</label><input type="text" name="button_url" class="widefat" placeholder="<?php _e('Auto-filled from product/post', 'keycreation-wikaz'); ?>"></div>
                </div>

                <div class="wikaz-modal-footer">
                    <button type="submit" class="button button-primary"><?php _e('Save Slide', 'keycreation-wikaz'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const nonce = '<?php echo wp_create_nonce('wikaz_admin_nonce'); ?>';

    // Helper: Debounce
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // === SLIDER MANAGEMENT ===
    
    function loadSliders() {
        $('#wikaz-slider-spinner').addClass('is-active');
        $.post(ajaxUrl, { action: 'wikaz_get_header_sliders', nonce: nonce }, function(res) {
            $('#wikaz-slider-spinner').removeClass('is-active');
            if(res.success) {
                renderSliders(res.data);
            }
        });
    }

    function renderSliders(sliders) {
        let html = '';
        if(!sliders.length) {
            html = '<tr><td colspan="5" align="center">No sliders found.</td></tr>';
        } else {
            sliders.forEach(s => {
                const shortcode = `[wikaz_header_slider id="${s.id}"]`;
                html += `
                    <tr>
                        <td><strong>${s.name}</strong></td>
                        <td><input type="text" readonly value='${shortcode}' onclick="this.select()" class="widefat" style="max-width:200px; text-align:center;"></td>
                        <td>-</td>
                        <td>${s.autoplay=='1'?'Autoplay':''} (${s.speed}ms)</td>
                        <td>
                            <button class="button wikaz-manage-slides" data-id="${s.id}" data-name="${s.name}"><span class="dashicons dashicons-images-alt2"></span> Manage Slides</button>
                            <button class="button wikaz-edit-slider" data-id="${s.id}" data-object='${JSON.stringify(s)}'><span class="dashicons dashicons-edit"></span></button>
                            <button class="button wikaz-delete-slider" data-id="${s.id}" style="color:#a00;"><span class="dashicons dashicons-trash"></span></button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#wikaz-sliders-list-body').html(html);
    }

    // Add Slider
    $('#wikaz-add-slider').click(function() {
        $('#wikaz-slider-form')[0].reset();
        $('#wikaz-slider-id').val(0);
        $('#wikaz-slider-modal-title').text('New Slider');
        $('#wikaz-slider-modal').addClass('active');
    });

    // Edit Slider
    $(document).on('click', '.wikaz-edit-slider', function() {
        const data = $(this).data('object');
        $('#wikaz-slider-id').val(data.id);
        $('#wikaz-slider-name').val(data.name);
        $('#wikaz-slider-speed').val(data.speed);
        $('#wikaz-slider-autoplay').prop('checked', data.autoplay == 1);
        $('#wikaz-slider-modal-title').text('Edit Slider');
        $('#wikaz-slider-modal').addClass('active');
    });

    // Save Slider
    $('#wikaz-slider-form').submit(function(e) {
        e.preventDefault();
        const data = $(this).serialize() + '&action=wikaz_save_header_slider&nonce=' + nonce;
        $.post(ajaxUrl, data, function(res) {
            if(res.success) {
                $('#wikaz-slider-modal').removeClass('active');
                loadSliders();
            } else {
                alert(res.data);
            }
        });
    });

    // Delete Slider
    $(document).on('click', '.wikaz-delete-slider', function() {
        if(!confirm('Delete this slider and all its slides?')) return;
        const id = $(this).data('id');
        $.post(ajaxUrl, { action: 'wikaz_delete_header_slider', id: id, nonce: nonce }, function(res) {
            loadSliders();
        });
    });

    // Close Modals
    $('.wikaz-modal-close').click(function() {
        $(this).closest('.wikaz-modal').removeClass('active');
    });

    
    // === SLIDE MANAGEMENT ===

    // Enter Slide Manager
    $(document).on('click', '.wikaz-manage-slides', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#wikaz-current-slider-id').val(id);
        $('#wikaz-current-slider-name').text(name);
        
        $('#wikaz-slider-manager').hide();
        $('#wikaz-slide-manager').show();
        
        loadSlides(id);
    });

    // Back to Sliders
    $('#wikaz-back-to-sliders').click(function() {
        $('#wikaz-slide-manager').hide();
        $('#wikaz-slider-manager').show();
        loadSliders(); // Refresh list
    });

    function loadSlides(sliderId) {
        $('#wikaz-header-slides-list').html('<p style="text-align:center;">Loading slides...</p>');
        $.post(ajaxUrl, { action: 'wikaz_get_header_slides', slider_id: sliderId, nonce: nonce }, function(res) {
            if(res.success) {
                renderSlides(res.data);
            }
        });
    }

    function renderSlides(slides) {
        if(!slides.length) {
            $('#wikaz-header-slides-list').html('<div class="wikaz-no-slides"><p>No slides yet.</p></div>');
            return;
        }
        
        let html = '';
        slides.forEach(slide => {
            const img = slide.background_image || '';
            const title = slide.title || 'Untitled';
            
            html += `
                <div class="wikaz-slide-item" data-id="${slide.id}">
                     <div class="wikaz-slide-preview">
                        ${img ? `<img src="${img}">` : '<span class="dashicons dashicons-format-image"></span>'}
                     </div>
                     <div class="wikaz-slide-info">
                        <h4>${title}</h4>
                        <p>${slide.subtitle || '-'}</p>
                     </div>
                     <div class="wikaz-slide-actions">
                        <button type="button" class="button wikaz-edit-header-slide"><span class="dashicons dashicons-edit"></span></button>
                        <button type="button" class="button wikaz-delete-header-slide"><span class="dashicons dashicons-trash"></span></button>
                     </div>
                </div>
            `;
        });
        $('#wikaz-header-slides-list').html(html);
    }

    // Add Slide
    $('#wikaz-add-header-slide').click(function() {
        $('#wikaz-header-slide-form')[0].reset();
        $('#wikaz-header-slide-id').val(0);
        $('#wikaz-header-slide-slider-id').val($('#wikaz-current-slider-id').val());
        resetMediaPreview();
        resetLinkSource();
        
        // Default to Image and Product
        $('#wikaz-header-slide-form input[name="media_type"][value="image"]').prop('checked', true).trigger('change');
        $('#wikaz-header-slide-form input[name="link_source"][value="product"]').prop('checked', true).trigger('change');
        
        $('#wikaz-header-slide-modal').addClass('active');
    });

    // Edit Slide
    $(document).on('click', '.wikaz-edit-header-slide', function() {
        const id = $(this).closest('.wikaz-slide-item').data('id');
        const $btn = $(this);
        $btn.prop('disabled', true).find('.dashicons').addClass('spin');

        $.post(ajaxUrl, { action: 'wikaz_get_header_slide', id: id, nonce: nonce }, function(res) {
            $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
            if(!res.success) {
                alert('Error loading slide data');
                return;
            }
            
            const data = res.data;
            $('#wikaz-header-slide-id').val(data.id);
            $('#wikaz-header-slide-slider-id').val(data.slider_id);
            
            // Populate fields
            $('[name="title"]').val(data.title);
            $('[name="subtitle"]').val(data.subtitle);
            $('[name="description"]').val(data.description);
            $('[name="button_text"]').val(data.button_text);
            $('[name="button_url"]').val(data.button_url);
            $('[name="layout"][value="' + (data.layout || 'full-bg') + '"]').prop('checked', true);
            $('[name="is_active"]').prop('checked', data.is_active == 1);
            
            // Media Type Detection (Safe)
            const mediaType = data.media_type || ((data.background_video && data.background_video !== '') ? 'video' : 'image');
            $('#wikaz-header-slide-form input[name="media_type"][value="' + mediaType + '"]').prop('checked', true).trigger('change');
            
            if(data.background_image) {
                $('#hs-background-image').val(data.background_image);
                $('#hs-image-preview img').attr('src', data.background_image).show();
                $('#hs-image-preview .wikaz-image-placeholder').hide();
                $('#hs-remove-image').show();
            } else {
                $('#hs-background-image').val('');
                $('#hs-image-preview img').hide().attr('src', '');
                $('#hs-image-preview .wikaz-image-placeholder').show();
                $('#hs-remove-image').hide();
            }
            
            if(data.background_video) {
                $('#hs-background-video').val(data.background_video);
                // Trigger video preview update
                $('#hs-background-video').trigger('input');
            } else {
                $('#hs-background-video').val('');
                $('#hs-video-preview video').hide().attr('src', '');
                $('#hs-video-preview .wikaz-video-placeholder').show();
                $('#hs-remove-video').hide();
            }

            // Link Source Detection
            resetLinkSource();
            let linkSource = data.link_source || (data.post_id ? 'post' : 'product');

            $('#wikaz-header-slide-form input[name="link_source"][value="' + linkSource + '"]').prop('checked', true).trigger('change');
            
            if(data.product_id || (data.product && data.product.id)) {
                const product = data.product || {};
                const productId = data.product_id || product.id;
                $('#hs-product-id').val(productId);
                const productName = product.title ? product.title : 'Product ID: ' + productId;
                const productImage = product.image ? product.image : '';
                
                $('#hs-selected-product .product-name').text(productName);
                if(productImage) $('#hs-selected-product img').attr('src', productImage);
                $('#hs-selected-product').show();
                $('#hs-product-search').hide().parent().find('label').hide();
            } 
            
            if(data.post_id || (data.post && data.post.id)) {
                const post = data.post || {};
                const postId = data.post_id || post.id;
                $('#hs-post-id').val(postId);
                const postName = post.title ? post.title : 'Post ID: ' + postId;
                const postImage = post.image ? post.image : '';

                $('#hs-selected-post .product-name').text(postName);
                if(postImage) $('#hs-selected-post img').attr('src', postImage);
                $('#hs-selected-post').show();
                $('#hs-post-search').hide().parent().find('label').hide();
            }

            $('#wikaz-header-slide-modal').addClass('active');
        });
    });

    // Save Slide
    $('#wikaz-header-slide-form').submit(function(e) {
        e.preventDefault();
        const data = $(this).serialize() + '&action=wikaz_save_header_slide&nonce=' + nonce;
        $.post(ajaxUrl, data, function(res) {
            if(res.success) {
                $('#wikaz-header-slide-modal').removeClass('active');
                loadSlides($('#wikaz-current-slider-id').val());
            } else {
                alert(res.data);
            }
        });
    });

    // Delete Slide
    $(document).on('click', '.wikaz-delete-header-slide', function() {
        if(!confirm('Delete this slide?')) return;
        const id = $(this).closest('.wikaz-slide-item').data('id');
        $.post(ajaxUrl, { action: 'wikaz_delete_header_slide', id: id, nonce: nonce }, function() {
            loadSlides($('#wikaz-current-slider-id').val());
        });
    });

    // Media Logic (Toggle)
    $(document).on('change', '#wikaz-header-slide-form input[name="media_type"]', function() {
        if($(this).val() === 'video') {
            $('#hs-section-media-video').show();
            $('#hs-section-media-image').hide();
        } else {
            $('#hs-section-media-video').hide();
            $('#hs-section-media-image').show();
        }
    });

    function resetMediaPreview() {
        $('#hs-background-image').val('');
        $('#hs-image-preview img').hide();
        $('#hs-image-preview .wikaz-image-placeholder').show();
        $('#hs-remove-image').hide();
        $('#hs-background-video').val('');
        $('#hs-remove-video').hide();
        $('#hs-video-preview video').hide().attr('src', '');
        $('#hs-video-preview .wikaz-video-placeholder').show();
    }

    // WP Media Uploader (Image)
    let mediaUploader;
    $(document).on('click', '#hs-select-image, #hs-image-preview', function(e) {
        e.preventDefault();
        if(mediaUploader) { mediaUploader.open(); return; }
        
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: { text: 'Choose Image' },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#hs-background-image').val(attachment.url);
            $('#hs-image-preview img').attr('src', attachment.url).show();
            $('#hs-image-preview .wikaz-image-placeholder').hide();
            $('#hs-remove-image').show();
        });
        mediaUploader.open();
    });

    $('#hs-remove-image').click(function() {
        $('#hs-background-image').val('');
        $('#hs-image-preview img').hide();
        $('#hs-image-preview .wikaz-image-placeholder').show();
        $('#hs-remove-image').hide();
    });

    // WP Media Uploader (Video)
    let videoUploader;
    $(document).on('click', '#hs-select-video, #hs-video-preview', function(e) {
        e.preventDefault();
        if(videoUploader) { videoUploader.open(); return; }
        
        videoUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Video',
            button: { text: 'Choose Video' },
            library: { type: 'video' },
            multiple: false
        });
        
        videoUploader.on('select', function() {
            const attachment = videoUploader.state().get('selection').first().toJSON();
            $('#hs-background-video').val(attachment.url).trigger('input');
        });
        videoUploader.open();
    });

    $('#hs-remove-video').click(function() {
        $('#hs-background-video').val('').trigger('input');
    });

    // Video Preview Logic
    $('#hs-background-video').on('input', debounce(function() {
        const val = $(this).val();
        const videoEl = $('#hs-video-preview video')[0];
        
        if(val) {
             $('#hs-remove-video').show();
             // Simple check if it is a file URL or Youtube/etc.
             if(val.match(/\.(mp4|webm|ogg)$/i)) {
                 videoEl.src = val;
                 $(videoEl).show();
                 $('#hs-video-preview .wikaz-video-placeholder').hide();
             } else {
                 // For YouTube/others, we just show placeholder text for now as we don't have iframe preview logic here
                 // Or we could try to show simple preview if needed.
                 // Keeping it simple: show URL is set.
                 $(videoEl).hide();
                 $('#hs-video-preview .wikaz-video-placeholder').show();
                 $('#hs-video-preview .wikaz-video-placeholder p').text('External video URL set');
             }
        } else {
            $('#hs-remove-video').hide();
            $(videoEl).hide();
            $('#hs-video-preview .wikaz-video-placeholder').show();
            $('#hs-video-preview .wikaz-video-placeholder p').text('Click to select video or paste URL');
        }
    }, 500));


    // === LINK SOURCE LOGIC ===
    
    // Toggle Link Source
    $(document).on('change', '#wikaz-header-slide-form input[name="link_source"]', function() {
        if($(this).val() === 'post') {
            $('#hs-post-search-group').show();
            $('#hs-product-search-group').hide();
        } else {
            $('#hs-post-search-group').hide();
            $('#hs-product-search-group').show();
        }
    });



    function resetLinkSource() {
        $('#hs-product-id').val('');
        $('#hs-post-id').val('');
        $('#hs-selected-product').hide();
        $('#hs-selected-post').hide();
        $('#hs-product-search').val('').show();
        $('#hs-post-search').val('').show();
        $('#hs-product-results').empty().hide();
        $('#hs-post-results').empty().hide();
    }

    // Search logic moved to admin-script.js

    // Init
    loadSliders();
});
</script>
