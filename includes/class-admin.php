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
        add_action('wp_ajax_wikaz_save_marquee', array($this, 'ajax_save_marquee'));
        add_action('wp_ajax_wikaz_get_slide', array($this, 'ajax_get_slide'));

        // Product Manager Actions
        add_action('wp_ajax_wikaz_get_pm_products', array($this, 'ajax_get_pm_products'));
        add_action('wp_ajax_wikaz_save_pm_product', array($this, 'ajax_save_pm_product'));
        add_action('wp_ajax_wikaz_delete_pm_product', array($this, 'ajax_delete_pm_product'));
        add_action('wp_ajax_wikaz_get_pm_attributes', array($this, 'ajax_get_pm_attributes'));

        // Master Data Handlers
        add_action('wp_ajax_wikaz_get_master_categories', array($this, 'ajax_get_master_categories'));
        add_action('wp_ajax_wikaz_get_master_tags', array($this, 'ajax_get_master_tags'));
        add_action('wp_ajax_wikaz_get_master_terms', array($this, 'ajax_get_master_terms'));
        add_action('wp_ajax_wikaz_save_master_item', array($this, 'ajax_save_master_item'));
        add_action('wp_ajax_wikaz_delete_master_item', array($this, 'ajax_delete_master_item'));
        add_action('wp_ajax_wikaz_save_master_attribute_type', array($this, 'ajax_save_master_attribute_type'));
        add_action('wp_ajax_wikaz_delete_master_attribute_type', array($this, 'ajax_delete_master_attribute_type'));
        add_action('wp_ajax_wikaz_get_pm_product', array($this, 'ajax_get_pm_product'));
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
            array($this, 'render_general_dashboard_page'),
            'dashicons-art',
            2
        );

        add_submenu_page(
            'wikaz-design',
            __('Running Text Settings', 'keycreation-wikaz'),
            __('Running Text', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-marquee',
            array($this, 'render_marquee_page')
        );

        add_submenu_page(
            'wikaz-design',
            __('Master Data', 'keycreation-wikaz'),
            __('Master Data', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-master-data',
            array($this, 'render_master_dashboard_page')
        );

        add_submenu_page(
            'wikaz-design',
            __('Carousel', 'keycreation-wikaz'),
            __('Carousel', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-carousel',
            array($this, 'render_carousel_page')
        );

        add_submenu_page(
            'wikaz-design',
            __('Product Manager', 'keycreation-wikaz'),
            __('Product Manager', 'keycreation-wikaz'),
            'manage_options',
            'wikaz-product-manager',
            array($this, 'render_product_manager_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        if (
            strpos($hook, 'wikaz-design') === false &&
            strpos($hook, 'wikaz-marquee') === false &&
            strpos($hook, 'wikaz-product-manager') === false &&
            strpos($hook, 'wikaz-master-data') === false &&
            strpos($hook, 'wikaz-carousel') === false
        ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('select2');
        wp_enqueue_script('select2');

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
     * Render general dashboard page
     */
    public function render_general_dashboard_page()
    {
        require_once WIKAZ_PLUGIN_DIR . 'admin/general-dashboard.php';
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
     * Render marquee admin page
     */
    public function render_marquee_page()
    {
        require_once WIKAZ_PLUGIN_DIR . 'admin/marquee-dashboard.php';
    }

    /**
     * Render product manager admin page
     */
    public function render_product_manager_page()
    {
        require_once WIKAZ_PLUGIN_DIR . 'admin/product-dashboard.php';
    }

    /**
     * Render master dashboard page
     */
    public function render_master_dashboard_page()
    {
        require_once WIKAZ_PLUGIN_DIR . 'admin/master-dashboard.php';
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
     * AJAX: Save marquee settings
     */
    public function ajax_save_marquee()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'keycreation-wikaz'));
        }

        $marquee_items = isset($_POST['marquee_items']) ? $_POST['marquee_items'] : array();
        $sanitized_items = array();

        if (is_array($marquee_items)) {
            foreach ($marquee_items as $item) {
                if (empty($item['text']))
                    continue;
                $sanitized_items[] = array(
                    'text' => sanitize_text_field($item['text']),
                    'link' => esc_url_raw($item['link'])
                );
            }
        }

        $json_value = wp_json_encode($sanitized_items);
        set_theme_mod('topbar_marquee_arr', $json_value);

        wp_send_json_success(__('Marquee settings saved', 'keycreation-wikaz'));
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

    /**
     * AJAX: Get products for Product Manager
     */
    public function ajax_get_pm_products()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = 10;

        $args = array(
            'status' => 'publish',
            'limit' => $per_page,
            'page' => $page,
            'paginate' => true,
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $products = wc_get_products($args);
        $data = array();

        foreach ($products->products as $product) {
            $price = $product->get_price();
            if ($product->is_type('variable')) {
                $min = $product->get_variation_regular_price('min');
                $max = $product->get_variation_regular_price('max');
                $price = ($min === $max) ? $min : $min . ' - ' . $max;
            }

            $data[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'price' => $price,
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail'),
                'type' => $product->get_type(),
                'stock' => $product->get_stock_quantity(),
                'variations_count' => $product->is_type('variable') ? count($product->get_children()) : 0
            );
        }

        wp_send_json_success(array(
            'products' => $data,
            'total_pages' => $products->max_num_pages
        ));
    }

    /**
     * AJAX: Get single product for PM editor
     */
    public function ajax_get_pm_product()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $product_id = intval($_POST['product_id']);
        $product = wc_get_product($product_id);

        if (!$product)
            wp_send_json_error('Product not found');

        $data = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'description' => $product->get_description('edit'),
            'short_description' => $product->get_short_description('edit'),
            'price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'sku' => $product->get_sku(),
            'type' => $product->get_type(),
            'image_id' => $product->get_image_id(),
            'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'large'),
            'gallery_images' => array_map(function ($id) {
                return array('id' => $id, 'url' => wp_get_attachment_image_url($id, 'thumbnail'));
            }, $product->get_gallery_image_ids()),
            'categories' => $product->get_category_ids(),
            'tags' => $product->get_tag_ids(),
            'video_url' => get_post_meta($product->get_id(), '_video_url', true),
            'attributes' => array(),
            'variations' => array()
        );

        if ($product->is_type('variable')) {
            $product_attributes = $product->get_attributes();
            foreach ($product_attributes as $slug => $attr) {
                // Remove pa_ prefix for JS matching
                $clean_slug = str_replace('pa_', '', $slug);
                $options = $attr->get_options();
                if ($attr->is_taxonomy()) {
                    $slug_options = array();
                    foreach ($options as $id) {
                        $term = get_term($id);
                        if ($term)
                            $slug_options[] = $term->slug;
                    }
                    $data['attributes'][$clean_slug] = $slug_options;
                } else {
                    $data['attributes'][$clean_slug] = $options;
                }
            }

            foreach ($product->get_children() as $var_id) {
                $var = wc_get_product($var_id);
                if (!$var)
                    continue;

                $data['variations'][] = array(
                    'id' => $var_id,
                    'sku' => $var->get_sku(),
                    'price' => $var->get_regular_price('edit') ?: $var->get_price('edit'),
                    'stock' => $var->get_stock_quantity('edit'),
                    'attributes' => $var->get_attributes()
                );
            }
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Get WooCommerce attributes
     */
    public function ajax_get_pm_attributes()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $attributes = array();

        foreach ($attribute_taxonomies as $tax) {
            $taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
            $terms = get_terms(array('taxonomy' => $taxonomy_name, 'hide_empty' => false));

            $attributes[] = array(
                'id' => $tax->attribute_id,
                'slug' => $tax->attribute_name,
                'label' => $tax->attribute_label,
                'terms' => array_map(function ($term) {
                    return array('id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug);
                }, $terms)
            );
        }

        wp_send_json_success($attributes);
    }

    /**
     * AJAX: Delete product
     */
    public function ajax_delete_pm_product()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $product_id = intval($_POST['product_id']);
        $result = wp_delete_post($product_id, true);

        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete product');
        }
    }

    /**
     * AJAX: Save product (Simple or Variable)
     */
    public function ajax_save_pm_product()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $product_id = intval($_POST['product_id']);
        $is_new = ($product_id === 0);

        // Determine type based on variations presence
        $variations_data = isset($_POST['variations']) ? $_POST['variations'] : array();
        $type = !empty($variations_data) ? 'variable' : 'simple';

        // 1. Create or Load Product
        if ($is_new) {
            $product = ($type === 'variable') ? new WC_Product_Variable() : new WC_Product_Simple();
        } else {
            $product = wc_get_product($product_id);
            // If type changed (rare but possible), it's tricky. We'll stick to original type if editing.
            if ($product->get_type() !== $type) {
                // Force type change logic if needed, but for now we follow the creator's flow.
            }
        }

        if (!$product)
            wp_send_json_error('Failed to load product');

        // 2. Set Basic Data
        $product->set_name(sanitize_text_field($_POST['name']));
        $product->set_status('publish');
        $product->set_sku(sanitize_text_field($_POST['sku']));
        $product->set_description(wp_kses_post($_POST['description']));
        $product->set_short_description(wp_kses_post($_POST['short_description']));
        $product->set_category_ids(isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array());
        $product->set_tag_ids(isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : array());

        if (isset($_POST['video_url'])) {
            $product->update_meta_data('_video_url', esc_url_raw($_POST['video_url']));
        }

        if (!empty($_POST['image_id'])) {
            $product->set_image_id(intval($_POST['image_id']));
        }

        if (!empty($_POST['gallery_ids'])) {
            $gallery_ids = array_map('intval', explode(',', $_POST['gallery_ids']));
            $product->set_gallery_image_ids($gallery_ids);
        }

        if ($type === 'simple') {
            $product->set_regular_price(sanitize_text_field($_POST['price']));
        }

        // 3. Handle Variable Product Attributes
        if ($type === 'variable') {
            $attributes_data = isset($_POST['attributes']) ? $_POST['attributes'] : array();
            $product_attributes = array();
            $index = 2;

            foreach ($attributes_data as $tax_slug => $terms) {
                $taxonomy = wc_attribute_taxonomy_name($tax_slug);
                $attribute = new WC_Product_Attribute();
                $attribute->set_id(wc_attribute_taxonomy_id_by_name($tax_slug));
                $attribute->set_name($taxonomy);
                $attribute->set_options($terms);

                // Set position based on importance
                $pos = 99;
                $slug_lower = strtolower($tax_slug);
                if (strpos($slug_lower, 'size') !== false || strpos($slug_lower, 'ukuran') !== false) {
                    $pos = 0;
                } elseif (strpos($slug_lower, 'color') !== false || strpos($slug_lower, 'warna') !== false) {
                    $pos = 1;
                } else {
                    $pos = $index++;
                }

                $attribute->set_position($pos);
                $attribute->set_visible(true);
                $attribute->set_variation(true);
                $product_attributes[] = $attribute;
            }
            $product->set_attributes($product_attributes);
        }

        // 4. Save Parent Product (Crucial: save BEFORE creating variations)
        $product_id = $product->save();

        // 5. Handle Variations
        if ($type === 'variable' && $product_id) {
            $existing_variations = $product->get_children();
            $processed_variation_ids = array();

            foreach ($variations_data as $v_data) {
                $v_attributes = array();
                foreach ($v_data['attributes'] as $slug => $val) {
                    $v_attributes[wc_attribute_taxonomy_name($slug)] = $val;
                }

                // Check if variation with these attributes already exists
                $variation_id = $this->find_matching_variation($product_id, $v_attributes);

                if ($variation_id) {
                    $variation = new WC_Product_Variation($variation_id);
                } else {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($product_id);
                    $variation->set_attributes($v_attributes);
                }

                $variation->set_regular_price(sanitize_text_field($v_data['price']));
                $variation->set_sku(sanitize_text_field($v_data['sku']));
                $variation->set_manage_stock(true);
                $variation->set_stock_quantity(intval($v_data['stock']));
                $variation->set_status('publish');
                $variation->save();
            }

            // Optional: Remove variations that are no longer in the matrix?
            // For now, let's just keep them to avoid accidental data loss.
        }

        wp_send_json_success(array('id' => $product_id));
    }

    /**
     * Find a variation ID matching specific attributes
     */
    private function find_matching_variation($product_id, $attributes)
    {
        $product = wc_get_product($product_id);
        if (!$product || $product->get_type() !== 'variable')
            return 0;

        $data_store = $product->get_data_store();
        return $data_store->find_matching_product_variation($product, $attributes);
    }

    /**
     * AJAX: Get master categories
     */
    public function ajax_get_master_categories()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));

        $data = array();
        foreach ($categories as $cat) {
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $data[] = array(
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'count' => $cat->count,
                'image' => wp_get_attachment_image_url($thumbnail_id, 'thumbnail') ?: wc_placeholder_img_src('thumbnail'),
                'parent' => $cat->parent
            );
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Get master tags
     */
    public function ajax_get_master_tags()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
        ));

        $data = array();
        foreach ($tags as $tag) {
            $data[] = array(
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->count,
            );
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Get master terms for an attribute
     */
    public function ajax_get_master_terms()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $taxonomy = sanitize_text_field($_POST['taxonomy']);
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        $data = array();
        foreach ($terms as $term) {
            $data[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Save master item
     */
    public function ajax_save_master_item()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $id = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']); // category, tag, term
        $name = sanitize_text_field($_POST['name']);
        $slug = sanitize_text_field($_POST['slug']);
        $taxonomy = ($type === 'term') ? sanitize_text_field($_POST['taxonomy']) : (($type === 'category') ? 'product_cat' : 'product_tag');

        $args = array(
            'name' => $name,
            'slug' => $slug,
        );

        if ($type === 'category') {
            $args['parent'] = intval($_POST['parent']);
        }

        if ($id > 0) {
            $result = wp_update_term($id, $taxonomy, $args);
        } else {
            $result = wp_insert_term($name, $taxonomy, $args);
            if (!is_wp_error($result))
                $id = $result['term_id'];
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Handle category image
        if ($type === 'category' && isset($_POST['image_id'])) {
            update_term_meta($id, 'thumbnail_id', intval($_POST['image_id']));
        }

        wp_send_json_success($id);
    }

    /**
     * AJAX: Delete master item
     */
    public function ajax_delete_master_item()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $id = intval($_POST['id']);
        $taxonomy = sanitize_text_field($_POST['taxonomy']);

        $result = wp_delete_term($id, $taxonomy);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    /**
     * AJAX: Save master attribute type (taxonomy)
     */
    public function ajax_save_master_attribute_type()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $slug = sanitize_text_field($_POST['slug']);

        $args = array(
            'name' => $name,
            'slug' => $slug,
            'type' => 'select',
            'order_by' => 'menu_order',
            'has_archives' => false,
        );

        if ($id > 0) {
            $result = wc_update_attribute($id, $args);
        } else {
            $result = wc_create_attribute($args);
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Delete master attribute type
     */
    public function ajax_delete_master_attribute_type()
    {
        check_ajax_referer('wikaz_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error('Unauthorized');

        $id = intval($_POST['id']);
        $result = wc_delete_attribute($id);

        if (!$result) {
            wp_send_json_error('Failed to delete attribute type');
        }

        wp_send_json_success();
    }
}
