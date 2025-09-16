<?php
/**
 * Plugin Name: PACWP Classifieds
 * Plugin URI: https://github.com/ederamos/PACWP
 * Description: A Craigslist-like classified ads system for WordPress
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
        $this->create_post_type();
        $this->create_taxonomies();
        $this->add_meta_boxes();
        add_action('save_post', array($this, 'save_listing_meta'));
        add_filter('template_include', array($this, 'include_template'));
        add_shortcode('pacwp_listings', array($this, 'display_listings_shortcode'));
        add_shortcode('pacwp_submit_form', array($this, 'display_submit_form_shortcode'));
    }
    
    public function create_post_type() {
        $labels = array(
            'name'               => 'Classified Listings',
            'singular_name'      => 'Classified Listing',
            'menu_name'          => 'Classifieds',
            'add_new'            => 'Add New Listing',
            'add_new_item'       => 'Add New Classified Listing',
            'edit_item'          => 'Edit Listing',
            'new_item'           => 'New Listing',
            'view_item'          => 'View Listing',
            'search_items'       => 'Search Listings',
            'not_found'          => 'No listings found',
            'not_found_in_trash' => 'No listings found in trash'
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'listings'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-list-view',
            'supports'            => array('title', 'editor', 'thumbnail', 'author'),
            'show_in_rest'        => true
        );
        
        register_post_type('pacwp_listing', $args);
    }
    
    public function create_taxonomies() {
        // Categories taxonomy
        $category_labels = array(
            'name'              => 'Listing Categories',
            'singular_name'     => 'Listing Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories'
        );
        
        register_taxonomy('pacwp_category', 'pacwp_listing', array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'listing-category'),
            'show_in_rest'      => true
        ));
        
        // Location taxonomy
        $location_labels = array(
            'name'              => 'Locations',
            'singular_name'     => 'Location',
            'search_items'      => 'Search Locations',
            'all_items'         => 'All Locations',
            'edit_item'         => 'Edit Location',
            'update_item'       => 'Update Location',
            'add_new_item'      => 'Add New Location',
            'new_item_name'     => 'New Location Name',
            'menu_name'         => 'Locations'
        );
        
        register_taxonomy('pacwp_location', 'pacwp_listing', array(
            'hierarchical'      => false,
            'labels'            => $location_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'location'),
            'show_in_rest'      => true
        ));
    }
    
    public function add_meta_boxes() {
        add_action('add_meta_boxes', array($this, 'add_listing_meta_boxes'));
    }
    
    public function add_listing_meta_boxes() {
        add_meta_box(
            'pacwp_listing_details',
            'Listing Details',
            array($this, 'listing_details_callback'),
            'pacwp_listing',
            'normal',
            'default'
        );
    }
    
    public function listing_details_callback($post) {
        wp_nonce_field('pacwp_listing_meta_box', 'pacwp_listing_meta_box_nonce');
        
        $price = get_post_meta($post->ID, '_pacwp_price', true);
        $contact_email = get_post_meta($post->ID, '_pacwp_contact_email', true);
        $contact_phone = get_post_meta($post->ID, '_pacwp_contact_phone', true);
        $listing_type = get_post_meta($post->ID, '_pacwp_listing_type', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="pacwp_price">Price ($)</label></th>';
        echo '<td><input type="text" id="pacwp_price" name="pacwp_price" value="' . esc_attr($price) . '" /></td></tr>';
        
        echo '<tr><th><label for="pacwp_contact_email">Contact Email</label></th>';
        echo '<td><input type="email" id="pacwp_contact_email" name="pacwp_contact_email" value="' . esc_attr($contact_email) . '" /></td></tr>';
        
        echo '<tr><th><label for="pacwp_contact_phone">Contact Phone</label></th>';
        echo '<td><input type="text" id="pacwp_contact_phone" name="pacwp_contact_phone" value="' . esc_attr($contact_phone) . '" /></td></tr>';
        
        echo '<tr><th><label for="pacwp_listing_type">Listing Type</label></th>';
        echo '<td><select id="pacwp_listing_type" name="pacwp_listing_type">';
        echo '<option value="for_sale"' . selected($listing_type, 'for_sale', false) . '>For Sale</option>';
        echo '<option value="wanted"' . selected($listing_type, 'wanted', false) . '>Wanted</option>';
        echo '<option value="job"' . selected($listing_type, 'job', false) . '>Job</option>';
        echo '<option value="housing"' . selected($listing_type, 'housing', false) . '>Housing</option>';
        echo '<option value="service"' . selected($listing_type, 'service', false) . '>Service</option>';
        echo '</select></td></tr>';
        echo '</table>';
    }
    
    public function save_listing_meta($post_id) {
        if (!isset($_POST['pacwp_listing_meta_box_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['pacwp_listing_meta_box_nonce'], 'pacwp_listing_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['pacwp_price'])) {
            update_post_meta($post_id, '_pacwp_price', sanitize_text_field($_POST['pacwp_price']));
        }
        
        if (isset($_POST['pacwp_contact_email'])) {
            update_post_meta($post_id, '_pacwp_contact_email', sanitize_email($_POST['pacwp_contact_email']));
        }
        
        if (isset($_POST['pacwp_contact_phone'])) {
            update_post_meta($post_id, '_pacwp_contact_phone', sanitize_text_field($_POST['pacwp_contact_phone']));
        }
        
        if (isset($_POST['pacwp_listing_type'])) {
            update_post_meta($post_id, '_pacwp_listing_type', sanitize_text_field($_POST['pacwp_listing_type']));
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('pacwp-style', PACWP_PLUGIN_URL . 'assets/css/pacwp-style.css', array(), PACWP_VERSION);
        wp_enqueue_script('pacwp-script', PACWP_PLUGIN_URL . 'assets/js/pacwp-script.js', array('jquery'), PACWP_VERSION, true);
    }
    
    public function include_template($template) {
        if (is_singular('pacwp_listing')) {
            $plugin_template = PACWP_PLUGIN_PATH . 'templates/single-listing.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        if (is_post_type_archive('pacwp_listing')) {
            $plugin_template = PACWP_PLUGIN_PATH . 'templates/archive-listing.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    public function display_listings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'location' => '',
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);
        
        $args = array(
            'post_type' => 'pacwp_listing',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'post_status' => 'publish'
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pacwp_category',
                'field' => 'slug',
                'terms' => $atts['category']
            );
        }
        
        if (!empty($atts['location'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'pacwp_location',
                'field' => 'slug',
                'terms' => $atts['location']
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
        } else {
            echo '<p>No listings found.</p>';
        }
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    public function display_submit_form_shortcode($atts) {
        ob_start();
        include PACWP_PLUGIN_PATH . 'templates/submit-form.php';
        return ob_get_clean();
    }
    
    private function display_listing_item() {
        $price = get_post_meta(get_the_ID(), '_pacwp_price', true);
        $listing_type = get_post_meta(get_the_ID(), '_pacwp_listing_type', true);
        $categories = get_the_terms(get_the_ID(), 'pacwp_category');
        $locations = get_the_terms(get_the_ID(), 'pacwp_location');
        
        echo '<div class="pacwp-listing-item">';
        echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        
        if ($price) {
            echo '<div class="pacwp-price">$' . esc_html($price) . '</div>';
        }
        
        if ($categories) {
            echo '<div class="pacwp-category">' . esc_html($categories[0]->name) . '</div>';
        }
        
        if ($locations) {
            echo '<div class="pacwp-location">' . esc_html($locations[0]->name) . '</div>';
        }
        
        echo '<div class="pacwp-excerpt">' . get_the_excerpt() . '</div>';
        echo '<div class="pacwp-date">' . get_the_date() . '</div>';
        echo '</div>';
    }
    
    public function activate() {
        $this->create_post_type();
        $this->create_taxonomies();
        flush_rewrite_rules();
        
        // Create default categories
        $this->create_default_categories();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_default_categories() {
        $categories = array(
            'for-sale' => 'For Sale',
            'jobs' => 'Jobs',
            'housing' => 'Housing',
            'services' => 'Services',
            'community' => 'Community',
            'personals' => 'Personals'
        );
        
        foreach ($categories as $slug => $name) {
            if (!term_exists($slug, 'pacwp_category')) {
                wp_insert_term($name, 'pacwp_category', array('slug' => $slug));
            }
        }
    }
}

// Initialize the plugin
new PACWP_Classifieds();