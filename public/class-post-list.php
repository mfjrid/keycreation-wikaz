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
        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        $search_query = isset($_GET['wikaz_s']) ? sanitize_text_field($_GET['wikaz_s']) : '';

        // Query arguments
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'paged'          => $paged,
            'post_status'    => 'publish',
        );

        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }

        $query = new WP_Query($args);

        ob_start();
        ?>
        <?php
        // Get the base URL without query strings for redirection
        $base_url = get_permalink();
        ?>
        <div class="wikaz-post-list-container">
            <!-- Search Bar (Using div instead of form to avoid hijacking) -->
            <!-- Uncommment ini kalau mau ada search -->
            <!-- <div class="wikaz-post-search-wrap">
                <div class="wikaz-post-search-form">
                    <input type="text" id="wikazSearchInput" value="<?php echo esc_attr($search_query); ?>" placeholder="Search stories..." onkeydown="if(event.key === 'Enter') document.getElementById('wikazSearchBtn').click()">
                    <button type="button" id="wikazSearchBtn">Search</button>
                </div>
            </div> -->

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchBtn = document.getElementById('wikazSearchBtn');
                const searchInput = document.getElementById('wikazSearchInput');
                
                if (searchBtn && searchInput) {
                    searchBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const keyword = searchInput.value.trim();
                        let targetUrl = '<?php echo esc_url($base_url); ?>';
                        
                        // Add or update the search parameter
                        if (targetUrl.includes('?')) {
                            // If URL has existing params (like page_id), we need to handle them
                            const urlObj = new URL(targetUrl);
                            urlObj.searchParams.set('wikaz_s', keyword);
                            // Always reset page to 1 on new search
                            urlObj.searchParams.delete('paged');
                            urlObj.searchParams.delete('page');
                            targetUrl = urlObj.toString();
                        } else {
                            targetUrl += '?wikaz_s=' + encodeURIComponent(keyword);
                        }
                        
                        console.log('Wikaz Search: Redirecting to...', targetUrl);
                        window.location.href = targetUrl;
                    });
                }
            });
            </script>

            <?php if ($query->have_posts()) : ?>
                <div class="wikaz-post-list">
                    <?php while ($query->have_posts()) : $query->the_post(); 
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        if (!$thumb_url) {
                            $thumb_url = 'https://placehold.jp/150x150.png'; // Fallback
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
                                <div class="wikaz-post-meta">
                                    <span class="wikaz-post-author">
                                        <span class="meta-icon">👤</span> <?php echo get_the_author(); ?>
                                    </span>
                                    <span class="wikaz-post-date">
                                        <span class="meta-icon">📅</span> <?php echo get_the_date(); ?>
                                    </span>
                                    <span class="wikaz-post-comments">
                                        <span class="meta-icon">💬</span> <?php echo get_comments_number(); ?> <?php _e('Comments', 'keycreation-wikaz'); ?>
                                    </span>
                                </div>
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
