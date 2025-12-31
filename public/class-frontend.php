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

        // Auto-inject carousel based on position setting
        $position = get_option('wikaz_carousel_position', 'before_content');
        if ($position === 'before_content') {
            add_action('wp_body_open', array($this, 'maybe_inject_carousel'), 5);
        }
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
        // Only load on front page or if shortcode is used
        if (!is_front_page() && !$this->has_shortcode()) {
            return;
        }

        // Swiper CSS
        wp_enqueue_style(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            array(),
            '11.0.0'
        );

        // Plugin CSS
        wp_enqueue_style(
            'wikaz-carousel-style',
            WIKAZ_PLUGIN_URL . 'public/css/carousel-style.css',
            array('swiper'),
            WIKAZ_VERSION
        );

        // Swiper JS
        wp_enqueue_script(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            array(),
            '11.0.0',
            true
        );

        // Plugin JS
        wp_enqueue_script(
            'wikaz-carousel',
            WIKAZ_PLUGIN_URL . 'public/js/carousel.js',
            array('swiper'),
            WIKAZ_VERSION,
            true
        );

        // Pass settings to JS
        wp_localize_script('wikaz-carousel', 'wikazCarousel', array(
            'autoplay' => get_option('wikaz_carousel_autoplay', '1') === '1',
            'speed' => intval(get_option('wikaz_carousel_speed', '5000'))
        ));
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
                        $title = $slide->title ?: ($product ? $product->get_name() : '');
                        $url = $slide->button_url ?: ($product ? get_permalink($product->get_id()) : '#');
                        $button_text = $slide->button_text ?: 'Shop Now';
                        $layout = $slide->layout ?: 'full-bg';
                        ?>
                        <div class="swiper-slide wikaz-slide layout-<?php echo esc_attr($layout); ?>">
                            <div class="wikaz-slide-image">
                                <div class="wikaz-slide-bg"
                                    style="background-image: url('<?php echo esc_url($slide->background_image); ?>');"></div>
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

                <!-- Navigation -->
                <div class="wikaz-carousel-nav">
                    <button class="wikaz-nav-prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="wikaz-nav-next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Pagination -->
                <div class="wikaz-carousel-pagination"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
