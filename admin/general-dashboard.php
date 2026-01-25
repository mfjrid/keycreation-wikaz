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
                <h3><?php _e('Home Carousel', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Create stunning, premium carousels for your homepage to boost engagement.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-carousel'); ?>"
                    class="button button-primary">
                    <?php _e('Go to Carousel', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>

        <!-- Marquee Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-megaphone"></span>
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

        <!-- Slider Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-slides"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Header Slider', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Create eye-catching header sliders to highlight promotions, campaigns, or key messages.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-header-sliders'); ?>" class="button button-primary">
                    <?php _e('Go to Header Slider', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>

        <!-- Simple Post Card -->
        <div class="wikaz-dashboard-card">
            <div class="card-icon">
                <span class="dashicons dashicons-welcome-write-blog"></span>
            </div>
            <div class="card-content">
                <h3><?php _e('Simple Post', 'keycreation-wikaz'); ?></h3>
                <p><?php _e('Add simple, structured posts for essential content and site updates.', 'keycreation-wikaz'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=wikaz-simple-post'); ?>" class="button button-primary">
                    <?php _e('Go to Simple Post', 'keycreation-wikaz'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Links / Documentation Section -->
    <div class="wikaz-dashboard-footer-section">
        <div class="wikaz-panel">
            <h3><?php _e('Need Help?', 'keycreation-wikaz'); ?></h3>
            <p><?php _e('If you encounter any issues or need custom feature requests, feel free to contact KeyCreation team.', 'keycreation-wikaz'); ?>
            </p>
            <div class="footer-links">
                <a href="https://keycreationofficial.com/" target="_blank" class="footer-link"><span class="dashicons dashicons-sos"></span> Support</a>
            </div>
        </div>
    </div>
</div>