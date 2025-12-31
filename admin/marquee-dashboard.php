<?php
/**
 * Marquee Settings Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

$marquee_value = get_theme_mod('topbar_marquee_arr', '');
$marquee_items = json_decode($marquee_value, true);

// Fallback to default if empty
if (empty($marquee_items)) {
    $marquee_items = array(
        array('text' => 'Busana Muslim Sutra Paling Halus', 'link' => '#'),
        array('text' => 'Pengiriman cepat ke seluruh Indonesia', 'link' => '#'),
        array('text' => 'Tampilan elegan di setiap lapisan', 'link' => '#')
    );
}
?>

<div class="wrap wikaz-admin-wrap marquee-settings">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-megaphone"></span>
        <?php _e('Top Bar Marquee Settings', 'keycreation-wikaz'); ?>
    </h1>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container">
        <div class="wikaz-settings-section full-width">
            <div class="wikaz-settings-panel">
                <h3>
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('Marquee Items', 'keycreation-wikaz'); ?>
                </h3>
                <p class="description">
                    <?php _e('Manage the scrolling text items in your top bar header.', 'keycreation-wikaz'); ?>
                </p>

                <form id="wikaz-marquee-form">
                    <div id="marquee-items-container">
                        <?php foreach ($marquee_items as $index => $item): ?>
                            <div class="marquee-item-row" data-index="<?php echo $index; ?>">
                                <div class="wikaz-form-group">
                                    <label><?php _e('Text', 'keycreation-wikaz'); ?></label>
                                    <input type="text" name="marquee_items[<?php echo $index; ?>][text]"
                                        value="<?php echo esc_attr($item['text']); ?>" class="widefat"
                                        placeholder="Scrolling text...">
                                </div>
                                <div class="wikaz-form-group">
                                    <label><?php _e('Link', 'keycreation-wikaz'); ?></label>
                                    <input type="text" name="marquee_items[<?php echo $index; ?>][link]"
                                        value="<?php echo esc_attr($item['link']); ?>" class="widefat"
                                        placeholder="https://...">
                                </div>
                                <button type="button" class="button remove-marquee-item"
                                    title="<?php _e('Remove', 'keycreation-wikaz'); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="marquee-actions">
                        <button type="button" class="button" id="add-marquee-item">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Add Item', 'keycreation-wikaz'); ?>
                        </button>
                        <button type="submit" class="button button-primary" id="save-marquee">
                            <?php _e('Save Changes', 'keycreation-wikaz'); ?>
                        </button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .marquee-item-row {
        background: #fff;
        border: 1px solid #ccd0d4;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
        display: flex;
        gap: 15px;
        align-items: flex-end;
    }

    .marquee-item-row .wikaz-form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .marquee-item-row .remove-marquee-item {
        color: #a00;
        border-color: #ccd0d4;
    }

    .marquee-item-row .remove-marquee-item:hover {
        background: #fbe9e9;
    }

    .marquee-actions {
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .marquee-settings .wikaz-settings-panel {
        max-width: 800px;
    }
</style>