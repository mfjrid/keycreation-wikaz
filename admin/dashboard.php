<?php
/**
 * Carousel Admin Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

$slides = Wikaz_Admin::get_slides();
$autoplay = get_option('wikaz_carousel_autoplay', '1');
$speed = get_option('wikaz_carousel_speed', '5000');
$position = get_option('wikaz_carousel_position', 'before_content');
$header_transparent = get_option('wikaz_header_transparent', '0');
?>

<div class="wrap wikaz-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-slides"></span>
        <?php _e('Carousel Slides', 'keycreation-wikaz'); ?>
    </h1>
    <button type="button" class="page-title-action" id="wikaz-add-slide">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php _e('Add New Slide', 'keycreation-wikaz'); ?>
    </button>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container">
        <!-- Slides List -->
        <div class="wikaz-slides-section">
            <div class="wikaz-slides-list" id="wikaz-slides-list">
                <?php if (empty($slides)): ?>
                    <div class="wikaz-no-slides">
                        <span class="dashicons dashicons-format-gallery"></span>
                        <p><?php _e('No slides yet. Click "Add New Slide" to create one.', 'keycreation-wikaz'); ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($slides as $slide):
                        $product = $slide->product_id ? wc_get_product($slide->product_id) : null;
                        ?>
                        <div class="wikaz-slide-item <?php echo $slide->is_active ? 'active' : 'inactive'; ?>"
                            data-id="<?php echo esc_attr($slide->id); ?>">
                            <div class="wikaz-slide-handle">
                                <span class="dashicons dashicons-move"></span>
                            </div>
                            <div class="wikaz-slide-preview">
                                <?php if ($slide->background_image): ?>
                                    <img src="<?php echo esc_url($slide->background_image); ?>" alt="">
                                <?php else: ?>
                                    <div class="wikaz-slide-no-image">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="wikaz-slide-info">
                                <h4><?php echo esc_html($slide->title ?: ($product ? $product->get_name() : __('Untitled', 'keycreation-wikaz'))); ?>
                                </h4>
                                <p><?php echo esc_html($slide->subtitle ?: '—'); ?></p>
                                <?php if ($product): ?>
                                    <span class="wikaz-slide-product">
                                        <span class="dashicons dashicons-products"></span>
                                        <?php echo esc_html($product->get_name()); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="wikaz-slide-actions">
                                <label class="wikaz-toggle">
                                    <input type="checkbox" class="wikaz-toggle-active" <?php checked($slide->is_active, 1); ?>>
                                    <span class="wikaz-toggle-slider"></span>
                                </label>
                                <button type="button" class="button wikaz-edit-slide"
                                    title="<?php _e('Edit', 'keycreation-wikaz'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button type="button" class="button wikaz-delete-slide"
                                    title="<?php _e('Delete', 'keycreation-wikaz'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Settings Panel -->
        <div class="wikaz-settings-section">
            <div class="wikaz-settings-panel">
                <h3>
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('Carousel Settings', 'keycreation-wikaz'); ?>
                </h3>
                <form id="wikaz-settings-form">
                    <div class="wikaz-form-group">
                        <label for="wikaz-autoplay">
                            <input type="checkbox" id="wikaz-autoplay" name="autoplay" value="1" <?php checked($autoplay, '1'); ?>>
                            <?php _e('Enable Autoplay', 'keycreation-wikaz'); ?>
                        </label>
                    </div>
                    <div class="wikaz-form-group">
                        <label for="wikaz-speed"><?php _e('Autoplay Speed (ms)', 'keycreation-wikaz'); ?></label>
                        <input type="number" id="wikaz-speed" name="speed" value="<?php echo esc_attr($speed); ?>"
                            min="1000" max="10000" step="500">
                    </div>
                    <div class="wikaz-form-group">
                        <label for="wikaz-position"><?php _e('Position', 'keycreation-wikaz'); ?></label>
                        <select id="wikaz-position" name="position">
                            <option value="before_content" <?php selected($position, 'before_content'); ?>>
                                <?php _e('Before Content (Auto)', 'keycreation-wikaz'); ?>
                            </option>
                            <option value="shortcode" <?php selected($position, 'shortcode'); ?>>
                                <?php _e('Shortcode Only', 'keycreation-wikaz'); ?>
                            </option>
                        </select>
                        <p class="description"><?php _e('Use shortcode: [wikaz_carousel]', 'keycreation-wikaz'); ?></p>
                    </div>
                    <div class="wikaz-form-group">
                        <label for="wikaz-header-transparent">
                            <input type="checkbox" id="wikaz-header-transparent" name="header_transparent" value="1"
                                <?php checked($header_transparent, '1'); ?>>
                            <?php _e('Transparent Header (Hero Effect)', 'keycreation-wikaz'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Make the site header transparent and overlay carousel behind it.', 'keycreation-wikaz'); ?>
                        </p>
                    </div>
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Save Settings', 'keycreation-wikaz'); ?>
                    </button>
                </form>
            </div>

            <div class="wikaz-help-panel">
                <h3>
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('Quick Tips', 'keycreation-wikaz'); ?>
                </h3>
                <ul>
                    <li><?php _e('Drag slides to reorder them', 'keycreation-wikaz'); ?></li>
                    <li><?php _e('Toggle switch to show/hide slides', 'keycreation-wikaz'); ?></li>
                    <li><?php _e('Link to products for auto title & URL', 'keycreation-wikaz'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Slide Modal -->
<div id="wikaz-slide-modal" class="wikaz-modal">
    <div class="wikaz-modal-content">
        <div class="wikaz-modal-header">
            <h2 id="wikaz-modal-title"><?php _e('Add New Slide', 'keycreation-wikaz'); ?></h2>
            <button type="button" class="wikaz-modal-close">&times;</button>
        </div>
        <form id="wikaz-slide-form">
            <input type="hidden" name="slide_id" id="wikaz-slide-id" value="0">

            <div class="wikaz-modal-body">
                <!-- Background Image -->
                <div class="wikaz-form-group wikaz-image-upload">
                    <label><?php _e('Background Image', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-image-preview" id="wikaz-image-preview">
                        <img src="" alt="" style="display: none;">
                        <div class="wikaz-image-placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                            <p><?php _e('Click to select image', 'keycreation-wikaz'); ?></p>
                        </div>
                    </div>
                    <input type="hidden" name="background_image" id="wikaz-background-image" value="">
                    <button type="button" class="button"
                        id="wikaz-select-image"><?php _e('Select Image', 'keycreation-wikaz'); ?></button>
                    <button type="button" class="button" id="wikaz-remove-image"
                        style="display: none;"><?php _e('Remove', 'keycreation-wikaz'); ?></button>
                </div>

                <div class="wikaz-form-row">
                    <!-- Title -->
                    <div class="wikaz-form-group">
                        <label for="wikaz-title"><?php _e('Title', 'keycreation-wikaz'); ?></label>
                        <input type="text" id="wikaz-title" name="title"
                            placeholder="<?php _e('Enter slide title...', 'keycreation-wikaz'); ?>">
                    </div>

                    <!-- Subtitle -->
                    <div class="wikaz-form-group">
                        <label for="wikaz-subtitle"><?php _e('Subtitle', 'keycreation-wikaz'); ?></label>
                        <input type="text" id="wikaz-subtitle" name="subtitle"
                            placeholder="<?php _e('Enter slide subtitle...', 'keycreation-wikaz'); ?>">
                    </div>
                </div>

                <!-- Layout Selection -->
                <div class="wikaz-form-group">
                    <label><?php _e('Slide Layout', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-layout-selector">
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="full-bg" checked>
                            <div class="layout-preview layout-full-bg"
                                title="<?php _e('Full Background', 'keycreation-wikaz'); ?>">
                                <div class="preview-box"></div>
                            </div>
                        </label>
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="split-left">
                            <div class="layout-preview layout-split-left"
                                title="<?php _e('Split (Image Left)', 'keycreation-wikaz'); ?>">
                                <div class="preview-box"></div>
                                <div class="preview-text"></div>
                            </div>
                        </label>
                        <label class="wikaz-layout-option">
                            <input type="radio" name="layout" value="split-right">
                            <div class="layout-preview layout-split-right"
                                title="<?php _e('Split (Image Right)', 'keycreation-wikaz'); ?>">
                                <div class="preview-text"></div>
                                <div class="preview-box"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="wikaz-form-group">
                    <label
                        for="wikaz-product-search"><?php _e('Link to Product (Optional)', 'keycreation-wikaz'); ?></label>
                    <div class="wikaz-product-search-wrap">
                        <input type="text" id="wikaz-product-search"
                            placeholder="<?php _e('Search products...', 'keycreation-wikaz'); ?>" autocomplete="off">
                        <input type="hidden" name="product_id" id="wikaz-product-id" value="">
                        <div class="wikaz-product-results" id="wikaz-product-results"></div>
                        <div class="wikaz-selected-product" id="wikaz-selected-product" style="display: none;">
                            <img src="" alt="">
                            <span class="product-name"></span>
                            <button type="button" class="remove-product">&times;</button>
                        </div>
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <!-- Button Text -->
                    <div class="wikaz-form-group">
                        <label for="wikaz-button-text"><?php _e('Button Text', 'keycreation-wikaz'); ?></label>
                        <input type="text" id="wikaz-button-text" name="button_text" value="Shop Now"
                            placeholder="Shop Now">
                    </div>

                    <!-- Button URL -->
                    <div class="wikaz-form-group">
                        <label for="wikaz-button-url"><?php _e('Button URL (Optional)', 'keycreation-wikaz'); ?></label>
                        <input type="url" id="wikaz-button-url" name="button_url"
                            placeholder="<?php _e('Auto-filled from product', 'keycreation-wikaz'); ?>">
                    </div>
                </div>

                <!-- Active Status -->
                <div class="wikaz-form-group">
                    <label>
                        <input type="checkbox" name="is_active" id="wikaz-is-active" value="1" checked>
                        <?php _e('Active (Show this slide)', 'keycreation-wikaz'); ?>
                    </label>
                </div>
            </div>

            <div class="wikaz-modal-footer">
                <button type="button"
                    class="button wikaz-modal-cancel"><?php _e('Cancel', 'keycreation-wikaz'); ?></button>
                <button type="submit" class="button button-primary" id="wikaz-save-slide">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Save Slide', 'keycreation-wikaz'); ?>
                </button>
            </div>
        </form>
    </div>
</div>