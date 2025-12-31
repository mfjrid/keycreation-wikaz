<?php
/**
 * Plugin Name: Keycreation Wikaz
 * Plugin URI: https://wikaz.id
 * Description: Custom design enhancements for Wikaz website - Carousel, banners, and more.
 * Version: 1.0.0
 * Author: Keycreation
 * Author URI: https://wikaz.id
 * License: GPL v2 or later
 * Text Domain: keycreation-wikaz
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WIKAZ_VERSION', '1.0.0');
define('WIKAZ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WIKAZ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WIKAZ_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Keycreation_Wikaz
{

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {
        require_once WIKAZ_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WIKAZ_PLUGIN_DIR . 'public/class-frontend.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Init admin
        if (is_admin()) {
            new Wikaz_Admin();
        }

        // Init frontend
        new Wikaz_Frontend();
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            product_id BIGINT DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            background_image VARCHAR(500) DEFAULT NULL,
            layout VARCHAR(20) DEFAULT 'full-bg',
            button_text VARCHAR(100) DEFAULT 'Shop Now',
            button_url VARCHAR(500) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Manually ensure layout column exists (dbDelta can be finicky with updates)
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'layout' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($row)) {
            $wpdb->query("ALTER TABLE $table_name ADD layout VARCHAR(20) DEFAULT 'full-bg' AFTER background_image");
        }

        // Set default options
        add_option('wikaz_carousel_autoplay', '1');
        add_option('wikaz_carousel_speed', '5000');
        add_option('wikaz_carousel_position', 'before_content');
    }
}

// Initialize plugin
function wikaz_init()
{
    return Keycreation_Wikaz::get_instance();
}
add_action('plugins_loaded', 'wikaz_init');
