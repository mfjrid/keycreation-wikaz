<?php
/**
 * General Dashboard Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wikaz-admin-wrap">
    <div class="wikaz-admin-header">
        <div class="header-main">
            <h1><?php _e('Wikaz Design Dashboard', 'keycreation-wikaz'); ?></h1>
            <p class="description">
                <?php _e('Welcome to your premium store customization hub. Manage all design elements from one central place.', 'keycreation-wikaz'); ?>
            </p>
        </div>
    </div>

    <div class="wikaz-dashboard-grid">
        <!-- Product Manager Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-products"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Product Manager', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Easily manage your WooCommerce products, variations, and stock in a simplified interface.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-product-manager'); ?>"
                    class="button button-primary">
                    <?php _e('Go to Product Manager', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>

        <!-- Master Data Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-database"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Master Data Manager', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Manage your store taxonomy: Categories, Tags, and Product Attributes with ease.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-master-data'); ?>" class="button button-primary">
                    <?php _e('Go to Master Data', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>

        <!-- Carousel Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-images-alt2"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Product Carousel', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Create stunning, premium product carousels for your homepage to boost engagement.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-design-carousel'); ?>"
                    class="button button-primary">
                    <?php _e('Go to Carousel', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>

        <!-- Marquee Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons- megaphone"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Running Text (Marquee)', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Add dynamic announcements and promotions to your site with a sleek running text bar.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-marquee'); ?>" class="button button-primary">
                    <?php _e('Go to Marquee', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Links / Documentation Section -->
    <div class="wikaz-dashboard-footer-section">
        <div class="wikaz-panel">
            <h3><?php _e('Need Help?', 'keycreation-wikaz'); ?></h3>
            <p><?php _e('If you encounter any issues or need custom feature requests, feel free to contact our support team.', 'keycreation-wikaz'); ?>
            </p>
            <div class="footer-links">
                <a href="#" class="footer-link"><span class="dashicons dashicons-external"></span> Documentation</a>
                <a href="#" class="footer-link"><span class="dashicons dashicons-sos"></span> Support</a>
            </div>
        </div>
    </div>
</div>