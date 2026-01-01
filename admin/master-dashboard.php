<?php
/**
 * Master Data Dashboard Template
 */
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap wikaz-admin-wrap">
    <div class="wikaz-admin-header">
        <div class="header-main">
            <h1><?php _e('Master Data Manager', 'keycreation-wikaz'); ?></h1>
            <p class="description">
                <?php _e('Manage WooCommerce Categories, Tags, and Attributes in one place.', 'keycreation-wikaz'); ?>
            </p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <h2 class="nav-tab-wrapper pm-tabs-wrapper">
        <a href="#tab-categories" class="nav-tab nav-tab-active"
            data-tab="categories"><?php _e('Categories', 'keycreation-wikaz'); ?></a>
        <a href="#tab-tags" class="nav-tab" data-tab="tags"><?php _e('Tags', 'keycreation-wikaz'); ?></a>
        <a href="#tab-attributes" class="nav-tab"
            data-tab="attributes"><?php _e('Attributes', 'keycreation-wikaz'); ?></a>
    </h2>

    <div class="wikaz-pm-container master-data-container">
        <!-- Categories Tab -->
        <div id="tab-categories" class="pm-tab-content active">
            <div class="master-panel">
                <div class="panel-header">
                    <h3><?php _e('Product Categories', 'keycreation-wikaz'); ?></h3>
                    <button type="button" class="button button-primary add-master-item" data-type="category">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add New Category', 'keycreation-wikaz'); ?>
                    </button>
                </div>
                <div class="pm-table-responsive">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th width="80"><?php _e('Image', 'keycreation-wikaz'); ?></th>
                                <th><?php _e('Name', 'keycreation-wikaz'); ?></th>
                                <th><?php _e('Slug', 'keycreation-wikaz'); ?></th>
                                <th width="100"><?php _e('Count', 'keycreation-wikaz'); ?></th>
                                <th width="120"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="master-categories-list">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tags Tab -->
        <div id="tab-tags" class="pm-tab-content">
            <div class="master-panel">
                <div class="panel-header">
                    <h3><?php _e('Product Tags', 'keycreation-wikaz'); ?></h3>
                    <button type="button" class="button button-primary add-master-item" data-type="tag">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Add New Tag', 'keycreation-wikaz'); ?>
                    </button>
                </div>
                <div class="pm-table-responsive">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'keycreation-wikaz'); ?></th>
                                <th><?php _e('Slug', 'keycreation-wikaz'); ?></th>
                                <th width="100"><?php _e('Count', 'keycreation-wikaz'); ?></th>
                                <th width="120"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="master-tags-list">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Attributes Tab -->
        <div id="tab-attributes" class="pm-tab-content">
            <div class="master-two-panel">
                <!-- Sidebar: Attribute Types -->
                <div class="master-sidebar">
                    <div class="panel-header">
                        <h4><?php _e('Attribute Types', 'keycreation-wikaz'); ?></h4>
                    </div>
                    <ul id="master-attributes-type-list" class="master-side-list">
                        <!-- Populated via AJAX -->
                    </ul>
                    <button type="button" class="button button-secondary button-small full-width add-attribute-type">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add New Type', 'keycreation-wikaz'); ?>
                    </button>
                </div>

                <!-- Main: Attribute Values (Terms) -->
                <div class="master-main-panel">
                    <div class="panel-header">
                        <h3 id="current-attribute-label"><?php _e('Select an attribute', 'keycreation-wikaz'); ?></h3>
                        <button type="button" class="button button-primary add-master-term" style="display:none;">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e('Add New Value', 'keycreation-wikaz'); ?>
                        </button>
                    </div>
                    <div class="pm-table-responsive">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Name', 'keycreation-wikaz'); ?></th>
                                    <th><?php _e('Slug', 'keycreation-wikaz'); ?></th>
                                    <th width="120"><?php _e('Actions', 'keycreation-wikaz'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="master-terms-list">
                                <tr>
                                    <td colspan="3" align="center">
                                        <?php _e('Please select an attribute type from the sidebar.', 'keycreation-wikaz'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Master Data CRUD -->
    <div id="wikaz-master-modal" class="wikaz-modal">
        <div class="wikaz-modal-content">
            <div class="wikaz-modal-header">
                <h2 id="wikaz-master-modal-title">Add New Item</h2>
                <button type="button" class="wikaz-modal-close">&times;</button>
            </div>
            <form id="wikaz-master-form">
                <input type="hidden" id="master-item-id" value="0">
                <input type="hidden" id="master-item-type" value="">
                <input type="hidden" id="master-item-taxonomy" value="">

                <div class="wikaz-modal-body">
                    <div id="master-attr-type-fields" style="display:none;">
                        <p class="description" style="margin-bottom:15px;">
                            <?php _e('Creating/Editing an attribute type (e.g., Material, Brand).', 'keycreation-wikaz'); ?>
                        </p>
                    </div>

                    <div class="wikaz-form-group" id="master-image-group" style="display:none;">
                        <label><?php _e('Thumbnail', 'keycreation-wikaz'); ?></label>
                        <div id="master-image-preview" class="pm-image-uploader">
                            <div class="placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                                <p>Click to upload</p>
                            </div>
                            <img src="" style="display:none;">
                            <input type="hidden" id="master-item-image-id" value="">
                        </div>
                    </div>

                    <div class="wikaz-form-group">
                        <label id="master-item-name-label"><?php _e('Name', 'keycreation-wikaz'); ?> <span
                                class="required">*</span></label>
                        <input type="text" id="master-item-name" required placeholder="Ex: Khimar, Blue, XL...">
                    </div>

                    <div class="wikaz-form-group" id="master-slug-group">
                        <label><?php _e('Slug', 'keycreation-wikaz'); ?></label>
                        <input type="text" id="master-item-slug" placeholder="Auto-generated if empty">
                    </div>

                    <div class="wikaz-form-group" id="master-parent-group" style="display:none;">
                        <label><?php _e('Parent Category', 'keycreation-wikaz'); ?></label>
                        <select id="master-item-parent" class="pm-select2">
                            <option value="0">None</option>
                        </select>
                    </div>
                </div>
                <div class="wikaz-modal-footer">
                    <button type="button"
                        class="button wikaz-modal-close"><?php _e('Cancel', 'keycreation-wikaz'); ?></button>
                    <button type="submit" class="button button-primary" id="master-save-btn">
                        <?php _e('Save Changes', 'keycreation-wikaz'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>