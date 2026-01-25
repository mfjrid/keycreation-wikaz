<?php
/**
 * Frontend functionality for Keycreation Wikaz
 */

if (!defined('ABSPATH')) {
    exit;
}

class Wikaz_Frontend
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        // Add body class for transparent header
        add_filter('body_class', array($this, 'add_body_class'));
        add_shortcode('wikaz_carousel', array($this, 'render_carousel'));
        add_shortcode('wikaz_header_slider', array($this, 'render_header_slider'));

        // Auto-inject carousel based on position setting
        $position = get_option('wikaz_carousel_position', 'before_content');
        if ($position === 'before_content') {
            add_action('wp_body_open', array($this, 'maybe_inject_carousel'), 5);
        }

        // Product page customizations
        add_filter('woocommerce_get_availability', array($this, 'custom_stock_availability'), 10, 2);
        
        // Clear default variation selection on product page
        add_filter('woocommerce_product_get_default_attributes', array($this, 'clear_default_attributes'), 10, 2);
        
        // Reorder variation attributes: Color first, then Size
        add_filter('woocommerce_product_get_attributes', array($this, 'reorder_variation_attributes'), 10, 2);
        add_filter('woocommerce_get_variation_prices_hash', array($this, 'variation_prices_hash'), 10, 3);
    }

    /**
     * Reorder product variation attributes: Color first, then Size
     */
    public function reorder_variation_attributes($attributes, $product)
    {
        if (empty($attributes) || !is_array($attributes)) {
            return $attributes;
        }

        // Define priority order (lower = higher priority)
        $priority_order = array();
        foreach ($attributes as $key => $attribute) {
            $slug_lower = strtolower($key);
            
            if (strpos($slug_lower, 'color') !== false || strpos($slug_lower, 'warna') !== false) {
                $priority_order[$key] = 0; // Color first
            } elseif (strpos($slug_lower, 'size') !== false || strpos($slug_lower, 'ukuran') !== false) {
                $priority_order[$key] = 1; // Size second
            } else {
                $priority_order[$key] = 99; // Others last
            }
        }

        // Sort attributes by priority
        uksort($attributes, function($a, $b) use ($priority_order) {
            $pos_a = isset($priority_order[$a]) ? $priority_order[$a] : 99;
            $pos_b = isset($priority_order[$b]) ? $priority_order[$b] : 99;
            return $pos_a - $pos_b;
        });

        return $attributes;
    }

    /**
     * Ensure variation prices hash includes our reordering
     */
    public function variation_prices_hash($hash, $product, $for_display)
    {
        $hash[] = 'wikaz_attribute_order';
        return $hash;
    }

    /**
     * Customize stock availability text to show quantity
     */
    public function custom_stock_availability($availability, $product)
    {
        // Only if managing stock and in stock
        if ($product->managing_stock() && $product->is_in_stock()) {
            $stock = $product->get_stock_quantity();
            if ($stock > 0) {
                $availability['availability'] = sprintf(__('%d available', 'keycreation-wikaz'), $stock);
            }
        }
        return $availability;
    }

    /**
     * Clear default attributes to prevent auto-selection of variations
     */
    public function clear_default_attributes($defaults, $product)
    {
        // Only clear on frontend single product pages
        if (is_product() && !is_admin()) {
            return array();
        }
        return $defaults;
    }

    /**
     * Add body class for transparent header
     */
    public function add_body_class($classes)
    {
        if (get_option('wikaz_header_transparent', '0') === '1' && (is_front_page() || is_home())) {
            $classes[] = 'wikaz-header-transparent';
        }
        return $classes;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets()
    {
        // Load standard frontend styles globally
        wp_enqueue_style(
            'wikaz-frontend-style',
            WIKAZ_PLUGIN_URL . 'public/css/frontend-style.css',
            array(),
            WIKAZ_VERSION
        );

        // Swiper CSS (Only for carousel pages)
        if (is_front_page() || $this->has_shortcode()) {
            wp_enqueue_style(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                array(),
                '11.0.0'
            );

            wp_enqueue_style(
                'wikaz-carousel-style',
                WIKAZ_PLUGIN_URL . 'public/css/carousel-style.css',
                array('swiper'),
                WIKAZ_VERSION
            );

            wp_enqueue_script(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                array(),
                '11.0.0',
                true
            );

            wp_enqueue_script(
                'wikaz-carousel',
                WIKAZ_PLUGIN_URL . 'public/js/carousel.js',
                array('swiper'),
                WIKAZ_VERSION,
                true
            );

            wp_localize_script('wikaz-carousel', 'wikazCarousel', array(
                'autoplay' => get_option('wikaz_carousel_autoplay', '1') === '1',
                'speed' => intval(get_option('wikaz_carousel_speed', '5000'))
            ));
        }
        
        // Enqueue Header Slider Script if needed
        if ($this->has_header_slider_shortcode()) {
             wp_enqueue_style(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                array(),
                '11.0.0'
            );

            wp_enqueue_style(
                'wikaz-carousel-style',
                WIKAZ_PLUGIN_URL . 'public/css/carousel-style.css',
                array('swiper'),
                WIKAZ_VERSION
            );

             wp_enqueue_script(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                array(),
                '11.0.0',
                true
            );

            wp_enqueue_script(
                'wikaz-header-slider',
                WIKAZ_PLUGIN_URL . 'public/js/header-slider.js',
                array('swiper'),
                WIKAZ_VERSION,
                true
            );
        }

        // Product Page Customizations
        if (is_product()) {
            global $product;
            if (!is_a($product, 'WC_Product')) {
                $product = wc_get_product(get_the_ID());
            }


            wp_enqueue_script(
                'wikaz-frontend-script',
                WIKAZ_PLUGIN_URL . 'public/js/frontend-script.js',
                array('jquery'),
                WIKAZ_VERSION,
                true
            );

            $current_stock = '';
            if (is_a($product, 'WC_Product')) {
                if ($product->is_type('variable')) {
                    $total_stock = 0;
                    foreach ($product->get_children() as $child_id) {
                        $variation = wc_get_product($child_id);
                        if ($variation && $variation->managing_stock()) {
                            $total_stock += $variation->get_stock_quantity();
                        }
                    }
                    $current_stock = $total_stock > 0 ? $total_stock : '';
                } elseif ($product->managing_stock()) {
                    $current_stock = $product->get_stock_quantity();
                }
            }

            wp_localize_script('wikaz-frontend-script', 'wikazProductData', array(
                'stockLabel' => __('available', 'keycreation-wikaz'),
                'viewCountSelector' => '.product-info-view',
                'stockSelector' => '.stock, .in-stock',
                'currentStock' => $current_stock
            ));
        }
    }

    /**
     * Check if current page has shortcode
     */
    private function has_shortcode()
    {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wikaz_carousel');
    }

    /**
     * Check if current page has header slider shortcode
     */
    private function has_header_slider_shortcode()
    {
        global $post;
        if (!is_a($post, 'WP_Post')) return false;
        
        // Robust check for Elementor or other page builders
        if (has_shortcode($post->post_content, 'wikaz_header_slider')) {
            return true;
        }
        
        // Manual string check as fallback for nested locations
        if (strpos($post->post_content, '[wikaz_header_slider') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Maybe inject carousel on front page
     */
    public function maybe_inject_carousel()
    {
        if (is_front_page()) {
            echo $this->render_carousel(array());
        }
    }

    /**
     * Get active slides
     */
    private function get_slides()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_carousel_slides';
        return $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1 ORDER BY sort_order ASC");
    }

    /**
     * Render carousel
     */
    public function render_carousel($atts)
    {
        $slides = $this->get_slides();

        if (empty($slides)) {
            return '';
        }

        $header_transparent = get_option('wikaz_header_transparent', '0');
        $wrapper_class = 'wikaz-carousel-wrapper';
        if ($header_transparent === '1') {
            $wrapper_class .= ' has-transparent-header';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">
            <div class="swiper wikaz-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($slides as $slide):
                        $product = $slide->product_id ? wc_get_product($slide->product_id) : null;
                        $post = $slide->post_id ? get_post($slide->post_id) : null;

                        $title = $slide->title ?: ($product ? $product->get_name() : ($post ? $post->post_title : ''));

                        // URL Priority: Button URL -> Product URL -> Post URL -> #
                        if ($slide->button_url) {
                            $url = $slide->button_url;
                        } elseif ($product) {
                            $url = get_permalink($product->get_id());
                        } elseif ($post) {
                            $url = get_permalink($post->ID);
                        } else {
                            $url = '#';
                        }

                        $button_text = $slide->button_text ?: 'Shop Now';
                        $layout = $slide->layout ?: 'full-bg';
                        ?>
                        <div class="swiper-slide wikaz-slide layout-<?php echo esc_attr($layout); ?>">
                            <div class="wikaz-slide-image">
                                <?php if ($slide->background_video):
                                    echo $this->get_video_embed($slide->background_video);
                                else: ?>
                                    <div class="wikaz-slide-bg"
                                        style="background-image: url('<?php echo esc_url($slide->background_image); ?>');"></div>
                                <?php endif; ?>
                                <div class="wikaz-slide-overlay"></div>
                            </div>
                            <div class="wikaz-slide-content">
                                <div class="wikaz-content-inner">
                                    <?php if ($slide->subtitle): ?>
                                        <span class="wikaz-slide-subtitle"><?php echo esc_html($slide->subtitle); ?></span>
                                    <?php endif; ?>
                                    <?php if ($title): ?>
                                        <h2 class="wikaz-slide-title"><?php echo esc_html($title); ?></h2>
                                    <?php endif; ?>
                                    <?php if ($slide->description): ?>
                                        <div class="wikaz-slide-description">
                                            <?php echo wp_kses_post($slide->description); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product): ?>
                                        <div class="wikaz-slide-price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($url && $url !== '#'): ?>
                                        <a href="<?php echo esc_url($url); ?>" class="wikaz-slide-button">
                                            <?php echo esc_html($button_text); ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- Pagination -->
            <div class="wikaz-carousel-pagination"></div>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get header slides for a specific slider
     */
    private function get_header_slides($slider_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wikaz_header_slider_slides';
        // Removed is_active check to show all slides (since existing ones might be 0)
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE slider_id = %d ORDER BY sort_order ASC", $slider_id));
    }

    /**
     * Render header slider
     */
    public function render_header_slider($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'wikaz_header_slider');

        $slider_id = intval($atts['id']);
        if (!$slider_id) return '';

        // Get slider settings
        global $wpdb;
        $slider = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wikaz_header_sliders WHERE id = %d AND is_active = 1", $slider_id));

        if (!$slider) {
            return '<div style="background:#fee; color:#a00; padding:10px; border:1px solid #a00;"><strong>Wikaz Slider Error:</strong> Slider with ID ' . esc_html($slider_id) . ' not found.</div>';
        }

        $slides = $this->get_header_slides($slider_id);
        if (empty($slides)) {
            return '<div style="background:#fee; color:#a00; padding:10px; border:1px solid #a00;"><strong>Wikaz Slider Error:</strong> Slider found, but no slides have been added yet.</div>';
        }

        // Force enqueue assets here to ensure they load even in page builders
        wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
        wp_enqueue_style('wikaz-carousel-style', WIKAZ_PLUGIN_URL . 'public/css/carousel-style.css', array('swiper'), WIKAZ_VERSION);
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
        wp_enqueue_script('wikaz-header-slider', WIKAZ_PLUGIN_URL . 'public/js/header-slider.js', array('swiper'), WIKAZ_VERSION, true);

        $uid = uniqid();
        $wrapper_class = 'wikaz-carousel-wrapper header-slider-wrapper'; 
        
        // Inline style to strict force visibility
        $style = 'width: 100%; height: 100vh; min-height: 500px; display: block; position: relative; overflow: hidden; background: #000;';

        ob_start();
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>" style="<?php echo esc_attr($style); ?>">
            <div class="swiper wikaz-carousel wikaz-header-slider-instance" 
                 data-autoplay="<?php echo esc_attr($slider->autoplay); ?>" 
                 data-speed="<?php echo esc_attr($slider->speed); ?>"
                 data-uid="<?php echo esc_attr($uid); ?>">
                 
                <div class="swiper-wrapper">
                    <?php foreach ($slides as $slide):
                        $product = $slide->product_id ? wc_get_product($slide->product_id) : null;
                        $post = $slide->post_id ? get_post($slide->post_id) : null;

                        $title = $slide->title ?: ($product ? $product->get_name() : ($post ? $post->post_title : ''));

                        // URL Priority: Button URL -> Product URL -> Post URL -> #
                        if ($slide->button_url) {
                            $url = $slide->button_url;
                        } elseif ($product) {
                            $url = get_permalink($product->get_id());
                        } elseif ($post) {
                            $url = get_permalink($post->ID);
                        } else {
                            $url = '#';
                        }

                        $button_text = $slide->button_text ?: 'Shop Now';
                        $layout = $slide->layout ?: 'full-bg';
                        ?>
                        <div class="swiper-slide wikaz-slide layout-<?php echo esc_attr($layout); ?>">
                            <div class="wikaz-slide-image">
                                <?php if ($slide->background_video):
                                    echo $this->get_video_embed($slide->background_video);
                                else: ?>
                                    <div class="wikaz-slide-bg"
                                        style="background-image: url('<?php echo esc_url($slide->background_image); ?>');"></div>
                                <?php endif; ?>
                                <div class="wikaz-slide-overlay"></div>
                            </div>
                            <div class="wikaz-slide-content">
                                <div class="wikaz-content-inner">
                                    <?php if ($slide->subtitle): ?>
                                        <span class="wikaz-slide-subtitle"><?php echo esc_html($slide->subtitle); ?></span>
                                    <?php endif; ?>
                                    <?php if ($title): ?>
                                        <h2 class="wikaz-slide-title"><?php echo esc_html($title); ?></h2>
                                    <?php endif; ?>
                                    <?php if ($slide->description): ?>
                                        <div class="wikaz-slide-description">
                                            <?php echo wp_kses_post($slide->description); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product): ?>
                                        <div class="wikaz-slide-price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($url && $url !== '#'): ?>
                                        <a href="<?php echo esc_url($url); ?>" class="wikaz-slide-button">
                                            <?php echo esc_html($button_text); ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="wikaz-carousel-pagination wikaz-pagination-<?php echo $uid; ?>"></div>

                <!-- Navigation Arrows -->
                <button class="wikaz-slider-nav wikaz-slider-prev wikaz-button-prev-<?php echo $uid; ?>" aria-label="Previous slide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="wikaz-slider-nav wikaz-slider-next wikaz-button-next-<?php echo $uid; ?>" aria-label="Next slide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get video embed code (YouTube, TikTok, or Local)
     */
    private function get_video_embed($url)
    {
        $url = trim($url);

        // Detect if it's a direct video file (mp4, webm, ogg, mov)
        $is_video_file = preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $url);

        if ($is_video_file) {
            $player_url = WIKAZ_PLUGIN_URL . 'public/video-player.php?src=' . urlencode($url);
            return sprintf(
                '<div class="wikaz-slide-video-container">
                    <iframe class="wikaz-video-embed" src="%s" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>',
                esc_url($player_url)
            );
        }

        // YouTube Detection
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = "https://www.youtube.com/embed/{$video_id}?autoplay=1&mute=1&controls=0&loop=1&playlist={$video_id}&showinfo=0&disablekb=1&fs=0&modestbranding=1&rel=0";
            return '<div class="wikaz-slide-video-container"><iframe class="wikaz-video-embed" src="' . esc_url($embed_url) . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>';
        }

        // TikTok Detection
        if (preg_match('/tiktok\.com\/.*\/video\/(\d+)/i', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = "https://www.tiktok.com/embed/v2/{$video_id}";
            return '<div class="wikaz-slide-video-container"><iframe class="wikaz-video-embed" src="' . esc_url($embed_url) . '" frameborder="0" allow="autoplay; encrypted-media"></iframe></div>';
        }

        // Vimeo Detection
        if (preg_match('/vimeo\.com\/(\d+)/i', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = "https://player.vimeo.com/video/{$video_id}?autoplay=1&muted=1&loop=1&background=1";
            return '<div class="wikaz-slide-video-container"><iframe class="wikaz-video-embed" src="' . esc_url($embed_url) . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
        }

        // Generic oEmbed fallback (for other providers like DailyMotion, etc.)
        $embed_code = wp_oembed_get($url, array('width' => 1920, 'height' => 1080));
        if ($embed_code) {
            return '<div class="wikaz-slide-video-container">' . $embed_code . '</div>';
        }

        // Last resort: treat as video file if nothing else matches but it looks like a URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $player_url = WIKAZ_PLUGIN_URL . 'public/video-player.php?src=' . urlencode($url);
            return sprintf(
                '<div class="wikaz-slide-video-container">
                    <iframe class="wikaz-video-embed" src="%s" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>',
                esc_url($player_url)
            );
        }

        return '';
    }
}
