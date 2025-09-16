<?php
/**
 * Admin functionality for PACWP Classifieds
 */

class PACWP_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('manage_pacwp_listing_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_pacwp_listing_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_filter('manage_edit-pacwp_listing_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'custom_orderby'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=pacwp_listing',
            'PACWP Settings',
            'Settings',
            'manage_options',
            'pacwp-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=pacwp_listing',
            'Statistics',
            'Statistics',
            'manage_options',
            'pacwp-stats',
            array($this, 'stats_page')
        );
    }
    
    public function admin_init() {
        register_setting('pacwp_settings', 'pacwp_options');
        
        add_settings_section(
            'pacwp_general_section',
            'General Settings',
            array($this, 'general_section_callback'),
            'pacwp_settings'
        );
        
        add_settings_field(
            'auto_publish',
            'Auto-publish listings',
            array($this, 'auto_publish_callback'),
            'pacwp_settings',
            'pacwp_general_section'
        );
        
        add_settings_field(
            'require_registration',
            'Require user registration',
            array($this, 'require_registration_callback'),
            'pacwp_settings',
            'pacwp_general_section'
        );
        
        add_settings_field(
            'listings_per_page',
            'Listings per page',
            array($this, 'listings_per_page_callback'),
            'pacwp_settings',
            'pacwp_general_section'
        );
        
        add_settings_field(
            'contact_form_email',
            'Admin notification email',
            array($this, 'contact_form_email_callback'),
            'pacwp_settings',
            'pacwp_general_section'
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'pacwp') !== false) {
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        }
    }
    
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['pacwp_price'] = 'Price';
                $new_columns['pacwp_category'] = 'Category';
                $new_columns['pacwp_location'] = 'Location';
                $new_columns['pacwp_contact'] = 'Contact';
                $new_columns['pacwp_type'] = 'Type';
            }
        }
        
        return $new_columns;
    }
    
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'pacwp_price':
                $price = get_post_meta($post_id, '_pacwp_price', true);
                echo $price ? '$' . esc_html($price) : '—';
                break;
                
            case 'pacwp_category':
                $categories = get_the_terms($post_id, 'pacwp_category');
                if ($categories && !is_wp_error($categories)) {
                    $category_names = array_map(function($cat) { return $cat->name; }, $categories);
                    echo esc_html(implode(', ', $category_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'pacwp_location':
                $locations = get_the_terms($post_id, 'pacwp_location');
                if ($locations && !is_wp_error($locations)) {
                    $location_names = array_map(function($loc) { return $loc->name; }, $locations);
                    echo esc_html(implode(', ', $location_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'pacwp_contact':
                $email = get_post_meta($post_id, '_pacwp_contact_email', true);
                $phone = get_post_meta($post_id, '_pacwp_contact_phone', true);
                
                $contact_info = array();
                if ($email) $contact_info[] = $email;
                if ($phone) $contact_info[] = $phone;
                
                echo $contact_info ? esc_html(implode(' / ', $contact_info)) : '—';
                break;
                
            case 'pacwp_type':
                $type = get_post_meta($post_id, '_pacwp_listing_type', true);
                echo $type ? esc_html(ucfirst(str_replace('_', ' ', $type))) : '—';
                break;
        }
    }
    
    public function sortable_columns($columns) {
        $columns['pacwp_price'] = 'price';
        $columns['pacwp_category'] = 'category';
        $columns['pacwp_location'] = 'location';
        $columns['pacwp_type'] = 'type';
        return $columns;
    }
    
    public function custom_orderby($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        switch ($orderby) {
            case 'price':
                $query->set('meta_key', '_pacwp_price');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'type':
                $query->set('meta_key', '_pacwp_listing_type');
                $query->set('orderby', 'meta_value');
                break;
        }
    }
    
    public function general_section_callback() {
        echo '<p>Configure general settings for PACWP Classifieds.</p>';
    }
    
    public function auto_publish_callback() {
        $options = get_option('pacwp_options');
        $value = isset($options['auto_publish']) ? $options['auto_publish'] : 0;
        echo '<input type="checkbox" id="auto_publish" name="pacwp_options[auto_publish]" value="1"' . checked(1, $value, false) . ' />';
        echo '<label for="auto_publish"> Automatically publish new listings (otherwise they require manual approval)</label>';
    }
    
    public function require_registration_callback() {
        $options = get_option('pacwp_options');
        $value = isset($options['require_registration']) ? $options['require_registration'] : 0;
        echo '<input type="checkbox" id="require_registration" name="pacwp_options[require_registration]" value="1"' . checked(1, $value, false) . ' />';
        echo '<label for="require_registration"> Require user registration to submit listings</label>';
    }
    
    public function listings_per_page_callback() {
        $options = get_option('pacwp_options');
        $value = isset($options['listings_per_page']) ? $options['listings_per_page'] : 20;
        echo '<input type="number" id="listings_per_page" name="pacwp_options[listings_per_page]" value="' . esc_attr($value) . '" min="1" max="100" />';
        echo '<p class="description">Number of listings to display per page (1-100)</p>';
    }
    
    public function contact_form_email_callback() {
        $options = get_option('pacwp_options');
        $value = isset($options['contact_form_email']) ? $options['contact_form_email'] : get_option('admin_email');
        echo '<input type="email" id="contact_form_email" name="pacwp_options[contact_form_email]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">Email address to receive notifications about new listings</p>';
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>PACWP Classifieds Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pacwp_settings');
                do_settings_sections('pacwp_settings');
                submit_button();
                ?>
            </form>
            
            <div style="margin-top: 40px;">
                <h2>Shortcodes</h2>
                <p>Use these shortcodes to display classifieds content on your site:</p>
                
                <div style="background: #f5f5f5; padding: 15px; margin: 10px 0;">
                    <h4>Display Listings</h4>
                    <code>[pacwp_listings]</code> - Show all listings<br>
                    <code>[pacwp_listings category="for-sale" limit="5"]</code> - Show 5 listings from "for-sale" category<br>
                    <code>[pacwp_listings location="new-york" orderby="price" order="ASC"]</code> - Show listings from New York, sorted by price
                </div>
                
                <div style="background: #f5f5f5; padding: 15px; margin: 10px 0;">
                    <h4>Submit Form</h4>
                    <code>[pacwp_submit_form]</code> - Display the listing submission form
                </div>
            </div>
            
            <div style="margin-top: 40px;">
                <h2>Quick Actions</h2>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=pacwp_listing'); ?>" class="button">Manage Listings</a>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=pacwp_category&post_type=pacwp_listing'); ?>" class="button">Manage Categories</a>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=pacwp_location&post_type=pacwp_listing'); ?>" class="button">Manage Locations</a>
                    <a href="<?php echo get_post_type_archive_link('pacwp_listing'); ?>" class="button" target="_blank">View Listings (Frontend)</a>
                </p>
            </div>
        </div>
        <?php
    }
    
    public function stats_page() {
        // Get statistics
        $total_listings = wp_count_posts('pacwp_listing');
        $published = $total_listings->publish;
        $pending = $total_listings->pending;
        $draft = $total_listings->draft;
        
        // Get listings by category
        $categories = get_terms(array(
            'taxonomy' => 'pacwp_category',
            'hide_empty' => false
        ));
        
        // Get recent listings
        $recent_listings = get_posts(array(
            'post_type' => 'pacwp_listing',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="wrap">
            <h1>PACWP Classifieds Statistics</h1>
            
            <div class="dashboard-widgets-wrap">
                <div class="metabox-holder">
                    
                    <!-- Overview Stats -->
                    <div class="postbox">
                        <h2 class="hndle">Overview</h2>
                        <div class="inside">
                            <div style="display: flex; gap: 20px;">
                                <div style="text-align: center; flex: 1;">
                                    <h3 style="font-size: 32px; margin: 0; color: #2271b1;"><?php echo $published; ?></h3>
                                    <p>Published Listings</p>
                                </div>
                                <div style="text-align: center; flex: 1;">
                                    <h3 style="font-size: 32px; margin: 0; color: #d63638;"><?php echo $pending; ?></h3>
                                    <p>Pending Review</p>
                                </div>
                                <div style="text-align: center; flex: 1;">
                                    <h3 style="font-size: 32px; margin: 0; color: #00a32a;"><?php echo $draft; ?></h3>
                                    <p>Drafts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories Stats -->
                    <div class="postbox">
                        <h2 class="hndle">Listings by Category</h2>
                        <div class="inside">
                            <?php if ($categories && !is_wp_error($categories)) : ?>
                                <canvas id="categoryChart" width="400" height="200"></canvas>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const ctx = document.getElementById('categoryChart').getContext('2d');
                                    new Chart(ctx, {
                                        type: 'doughnut',
                                        data: {
                                            labels: [<?php echo implode(',', array_map(function($cat) { return '"' . esc_js($cat->name) . '"'; }, $categories)); ?>],
                                            datasets: [{
                                                data: [<?php echo implode(',', array_map(function($cat) { return $cat->count; }, $categories)); ?>],
                                                backgroundColor: [
                                                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                                                    '#9966FF', '#FF9F40', '#C9CBCF', '#4BC0C0'
                                                ]
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'right'
                                                }
                                            }
                                        }
                                    });
                                });
                                </script>
                            <?php else : ?>
                                <p>No categories found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Listings -->
                    <div class="postbox">
                        <h2 class="hndle">Recent Listings</h2>
                        <div class="inside">
                            <?php if ($recent_listings) : ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_listings as $listing) : 
                                            $categories = get_the_terms($listing->ID, 'pacwp_category');
                                            $price = get_post_meta($listing->ID, '_pacwp_price', true);
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($listing->post_title); ?></strong></td>
                                            <td>
                                                <?php 
                                                echo $categories && !is_wp_error($categories) 
                                                    ? esc_html($categories[0]->name) 
                                                    : '—'; 
                                                ?>
                                            </td>
                                            <td><?php echo $price ? '$' . esc_html($price) : '—'; ?></td>
                                            <td><?php echo get_the_date('', $listing); ?></td>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($listing->ID); ?>">Edit</a> |
                                                <a href="<?php echo get_permalink($listing->ID); ?>" target="_blank">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else : ?>
                                <p>No listings found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize admin functionality
if (is_admin()) {
    new PACWP_Admin();
}