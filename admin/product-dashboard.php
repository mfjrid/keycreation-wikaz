<?php
/**
 * Product Manager Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = get_terms('product_cat', array('hide_empty' => false));
$tags = get_terms('product_tag', array('hide_empty' => false));
?>

<div class="wrap wikaz-admin-wrap product-manager">
    <div class="wikaz-pm-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-products"></span>
            <?php _e('Simplified Product Manager', 'keycreation-wikaz'); ?>
        </h1>
        <button type="button" class="page-title-action" id="wikaz-add-pm-product">
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php _e('Add New Product', 'keycreation-wikaz'); ?>
        </button>
    </div>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container full-width">
        <!-- Tools Bar -->
        <div class="wikaz-pm-tools">
            <div class="wikaz-search-box">
                <input type="text" id="wikaz-pm-search"
                    placeholder="<?php _e('Search products...', 'keycreation-wikaz'); ?>">
                <span class="dashicons dashicons-search"></span>
            </div>
        </div>

        <!-- Product Table -->
        <div class="wikaz-pm-list-wrap">
            <div id="wikaz-pm-loader" class="wikaz-loader" style="display:none;">
                <span class="spinner is-active"></span>
            </div>
            <table class="wp-list-table widefat fixed striped products">
                <thead>
                    <tr>
                        <th class="column-thumb"><?php _e('Image', 'keycreation-wikaz'); ?></th>
                        <th class="column-name"><?php _e('Name', 'keycreation-wikaz'); ?></th>
                        <th class="column-sku"><?php _e('SKU', 'keycreation-wikaz'); ?></th>
                        <th class="column-type"><?php _e('Type', 'keycreation-wikaz'); ?></th>
                        <th class="column-price"><?php _e('Price', 'keycreation-wikaz'); ?></th>
                        <th class="column-stock"><?php _e('Stock', 'keycreation-wikaz'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                    </tr>
                </thead>
                <tbody id="wikaz-pm-product-list">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>

            <div id="wikaz-pm-pagination" class="wikaz-pagination">
                <!-- Pagination via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Product Editor Modal -->
<div class="wikaz-modal wikaz-pm-modal" id="wikaz-pm-modal">
    <div class="wikaz-modal-content">
        <div class="wikaz-modal-header">
            <h2 id="wikaz-pm-modal-title"><?php _e('Add New Product', 'keycreation-wikaz'); ?></h2>
            <button type="button" class="wikaz-modal-close">&times;</button>
        </div>
        <div class="wikaz-modal-body">
            <form id="wikaz-pm-form">
                <input type="hidden" id="pm-product-id" value="0">

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Product Name', 'keycreation-wikaz'); ?> <span class="required">*</span></label>
                        <input type="text" id="pm-product-name" required placeholder="Juice Slim Green Tea...">
                    </div>
                    <div class="wikaz-form-group">
                        <label><?php _e('Base SKU', 'keycreation-wikaz'); ?></label>
                        <input type="text" id="pm-product-sku" placeholder="WIZ-TEA">
                        <p class="description"><?php _e('Used as prefix for variations', 'keycreation-wikaz'); ?></p>
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Regular Price', 'keycreation-wikaz'); ?> (Rp)</label>
                        <input type="number" id="pm-product-price" placeholder="150000">
                    </div>
                    <div class="wikaz-form-group">
                        <label><?php _e('Category', 'keycreation-wikaz'); ?></label>
                        <select id="pm-product-category" multiple class="pm-select2">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Short Description', 'keycreation-wikaz'); ?></label>
                        <textarea id="pm-product-short-description" rows="2"
                            placeholder="<?php _e('Write a brief summary...', 'keycreation-wikaz'); ?>"></textarea>
                    </div>
                    <div class="wikaz-form-group">
                        <label><?php _e('Product Tags', 'keycreation-wikaz'); ?></label>
                        <select id="pm-product-tags" multiple class="pm-select2">
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Full Description', 'keycreation-wikaz'); ?></label>
                        <textarea id="pm-product-description" rows="4"
                            placeholder="<?php _e('Write a detailed description...', 'keycreation-wikaz'); ?>"></textarea>
                    </div>
                    <div class="wikaz-form-group">
                        <label><?php _e('Video URL', 'keycreation-wikaz'); ?></label>
                        <input type="url" id="pm-product-video-url" placeholder="https://youtube.com/watch?v=...">
                        <p class="description">
                            <?php _e('YouTube or Vimeo link for video thumbnail', 'keycreation-wikaz'); ?></p>
                    </div>
                </div>

                <!-- Image & Gallery Section -->
                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Main Image', 'keycreation-wikaz'); ?> <span class="required">*</span></label>
                        <div class="pm-image-uploader main-uploader" id="pm-image-uploader">
                            <input type="hidden" id="pm-product-image-id">
                            <div class="pm-image-preview" id="pm-image-preview">
                                <div class="placeholder">
                                    <span class="dashicons dashicons-admin-media"></span>
                                    <p><?php _e('Set Main Image', 'keycreation-wikaz'); ?></p>
                                </div>
                                <img src="" style="display:none;">
                            </div>
                        </div>
                    </div>
                    <div class="wikaz-form-group">
                        <label><?php _e('Product Gallery', 'keycreation-wikaz'); ?></label>
                        <div class="pm-gallery-uploader" id="pm-gallery-uploader">
                            <input type="hidden" id="pm-product-gallery-ids">
                            <div class="pm-gallery-container" id="pm-gallery-container">
                                <!-- Gallery items added here -->
                                <div class="pm-gallery-add" id="pm-add-gallery-item">
                                    <span class="dashicons dashicons-plus"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variations Section -->
                <div class="pm-variations-section">
                    <h3>
                        <span class="dashicons dashicons-forms"></span>
                        <?php _e('Variations (Color & Size)', 'keycreation-wikaz'); ?>
                    </h3>
                    <div id="pm-attributes-container">
                        <!-- Attributes loaded via AJAX -->
                        <div class="wikaz-loader"><span class="spinner is-active"></span></div>
                    </div>

                    <div class="pm-variation-matrix-wrap" id="pm-variation-matrix-wrap" style="display:none;">
                        <h4><?php _e('Stock per Variation', 'keycreation-wikaz'); ?></h4>
                        <table class="pm-variation-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Variation', 'keycreation-wikaz'); ?></th>
                                    <th><?php _e('SKU', 'keycreation-wikaz'); ?></th>
                                    <th><?php _e('Price', 'keycreation-wikaz'); ?></th>
                                    <th><?php _e('Stock', 'keycreation-wikaz'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="pm-variation-matrix-body"></tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        <div class="wikaz-modal-footer">
            <button type="button" class="button wikaz-modal-cancel"><?php _e('Cancel', 'keycreation-wikaz'); ?></button>
            <button type="submit" form="wikaz-pm-form" class="button button-primary" id="pm-save-btn">
                <span class="dashicons dashicons-yes"></span>
                <?php _e('Save Product', 'keycreation-wikaz'); ?>
            </button>
            <span class="spinner pm-save-spinner"></span>
        </div>
    </div>
</div>

<style>
    /* Scoped styles for Product Manager */
    .product-manager .wikaz-pm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .product-manager .wikaz-pm-tools {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .product-manager .wikaz-search-box {
        position: relative;
        max-width: 300px;
    }

    .product-manager .wikaz-search-box input {
        width: 100%;
        padding: 8px 35px 8px 12px;
        border-radius: 20px;
        border: 1px solid #ddd;
    }

    .product-manager .wikaz-search-box .dashicons {
        position: absolute;
        right: 10px;
        top: 8px;
        color: #999;
    }

    .product-manager .column-thumb {
        width: 60px;
    }

    .product-manager .column-thumb img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }

    .product-manager .column-actions {
        width: 120px;
        text-align: right;
    }

    .product-manager .wikaz-pagination {
        margin-top: 20px;
        text-align: center;
    }

    /* Modal Editor */
    .pm-variations-section {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .pm-variations-section h3 {
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }

    .pm-attribute-row {
        margin-bottom: 15px;
    }

    .pm-attribute-label {
        font-weight: 600;
        font-size: 13px;
        color: #555;
        margin-bottom: 8px;
        display: block;
    }

    .pm-terms-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pm-term-item {
        cursor: pointer;
    }

    .pm-term-item input {
        display: none;
    }

    .pm-term-item span {
        padding: 4px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
        background: #fff;
        transition: all 0.2s;
    }

    .pm-term-item input:checked+span {
        background: #6366f1;
        color: #fff;
        border-color: #6366f1;
    }

    .pm-variation-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .pm-variation-table th {
        text-align: left;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #eee;
        font-size: 12px;
    }

    .pm-variation-table td {
        padding: 8px;
        border: 1px solid #eee;
    }

    .pm-variation-table input {
        width: 100%;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 12px;
    }

    .pm-image-preview {
        width: 120px;
        height: 120px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        background: #fafafa;
    }

    .pm-image-preview:hover {
        border-color: #6366f1;
    }

    .pm-image-preview .placeholder {
        text-align: center;
        color: #999;
    }

    .pm-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pm-save-spinner {
        float: right;
        margin-top: 8px;
        margin-right: 15px;
        visibility: hidden;
    }

    .pm-save-spinner.is-active {
        visibility: visible;
    }
</style>