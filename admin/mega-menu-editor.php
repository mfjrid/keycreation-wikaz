<?php
/**
 * Simplified Mega Menu Editor Landing Page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wikaz-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-screenoptions"></span>
        <?php _e('Mega Menu Editor', 'keycreation-wikaz'); ?>
    </h1>
    <hr class="wp-header-end">

    <div class="wikaz-admin-container" style="max-width: 900px; margin-top: 30px;">
        <div class="wikaz-editor-card" style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); text-align: center;">
            <h2 style="font-size: 24px; margin-bottom: 30px;"><?php _e('Pilih Menu yang Ingin Diedit', 'keycreation-wikaz'); ?></h2>
            
            <div class="wikaz-mm-buttons" style="display: flex; gap: 20px; justify-content: center;">
                <a href="<?php echo admin_url('post.php?post=6513&action=elementor'); ?>" target="_blank" class="button button-primary button-hero" style="background: #e91e63; border-color: #e91e63; padding: 0 40px; height: 60px; line-height: 58px; font-size: 18px; border-radius: 8px;">
                    <span class="dashicons dashicons-admin-users" style="font-size: 24px; margin-top: 18px; margin-right: 10px;"></span>
                    <?php _e('Kategori Pria', 'keycreation-wikaz'); ?>
                </a>
                
                <a href="<?php echo admin_url('post.php?post=6519&action=elementor'); ?>" target="_blank" class="button button-primary button-hero" style="background: #9c27b0; border-color: #9c27b0; padding: 0 40px; height: 60px; line-height: 58px; font-size: 18px; border-radius: 8px;">
                    <span class="dashicons dashicons-admin-users" style="font-size: 24px; margin-top: 18px; margin-right: 10px;"></span>
                    <?php _e('Kategori Wanita', 'keycreation-wikaz'); ?>
                </a>
            </div>
            
            <div style="margin-top: 40px; color: #666; font-style: italic;">
                <p><?php _e('Klik tombol di atas untuk membuka editor Elementor langsung pada menu tersebut.', 'keycreation-wikaz'); ?></p>
            </div>
        </div>
    </div>
</div>
