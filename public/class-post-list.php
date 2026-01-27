<?php
/**
 * Post List functionality for Keycreation Wikaz
 */

if (!defined('ABSPATH')) {
    exit;
}

class Wikaz_Post_List
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_shortcode('wikaz_post_list', array($this, 'render_shortcode'));
    }

    /**
     * Render the post list shortcode
     */
    public function render_shortcode($atts)
    {
        // Enqueue styles
        wp_enqueue_style('wikaz-post-list', WIKAZ_PLUGIN_URL . 'public/css/post-list.css', array(), WIKAZ_VERSION);

        // Get current page and search term
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $search_query = isset($_GET['wikaz_s']) ? sanitize_text_field($_GET['wikaz_s']) : '';

        // Query arguments
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'paged'          => $paged,
            'post_status'    => 'publish',
            's'              => $search_query,
        );

        $query = new WP_Query($args);

        ob_start();
        ?>
        <div class="wikaz-post-list-container">
            <!-- Search Form -->
            <div class="wikaz-post-search-wrap">
                <form role="search" method="get" class="wikaz-post-search-form" action="">
                    <input type="text" value="<?php echo esc_attr($search_query); ?>" name="wikaz_s" placeholder="Search stories...">
                    <!-- Preserve existing page if needed, but for simple list usually blank action is fine -->
                    <button type="submit">Search</button>
                    <?php 
                    // If on a static page, we might need to pass the page ID to keep the user there
                    if (is_page()) {
                        echo '<input type="hidden" name="page_id" value="' . get_the_ID() . '">';
                    }
                    ?>
                </form>
            </div>

            <?php if ($query->have_posts()) : ?>
                <div class="wikaz-post-list">
                    <?php while ($query->have_posts()) : $query->the_post(); 
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        if (!$thumb_url) {
                            $thumb_url = 'https://via.placeholder.com/150'; // Fallback
                        }
                    ?>
                        <article class="wikaz-post-item">
                            <div class="wikaz-post-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>">
                                </a>
                            </div>
                            <div class="wikaz-post-content">
                                <h3 class="wikaz-post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <div class="wikaz-post-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="wikaz-post-pagination">
                    <?php
                    echo paginate_links(array(
                        'total'     => $query->max_num_pages,
                        'current'   => $paged,
                        'format'    => '?paged=%#%',
                        'add_args'  => array('wikaz_s' => $search_query),
                        'prev_text' => '←',
                        'next_text' => '→',
                        'type'      => 'plain',
                    ));
                    ?>
                </div>
                <?php wp_reset_postdata(); ?>

            <?php else : ?>
                <p><?php _e('No posts found matching your criteria.', 'keycreation-wikaz'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
