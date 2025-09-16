<?php
/**
 * Demo content creator for PACWP Classifieds
 * Run this script from WordPress admin to create sample listings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function pacwp_create_demo_content() {
    
    // Sample listings data
    $sample_listings = array(
        array(
            'title' => '2010 Honda Civic - Excellent Condition',
            'content' => 'Selling my reliable 2010 Honda Civic. 120,000 miles, excellent condition, well maintained. New tires, recent oil change. Clean interior, no smoking. Perfect for daily commuting.',
            'price' => '8500',
            'type' => 'for_sale',
            'category' => 'for-sale',
            'location' => 'San Francisco',
            'email' => 'seller@example.com',
            'phone' => '(555) 123-4567'
        ),
        array(
            'title' => 'Software Developer - Remote Position',
            'content' => 'Looking for an experienced software developer to join our team. Remote work available. Must have 3+ years experience with PHP, WordPress, and MySQL. Competitive salary and benefits.',
            'price' => '75000',
            'type' => 'job',
            'category' => 'jobs',
            'location' => 'Remote',
            'email' => 'hr@techcompany.com',
            'phone' => '(555) 987-6543'
        ),
        array(
            'title' => '2BR Apartment for Rent - Downtown',
            'content' => 'Beautiful 2-bedroom apartment in downtown area. Hardwood floors, updated kitchen, in-unit laundry. Walking distance to public transit. Available immediately.',
            'price' => '2200',
            'type' => 'housing',
            'category' => 'housing',
            'location' => 'New York',
            'email' => 'landlord@example.com',
            'phone' => '(555) 456-7890'
        ),
        array(
            'title' => 'iPhone 13 Pro - Like New',
            'content' => 'iPhone 13 Pro, 256GB, Space Gray. Purchased 6 months ago, barely used. Includes original box, charger, and screen protector already applied. No scratches or damage.',
            'price' => '850',
            'type' => 'for_sale',
            'category' => 'for-sale',
            'location' => 'Los Angeles',
            'email' => 'iphone.seller@example.com',
            'phone' => '(555) 234-5678'
        ),
        array(
            'title' => 'Freelance Graphic Designer Available',
            'content' => 'Professional graphic designer available for freelance work. Specializing in logos, branding, web design, and print materials. 5+ years experience, quick turnaround.',
            'price' => '50',
            'type' => 'service',
            'category' => 'services',
            'location' => 'Chicago',
            'email' => 'designer@example.com',
            'phone' => '(555) 345-6789'
        ),
        array(
            'title' => 'Mountain Bike - Trek 4300',
            'content' => 'Trek 4300 mountain bike in good condition. 26" wheels, front suspension, 21-speed. Great for trails and city riding. Includes helmet and lock.',
            'price' => '300',
            'type' => 'for_sale',
            'category' => 'for-sale',
            'location' => 'Denver',
            'email' => 'bikerider@example.com',
            'phone' => '(555) 567-8901'
        )
    );
    
    $created_count = 0;
    
    foreach ($sample_listings as $listing_data) {
        
        // Create the post
        $post_data = array(
            'post_title'   => $listing_data['title'],
            'post_content' => $listing_data['content'],
            'post_type'    => 'pacwp_listing',
            'post_status'  => 'publish',
            'post_author'  => 1
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            
            // Add meta data
            update_post_meta($post_id, '_pacwp_price', $listing_data['price']);
            update_post_meta($post_id, '_pacwp_contact_email', $listing_data['email']);
            update_post_meta($post_id, '_pacwp_contact_phone', $listing_data['phone']);
            update_post_meta($post_id, '_pacwp_listing_type', $listing_data['type']);
            
            // Assign category
            $category = get_term_by('slug', $listing_data['category'], 'pacwp_category');
            if ($category) {
                wp_set_post_terms($post_id, array($category->term_id), 'pacwp_category');
            }
            
            // Assign location
            $location_term = wp_insert_term($listing_data['location'], 'pacwp_location');
            if (!is_wp_error($location_term)) {
                wp_set_post_terms($post_id, array($location_term['term_id']), 'pacwp_location');
            } else {
                // Location might already exist
                $existing_location = get_term_by('name', $listing_data['location'], 'pacwp_location');
                if ($existing_location) {
                    wp_set_post_terms($post_id, array($existing_location->term_id), 'pacwp_location');
                }
            }
            
            $created_count++;
        }
    }
    
    return $created_count;
}

// Add admin notice with demo creation button
add_action('admin_notices', function() {
    if (isset($_GET['pacwp_create_demo']) && $_GET['pacwp_create_demo'] === '1') {
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $created = pacwp_create_demo_content();
        
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Demo content created!</strong> ' . $created . ' sample listings have been added to your site.</p>';
        echo '</div>';
        
        return;
    }
    
    // Only show on PACWP admin pages
    $screen = get_current_screen();
    if (strpos($screen->id, 'pacwp') === false && $screen->post_type !== 'pacwp_listing') {
        return;
    }
    
    // Check if demo content already exists
    $existing_listings = get_posts(array(
        'post_type' => 'pacwp_listing',
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (empty($existing_listings)) {
        echo '<div class="notice notice-info">';
        echo '<p><strong>Welcome to PACWP Classifieds!</strong> ';
        echo 'Get started by creating some demo content to see how the plugin works. ';
        echo '<a href="' . admin_url('edit.php?post_type=pacwp_listing&pacwp_create_demo=1') . '" class="button button-primary">Create Demo Listings</a>';
        echo '</p>';
        echo '</div>';
    }
});