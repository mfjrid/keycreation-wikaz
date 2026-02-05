<?php
/**
 * Plugin Name: Keycreation Wikaz
 * Plugin URI: https://keycreationofficial.com
 * Description: Custom design enhancements for Wikaz website - Carousel, product management, and more.
 * Version: 1.0.0
 * Author: Keycreation
 * Author URI: https://keycreationofficial.com
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
define('WIKAZ_VERSION', '1.0.2');
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
        $this->maybe_update_database();
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {
        require_once WIKAZ_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WIKAZ_PLUGIN_DIR . 'public/class-frontend.php';
        require_once WIKAZ_PLUGIN_DIR . 'public/class-post-list.php';
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
        new Wikaz_Post_List();

        // Register CPT
        // add_action('init', array($this, 'register_cpt'));
    }

    /**
     * Maybe update database schema
     */
    public function maybe_update_database()
    {
        $db_version = get_option('wikaz_db_version', '0.0.0');
        if (version_compare($db_version, WIKAZ_VERSION, '<')) {
            $this->activate();
            update_option('wikaz_db_version', WIKAZ_VERSION);
        }
    }

    /**
     * Register Custom Post Type
     */
    public function register_cpt()
    {
        register_post_type('wikaz_simple_post', array(
            'labels' => array(
                'name' => __('Simple Posts', 'keycreation-wikaz'),
                'singular_name' => __('Simple Post', 'keycreation-wikaz'),
                'add_new' => __('Add New', 'keycreation-wikaz'),
                'add_new_item' => __('Add New Simple Post', 'keycreation-wikaz'),
                'edit_item' => __('Edit Simple Post', 'keycreation-wikaz'),
                'new_item' => __('New Simple Post', 'keycreation-wikaz'),
                'view_item' => __('View Simple Post', 'keycreation-wikaz'),
                'search_items' => __('Search Simple Posts', 'keycreation-wikaz'),
                'not_found' => __('No simple posts found', 'keycreation-wikaz'),
                'not_found_in_trash' => __('No simple posts found in Trash', 'keycreation-wikaz'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'simple-post'),
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_menu' => false, // We use our custom menu
        ));
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
            post_id BIGINT DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            background_image VARCHAR(500) DEFAULT NULL,
            background_video VARCHAR(500) DEFAULT NULL,
            layout VARCHAR(20) DEFAULT 'full-bg',
            media_type VARCHAR(20) DEFAULT 'image',
            link_source VARCHAR(20) DEFAULT 'product',
            description TEXT DEFAULT NULL,
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

        // Manually ensure description column exists
        $desc_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'description' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($desc_row)) {
            $wpdb->query("ALTER TABLE $table_name ADD description TEXT DEFAULT NULL AFTER layout");
        }

        // Manually ensure background_video column exists
        $video_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'background_video' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($video_row)) {
            $wpdb->query("ALTER TABLE $table_name ADD background_video VARCHAR(500) DEFAULT NULL AFTER background_image");
        }

        // Manually ensure post_id column exists
        $post_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'post_id' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($post_row)) {
            $wpdb->query("ALTER TABLE $table_name ADD post_id BIGINT DEFAULT NULL AFTER product_id");
        }

        // Manually ensure media_type column exists
        $mt_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'media_type' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($mt_row)) {
            $wpdb->query("ALTER TABLE $table_name ADD media_type VARCHAR(20) DEFAULT 'image' AFTER layout");
        }

        // Manually ensure link_source column exists
        $ls_row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'link_source' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($ls_row)) {
            $wpdb->query("ALTER TABLE $table_name ADD link_source VARCHAR(20) DEFAULT 'product' AFTER media_type");
        }

        // Set default options
        add_option('wikaz_carousel_autoplay', '1');
        add_option('wikaz_carousel_speed', '5000');
        add_option('wikaz_carousel_position', 'before_content');

        // Header Slider Tables
        $sliders_table = $wpdb->prefix . 'wikaz_header_sliders';
        $sql_sliders = "CREATE TABLE IF NOT EXISTS $sliders_table (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            autoplay TINYINT(1) DEFAULT 1,
            speed INT DEFAULT 5000,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_sliders);

        $slider_slides_table = $wpdb->prefix . 'wikaz_header_slider_slides';
        $sql_slider_slides = "CREATE TABLE IF NOT EXISTS $slider_slides_table (
            id INT NOT NULL AUTO_INCREMENT,
            slider_id INT NOT NULL,
            product_id BIGINT DEFAULT NULL,
            post_id BIGINT DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            background_image VARCHAR(500) DEFAULT NULL,
            background_video VARCHAR(500) DEFAULT NULL,
            layout VARCHAR(20) DEFAULT 'full-bg',
            media_type VARCHAR(20) DEFAULT 'image',
            link_source VARCHAR(20) DEFAULT 'product',
            description TEXT DEFAULT NULL,
            button_text VARCHAR(100) DEFAULT 'Shop Now',
            button_url VARCHAR(500) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slider_id (slider_id)
        ) $charset_collate;";
        dbDelta($sql_slider_slides);

        // Ensure media_type and link_source for header slides too
        $mt_row_hs = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$slider_slides_table' AND COLUMN_NAME = 'media_type' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($mt_row_hs)) {
            $wpdb->query("ALTER TABLE $slider_slides_table ADD media_type VARCHAR(20) DEFAULT 'image' AFTER layout");
        }
        $ls_row_hs = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$slider_slides_table' AND COLUMN_NAME = 'link_source' AND TABLE_SCHEMA = '" . DB_NAME . "'");
        if (empty($ls_row_hs)) {
            $wpdb->query("ALTER TABLE $slider_slides_table ADD link_source VARCHAR(20) DEFAULT 'product' AFTER media_type");
        }
    }
}

// Initialize plugin
function wikaz_init()
{
    return Keycreation_Wikaz::get_instance();
}
add_action('plugins_loaded', 'wikaz_init');
