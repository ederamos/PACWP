<?php
/**
 * Plugin Name: PACWP Classifieds
 * Plugin URI: https://github.com/ederamos/PACWP
 * Description: A Craigslist-style classified ads plugin for WordPress
 * Version: 1.0.0
 * Author: PACWP Team
 * License: GPL v2 or later
 * Text Domain: pacwp-classifieds
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PACWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PACWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PACWP_VERSION', '1.0.0');

// Main plugin class
class PACWP_Classifieds {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        $this->create_post_types();
        $this->create_taxonomies();
        $this->add_meta_boxes();
        add_action('save_post', array($this, 'save_listing_meta'));
        add_shortcode('pacwp_listings', array($this, 'display_listings_shortcode'));
        add_shortcode('pacwp_submit_form', array($this, 'submit_form_shortcode'));
        add_shortcode('pacwp_search_form', array($this, 'search_form_shortcode'));
        add_action('wp_ajax_submit_listing', array($this, 'handle_listing_submission'));
        add_action('wp_ajax_nopriv_submit_listing', array($this, 'handle_listing_submission'));
        add_action('wp_ajax_search_listings', array($this, 'handle_search_listings'));
        add_action('wp_ajax_nopriv_search_listings', array($this, 'handle_search_listings'));
        add_filter('template_include', array($this, 'load_custom_templates'));
        add_action('pre_get_posts', array($this, 'modify_archive_query'));
    }
    
    public function create_post_types() {
        register_post_type('pacwp_listing', array(
            'labels' => array(
                'name' => __('Listings', 'pacwp-classifieds'),
                'singular_name' => __('Listing', 'pacwp-classifieds'),
                'add_new' => __('Add New Listing', 'pacwp-classifieds'),
                'add_new_item' => __('Add New Listing', 'pacwp-classifieds'),
                'edit_item' => __('Edit Listing', 'pacwp-classifieds'),
                'new_item' => __('New Listing', 'pacwp-classifieds'),
                'view_item' => __('View Listing', 'pacwp-classifieds'),
                'search_items' => __('Search Listings', 'pacwp-classifieds'),
                'not_found' => __('No listings found', 'pacwp-classifieds'),
                'not_found_in_trash' => __('No listings found in trash', 'pacwp-classifieds'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'listings'),
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'menu_icon' => 'dashicons-list-view',
            'show_in_rest' => true,
        ));
    }
    
    public function create_taxonomies() {
        // Category taxonomy
        register_taxonomy('listing_category', 'pacwp_listing', array(
            'labels' => array(
                'name' => __('Categories', 'pacwp-classifieds'),
                'singular_name' => __('Category', 'pacwp-classifieds'),
            ),
            'hierarchical' => true,
            'public' => true,
            'rewrite' => array('slug' => 'listing-category'),
            'show_in_rest' => true,
        ));
        
        // Location taxonomy
        register_taxonomy('listing_location', 'pacwp_listing', array(
            'labels' => array(
                'name' => __('Locations', 'pacwp-classifieds'),
                'singular_name' => __('Location', 'pacwp-classifieds'),
            ),
            'hierarchical' => true,
            'public' => true,
            'rewrite' => array('slug' => 'location'),
            'show_in_rest' => true,
        ));
    }
    
    public function add_meta_boxes() {
        add_action('add_meta_boxes', function() {
            add_meta_box(
                'listing_details',
                __('Listing Details', 'pacwp-classifieds'),
                array($this, 'listing_details_meta_box'),
                'pacwp_listing',
                'normal',
                'high'
            );
        });
    }
    
    public function listing_details_meta_box($post) {
        wp_nonce_field('save_listing_meta', 'listing_meta_nonce');
        
        $price = get_post_meta($post->ID, '_listing_price', true);
        $contact_email = get_post_meta($post->ID, '_listing_contact_email', true);
        $contact_phone = get_post_meta($post->ID, '_listing_contact_phone', true);
        $address = get_post_meta($post->ID, '_listing_address', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="listing_price">' . __('Price', 'pacwp-classifieds') . '</label></th>';
        echo '<td><input type="text" id="listing_price" name="listing_price" value="' . esc_attr($price) . '" /></td></tr>';
        
        echo '<tr><th><label for="listing_contact_email">' . __('Contact Email', 'pacwp-classifieds') . '</label></th>';
        echo '<td><input type="email" id="listing_contact_email" name="listing_contact_email" value="' . esc_attr($contact_email) . '" /></td></tr>';
        
        echo '<tr><th><label for="listing_contact_phone">' . __('Contact Phone', 'pacwp-classifieds') . '</label></th>';
        echo '<td><input type="text" id="listing_contact_phone" name="listing_contact_phone" value="' . esc_attr($contact_phone) . '" /></td></tr>';
        
        echo '<tr><th><label for="listing_address">' . __('Address', 'pacwp-classifieds') . '</label></th>';
        echo '<td><textarea id="listing_address" name="listing_address" rows="3" cols="50">' . esc_textarea($address) . '</textarea></td></tr>';
        echo '</table>';
    }
    
    public function save_listing_meta($post_id) {
        if (!isset($_POST['listing_meta_nonce']) || !wp_verify_nonce($_POST['listing_meta_nonce'], 'save_listing_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = array('listing_price', 'listing_contact_email', 'listing_contact_phone', 'listing_address');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('pacwp-style', PACWP_PLUGIN_URL . 'assets/css/style.css', array(), PACWP_VERSION);
        wp_enqueue_script('pacwp-script', PACWP_PLUGIN_URL . 'assets/js/script.js', array('jquery'), PACWP_VERSION, true);
        wp_localize_script('pacwp-script', 'pacwp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pacwp_nonce')
        ));
    }
    
    public function display_listings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'location' => '',
            'limit' => 10,
        ), $atts);
        
        $args = array(
            'post_type' => 'pacwp_listing',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'listing_category',
                'field' => 'slug',
                'terms' => $atts['category'],
            );
        }
        
        if (!empty($atts['location'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'listing_location',
                'field' => 'slug',
                'terms' => $atts['location'],
            );
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="pacwp-listings">';
            while ($query->have_posts()) {
                $query->the_post();
                $this->display_listing_item();
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No listings found.', 'pacwp-classifieds') . '</p>';
        }
        
        return ob_get_clean();
    }
    
    private function display_listing_item() {
        $price = get_post_meta(get_the_ID(), '_listing_price', true);
        $address = get_post_meta(get_the_ID(), '_listing_address', true);
        
        echo '<div class="pacwp-listing-item">';
        echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        if ($price) {
            echo '<div class="listing-price">$' . esc_html($price) . '</div>';
        }
        if ($address) {
            echo '<div class="listing-address">' . esc_html($address) . '</div>';
        }
        echo '<div class="listing-excerpt">' . get_the_excerpt() . '</div>';
        echo '<div class="listing-date">' . get_the_date() . '</div>';
        echo '</div>';
    }
    
    public function submit_form_shortcode($atts) {
        ob_start();
        ?>
        <form id="pacwp-submit-form" class="pacwp-submit-form">
            <?php wp_nonce_field('pacwp_submit_listing', 'pacwp_nonce'); ?>
            
            <div class="form-group">
                <label for="listing_title"><?php _e('Title', 'pacwp-classifieds'); ?></label>
                <input type="text" id="listing_title" name="listing_title" required />
            </div>
            
            <div class="form-group">
                <label for="listing_description"><?php _e('Description', 'pacwp-classifieds'); ?></label>
                <textarea id="listing_description" name="listing_description" rows="6" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="listing_category"><?php _e('Category', 'pacwp-classifieds'); ?></label>
                <?php
                wp_dropdown_categories(array(
                    'taxonomy' => 'listing_category',
                    'name' => 'listing_category',
                    'id' => 'listing_category',
                    'show_option_none' => __('Select Category', 'pacwp-classifieds'),
                ));
                ?>
            </div>
            
            <div class="form-group">
                <label for="listing_location"><?php _e('Location', 'pacwp-classifieds'); ?></label>
                <?php
                wp_dropdown_categories(array(
                    'taxonomy' => 'listing_location',
                    'name' => 'listing_location',
                    'id' => 'listing_location',
                    'show_option_none' => __('Select Location', 'pacwp-classifieds'),
                ));
                ?>
            </div>
            
            <div class="form-group">
                <label for="listing_price"><?php _e('Price', 'pacwp-classifieds'); ?></label>
                <input type="text" id="listing_price" name="listing_price" />
            </div>
            
            <div class="form-group">
                <label for="listing_contact_email"><?php _e('Contact Email', 'pacwp-classifieds'); ?></label>
                <input type="email" id="listing_contact_email" name="listing_contact_email" required />
            </div>
            
            <div class="form-group">
                <label for="listing_contact_phone"><?php _e('Contact Phone', 'pacwp-classifieds'); ?></label>
                <input type="text" id="listing_contact_phone" name="listing_contact_phone" />
            </div>
            
            <div class="form-group">
                <label for="listing_address"><?php _e('Address', 'pacwp-classifieds'); ?></label>
                <textarea id="listing_address" name="listing_address" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <input type="submit" value="<?php _e('Submit Listing', 'pacwp-classifieds'); ?>" />
            </div>
            
            <div id="pacwp-form-message"></div>
        </form>
        <?php
        return ob_get_clean();
    }
    
    public function handle_listing_submission() {
        check_ajax_referer('pacwp_nonce', 'pacwp_nonce');
        
        $title = sanitize_text_field($_POST['listing_title']);
        $description = sanitize_textarea_field($_POST['listing_description']);
        $category = intval($_POST['listing_category']);
        $location = intval($_POST['listing_location']);
        $price = sanitize_text_field($_POST['listing_price']);
        $contact_email = sanitize_email($_POST['listing_contact_email']);
        $contact_phone = sanitize_text_field($_POST['listing_contact_phone']);
        $address = sanitize_textarea_field($_POST['listing_address']);
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_type' => 'pacwp_listing',
            'post_status' => 'pending', // Require admin approval
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // Set category and location
            if ($category) {
                wp_set_post_terms($post_id, array($category), 'listing_category');
            }
            if ($location) {
                wp_set_post_terms($post_id, array($location), 'listing_location');
            }
            
            // Save meta fields
            update_post_meta($post_id, '_listing_price', $price);
            update_post_meta($post_id, '_listing_contact_email', $contact_email);
            update_post_meta($post_id, '_listing_contact_phone', $contact_phone);
            update_post_meta($post_id, '_listing_address', $address);
            
            wp_send_json_success(__('Listing submitted successfully and is pending approval.', 'pacwp-classifieds'));
        } else {
            wp_send_json_error(__('Error submitting listing. Please try again.', 'pacwp-classifieds'));
        }
    }
    
    public function search_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_categories' => true,
            'show_locations' => true,
        ), $atts);
        
        ob_start();
        ?>
        <form id="pacwp-search-form" class="pacwp-search-form" method="GET">
            <div class="form-row">
                <input type="text" id="pacwp-search-input" name="search" 
                       placeholder="<?php _e('Search listings...', 'pacwp-classifieds'); ?>" 
                       value="<?php echo esc_attr(get_query_var('search')); ?>" />
                
                <?php if ($atts['show_categories']) : ?>
                <select id="pacwp-category-filter" name="category">
                    <option value=""><?php _e('All Categories', 'pacwp-classifieds'); ?></option>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'listing_category',
                        'hide_empty' => false,
                    ));
                    foreach ($categories as $category) {
                        $selected = (get_query_var('category') == $category->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
                <?php endif; ?>
                
                <?php if ($atts['show_locations']) : ?>
                <select id="pacwp-location-filter" name="location">
                    <option value=""><?php _e('All Locations', 'pacwp-classifieds'); ?></option>
                    <?php
                    $locations = get_terms(array(
                        'taxonomy' => 'listing_location',
                        'hide_empty' => false,
                    ));
                    foreach ($locations as $location) {
                        $selected = (get_query_var('location') == $location->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($location->slug) . '" ' . $selected . '>' . esc_html($location->name) . '</option>';
                    }
                    ?>
                </select>
                <?php endif; ?>
                
                <button type="submit"><?php _e('Search', 'pacwp-classifieds'); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
    
    public function handle_search_listings() {
        check_ajax_referer('pacwp_nonce', 'nonce');
        
        $search = sanitize_text_field($_POST['search']);
        $category = sanitize_text_field($_POST['category']);
        $location = sanitize_text_field($_POST['location']);
        
        $args = array(
            'post_type' => 'pacwp_listing',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $tax_query = array();
        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_category',
                'field' => 'slug',
                'terms' => $category,
            );
        }
        
        if (!empty($location)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_location',
                'field' => 'slug',
                'terms' => $location,
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->display_listing_item();
            }
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No listings found.', 'pacwp-classifieds') . '</p>';
        }
        
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
    
    public function load_custom_templates($template) {
        global $post;
        
        // Single listing template
        if (is_singular('pacwp_listing')) {
            $custom_template = PACWP_PLUGIN_PATH . 'templates/single-pacwp_listing.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        // Archive template
        if (is_post_type_archive('pacwp_listing') || is_tax('listing_category') || is_tax('listing_location')) {
            $custom_template = PACWP_PLUGIN_PATH . 'templates/archive-pacwp_listing.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    public function modify_archive_query($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_post_type_archive('pacwp_listing') || is_tax('listing_category') || is_tax('listing_location')) {
                $query->set('posts_per_page', 20);
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                
                // Handle search parameter
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $query->set('s', sanitize_text_field($_GET['search']));
                }
                
                // Handle category filter
                if (isset($_GET['category']) && !empty($_GET['category'])) {
                    $tax_query = $query->get('tax_query') ?: array();
                    $tax_query[] = array(
                        'taxonomy' => 'listing_category',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['category']),
                    );
                    $query->set('tax_query', $tax_query);
                }
                
                // Handle location filter
                if (isset($_GET['location']) && !empty($_GET['location'])) {
                    $tax_query = $query->get('tax_query') ?: array();
                    $tax_query[] = array(
                        'taxonomy' => 'listing_location',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['location']),
                    );
                    $query->set('tax_query', $tax_query);
                }
            }
        }
    }
    
    public function activate() {
        $this->create_post_types();
        $this->create_taxonomies();
        flush_rewrite_rules();
        
        // Create default categories
        $default_categories = array(
            'For Sale' => array('cars-trucks', 'electronics', 'furniture', 'general'),
            'Housing' => array('apartments-housing', 'rooms-shared', 'housing-wanted'),
            'Jobs' => array('accounting-finance', 'admin-office', 'art-media-design', 'general-labor'),
            'Services' => array('automotive', 'beauty', 'computer', 'household'),
            'Community' => array('activities', 'artists', 'childcare', 'general'),
        );
        
        foreach ($default_categories as $parent => $children) {
            $parent_term = wp_insert_term($parent, 'listing_category');
            if (!is_wp_error($parent_term)) {
                foreach ($children as $child) {
                    wp_insert_term(ucwords(str_replace('-', ' ', $child)), 'listing_category', array(
                        'parent' => $parent_term['term_id'],
                        'slug' => $child,
                    ));
                }
            }
        }
        
        // Create default locations
        $default_locations = array('New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix');
        foreach ($default_locations as $location) {
            wp_insert_term($location, 'listing_location');
        }
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new PACWP_Classifieds();