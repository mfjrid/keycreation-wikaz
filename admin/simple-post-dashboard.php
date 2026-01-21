<?php
/**
 * Simple Post Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wikaz-admin-wrap simple-post-manager">
    <div class="wikaz-pm-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-edit-page"></span>
            <?php _e('Simple Post Manager', 'keycreation-wikaz'); ?>
        </h1>
        <button type="button" class="page-title-action" id="wikaz-add-simple-post">
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php _e('Add New Post', 'keycreation-wikaz'); ?>
        </button>
    </div>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container full-width">
        <!-- Tools Bar -->
        <div class="wikaz-pm-tools">
            <div class="wikaz-search-box">
                <input type="text" id="wikaz-sp-search"
                    placeholder="<?php _e('Search posts...', 'keycreation-wikaz'); ?>">
                <span class="dashicons dashicons-search"></span>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="wikaz-pm-list-wrap">
            <div id="wikaz-sp-loader" class="wikaz-loader" style="display:none;">
                <span class="spinner is-active"></span>
            </div>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th class="column-thumb"><?php _e('Image', 'keycreation-wikaz'); ?></th>
                        <th class="column-title"><?php _e('Title', 'keycreation-wikaz'); ?></th>
                        <th class="column-date"><?php _e('Date', 'keycreation-wikaz'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                    </tr>
                </thead>
                <tbody id="wikaz-sp-list">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>

            <div id="wikaz-sp-pagination" class="wikaz-pagination">
                <!-- Pagination via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Simple Post Editor Modal -->
<div class="wikaz-modal wikaz-sp-modal" id="wikaz-sp-modal">
    <div class="wikaz-modal-content large-modal">
        <div class="wikaz-modal-header">
            <h2 id="wikaz-sp-modal-title"><?php _e('Add New Post', 'keycreation-wikaz'); ?></h2>
            <button type="button" class="wikaz-modal-close">&times;</button>
        </div>
        <div class="wikaz-modal-body">
            <form id="wikaz-sp-form">
                <input type="hidden" id="sp-post-id" value="0">

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group">
                        <label><?php _e('Featured Image', 'keycreation-wikaz'); ?></label>
                        <div class="pm-image-uploader main-uploader" id="sp-image-uploader">
                            <input type="hidden" id="sp-post-image-id">
                            <div class="pm-image-preview" id="sp-image-preview">
                                <div class="placeholder">
                                    <span class="dashicons dashicons-admin-media"></span>
                                    <p><?php _e('Set Featured Image', 'keycreation-wikaz'); ?></p>
                                </div>
                                <img src="" style="display:none;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group full-width">
                        <label><?php _e('Title', 'keycreation-wikaz'); ?> <span class="required">*</span></label>
                        <input type="text" id="sp-post-title" required placeholder="Enter post title...">
                    </div>
                </div>

                <div class="wikaz-form-row">
                    <div class="wikaz-form-group full-width">
                        <label><?php _e('Content', 'keycreation-wikaz'); ?></label>
                        <div class="summernote-wrapper">
                            <textarea id="sp-post-content"></textarea>
                        </div>
                    </div>
                </div>

                

            </form>
        </div>
        <div class="wikaz-modal-footer">
            <button type="button" class="button wikaz-modal-cancel"><?php _e('Cancel', 'keycreation-wikaz'); ?></button>
            <button type="submit" form="wikaz-sp-form" class="button button-primary" id="sp-save-btn">
                <span class="dashicons dashicons-yes"></span>
                <?php _e('Save Post', 'keycreation-wikaz'); ?>
            </button>
            <span class="spinner sp-save-spinner"></span>
        </div>
    </div>
</div>

<style>
    /* Scoped styles for Simple Post Manager */
    .simple-post-manager .wikaz-pm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .simple-post-manager .wikaz-pm-tools {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .simple-post-manager .wikaz-search-box {
        position: relative;
        max-width: 300px;
    }

    .simple-post-manager .wikaz-search-box input {
        width: 100%;
        padding: 8px 35px 8px 12px;
        border-radius: 20px;
        border: 1px solid #ddd;
    }

    .simple-post-manager .wikaz-search-box .dashicons {
        position: absolute;
        right: 10px;
        top: 8px;
        color: #999;
    }

    .simple-post-manager .column-thumb {
        width: 80px;
    }

    .simple-post-manager .column-thumb img {
        width: 60px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }
    
    .simple-post-manager .column-actions {
        width: 120px;
        text-align: right;
    }

    .simple-post-manager .wikaz-pagination {
        margin-top: 20px;
        text-align: center;
    }

    /* Modal Tweaks */
    .wikaz-modal-content.large-modal {
        max-width: 900px;
        width: 90%;
        height: auto;
        max-height: 90vh;
    }
    
    .wikaz-form-group.full-width {
        width: 100%;
        grid-column: 1 / -1;
    }

    /* Fix full width container override */
    .wikaz-admin-container.full-width {
        display: block;
        grid-template-columns: none;
    }

    .wikaz-admin-container.full-width .wikaz-pm-tools {
        margin-bottom: 20px;
    }

    /* Summernote z-index fix within modal */
    .note-modal-backdrop {
        z-index: 100001 !important;
    }
    .note-modal {
        z-index: 100002 !important;
    }
</style>
