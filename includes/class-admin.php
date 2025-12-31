<?php
/**
 * Admin functionality for Keycreation Wikaz
 */

if (!defined('ABSPATH')) {
    exit;
}

class Wikaz_Admin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // AJAX handlers
        add_action('wp_ajax_wikaz_save_slide', array($this, 'ajax_save_slide'));
        add_action('wp_ajax_wikaz_delete_slide', array($this, 'ajax_delete_slide'));
        add_action('wp_ajax_wikaz_update_order', array($this, 'ajax_update_order'));
        add_action('wp_ajax_wikaz_search_products', array($this, 'ajax_search_products'));
        add_action('wp_ajax_wikaz_toggle_slide', array($this, 'ajax_toggle_slide'));
        add_action('wp_ajax_wikaz_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wikaz_get_slide', array($this, 'ajax_get_slide'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('Wikaz Design', 'keycreation-wikaz'),
            __('Wikaz Design', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-design',
            array($this, 'render_carousel_page'),
            'dashicons-art',
            30
        );

        add_submenu_page(
            'wikaz-design',
            __('Carousel', 'keycreation-wikaz'),
            __('Carousel', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-design',
            array($this, 'render_carousel_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'wikaz-design') === false) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_style(
            'wikaz-admin-style',
            WIKAZ_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            WIKAZ_VERSION
        );

        wp_enqueue_script(
            'wikaz-admin-script',
            WIKAZ_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery', 'jquery-ui-sortable'),
            WIKAZ_VERSION,
            true
        );

        wp_localize_script('wikaz-admin-script', 'wikazAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wikaz_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this slide?', 'keycreation-wikaz'),
                'selectImage' => __('Select Background Image', 'keycreation-wikaz'),
                'useImage' => __('Use this image', 'keycreation-wikaz'),
                'saving' => __('Saving...', 'keycreation-wikaz'),
                'saved' => __('Saved!', 'keycreation-wikaz'),
                'error' => __('An error occurred', 'keycreation-wikaz'),
            )
        ));
    }

    /**
     * Render carousel admin page
     */
    public function render_carousel_page()
    {
        // Ensure table exists (fallback for activation hook)
        $this->maybe_create_table();
        require_once WIKAZ_PLUGIN_DIR . 'admin/dashboard.php';
    }

    /**
     * Ensure table exists
     */
    private function maybe_create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';
        $column_exists = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = 'layout' AND TABLE_SCHEMA = '" . DB_NAME . "'");

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name || empty($column_exists)) {
            $plugin = Keycreation_Wikaz::get_instance();
            $plugin->activate();
        }
    }

    /**
     * Get all slides
     */
    public static function get_slides()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY sort_order ASC");
    }

    /**
     * AJAX: Save slide
     */
    public function ajax_save_slide()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';

        $slide_id = isset($_POST['slide_id']) ? intval($_POST['slide_id']) : 0;
        $data = array(
            'product_id' => !empty($_POST['product_id']) ? intval($_POST['product_id']) : null,
            'title' => sanitize_text_field($_POST['title']),
            'subtitle' => sanitize_text_field($_POST['subtitle']),
            'background_image' => esc_url_raw($_POST['background_image']),
            'layout' => sanitize_text_field($_POST['layout']),
            'button_text' => sanitize_text_field($_POST['button_text']),
            'button_url' => esc_url_raw($_POST['button_url']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        );

        if ($slide_id > 0) {
            $result = $wpdb->update($table_name, $data, array('id' => $slide_id));
        } else {
            // Get max sort order
            $max_order = $wpdb->get_var("SELECT MAX(sort_order) FROM $table_name");
            $data['sort_order'] = ($max_order !== null) ? $max_order + 1 : 0;
            $result = $wpdb->insert($table_name, $data);
            $slide_id = $wpdb->insert_id;
        }

        if ($result === false) {
            wp_send_json_error(array('message' => $wpdb->last_error));
        }

        wp_send_json_success(array('slide_id' => $slide_id));
    }

    /**
     * AJAX: Delete slide
     */
    public function ajax_delete_slide()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';

        $slide_id = intval($_POST['slide_id']);
        $wpdb->delete($table_name, array('id' => $slide_id));

        wp_send_json_success();
    }

    /**
     * AJAX: Update slide order
     */
    public function ajax_update_order()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';

        $order = isset($_POST['order']) ? $_POST['order'] : array();

        foreach ($order as $index => $slide_id) {
            $wpdb->update(
                $table_name,
                array('sort_order' => $index),
                array('id' => intval($slide_id))
            );
        }

        wp_send_json_success();
    }

    /**
     * AJAX: Search WooCommerce products
     */
    public function ajax_search_products()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $search = sanitize_text_field($_POST['search']);

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 10,
            's' => $search,
            'post_status' => 'publish'
        );

        $products = get_posts($args);
        $results = array();

        foreach ($products as $product) {
            $wc_product = wc_get_product($product->ID);
            $image = wp_get_attachment_image_url($wc_product->get_image_id(), 'thumbnail');

            $results[] = array(
                'id' => $product->ID,
                'title' => $product->post_title,
                'image' => $image ? $image : wc_placeholder_img_src('thumbnail'),
                'price' => $wc_product->get_price_html(),
                'url' => get_permalink($product->ID)
            );
        }

        wp_send_json_success($results);
    }

    /**
     * AJAX: Toggle slide active status
     */
    public function ajax_toggle_slide()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';

        $slide_id = intval($_POST['slide_id']);
        $is_active = intval($_POST['is_active']);

        $wpdb->update(
            $table_name,
            array('is_active' => $is_active),
            array('id' => $slide_id)
        );

        wp_send_json_success();
    }

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        update_option('wikaz_carousel_autoplay', isset($_POST['autoplay']) ? '1' : '0');
        update_option('wikaz_carousel_speed', intval($_POST['speed']));
        update_option('wikaz_carousel_position', sanitize_text_field($_POST['position']));
        update_option('wikaz_header_transparent', isset($_POST['header_transparent']) ? '1' : '0');

        wp_send_json_success();
    }

    /**
     * AJAX: Get single slide data
     */
    public function ajax_get_slide()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';

        $slide_id = intval($_POST['slide_id']);
        $slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $slide_id));

        if (!$slide) {
            wp_send_json_error('Slide not found');
        }

        $data = (array) $slide;

        // Add product data if linked
        if ($slide->product_id) {
            $product = wc_get_product($slide->product_id);
            if ($product) {
                $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                $data['product'] = array(
                    'id' => $slide->product_id,
                    'title' => $product->get_name(),
                    'image' => $image ? $image : wc_placeholder_img_src('thumbnail'),
                    'url' => get_permalink($slide->product_id)
                );
            }
        }

        wp_send_json_success($data);
    }
}
