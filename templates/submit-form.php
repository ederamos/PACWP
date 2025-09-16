<?php
/**
 * Template for listing submission form
 */

// Handle form submission
if ($_POST && isset($_POST['pacwp_submit_listing'])) {
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['pacwp_submit_nonce'], 'pacwp_submit_listing')) {
        $error_message = 'Security check failed. Please try again.';
    } else {
        
        // Sanitize and validate input
        $title = sanitize_text_field($_POST['pacwp_title']);
        $description = sanitize_textarea_field($_POST['pacwp_description']);
        $price = sanitize_text_field($_POST['pacwp_price']);
        $contact_email = sanitize_email($_POST['pacwp_contact_email']);
        $contact_phone = sanitize_text_field($_POST['pacwp_contact_phone']);
        $listing_type = sanitize_text_field($_POST['pacwp_listing_type']);
        $category = intval($_POST['pacwp_category']);
        $location = sanitize_text_field($_POST['pacwp_location']);
        
        $errors = array();
        
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($description)) {
            $errors[] = 'Description is required.';
        }
        
        if (empty($contact_email)) {
            $errors[] = 'Contact email is required.';
        } elseif (!is_email($contact_email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($errors)) {
            // Create the post
            $post_data = array(
                'post_title'   => $title,
                'post_content' => $description,
                'post_type'    => 'pacwp_listing',
                'post_status'  => is_user_logged_in() ? 'publish' : 'pending',
                'post_author'  => is_user_logged_in() ? get_current_user_id() : 1
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                // Save meta data
                update_post_meta($post_id, '_pacwp_price', $price);
                update_post_meta($post_id, '_pacwp_contact_email', $contact_email);
                update_post_meta($post_id, '_pacwp_contact_phone', $contact_phone);
                update_post_meta($post_id, '_pacwp_listing_type', $listing_type);
                
                // Assign category
                if ($category) {
                    wp_set_post_terms($post_id, array($category), 'pacwp_category');
                }
                
                // Assign location
                if (!empty($location)) {
                    $location_term = wp_insert_term($location, 'pacwp_location');
                    if (!is_wp_error($location_term)) {
                        wp_set_post_terms($post_id, array($location_term['term_id']), 'pacwp_location');
                    } else {
                        // Location might already exist
                        $existing_location = get_term_by('name', $location, 'pacwp_location');
                        if ($existing_location) {
                            wp_set_post_terms($post_id, array($existing_location->term_id), 'pacwp_location');
                        }
                    }
                }
                
                $success_message = is_user_logged_in() 
                    ? 'Your listing has been published successfully!' 
                    : 'Your listing has been submitted for review and will be published shortly.';
                
                // Reset form data
                $_POST = array();
                
            } else {
                $error_message = 'There was an error creating your listing. Please try again.';
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
}
?>

<div class="pacwp-submit-form">
    <h2>Post a New Listing</h2>
    
    <?php if (isset($success_message)) : ?>
        <div class="pacwp-message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)) : ?>
        <div class="pacwp-message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (!is_user_logged_in()) : ?>
        <div class="pacwp-message" style="background: #fff3cd; border-color: #ffeaa7; color: #856404;">
            <strong>Note:</strong> Your listing will be reviewed before publication. 
            <a href="<?php echo wp_login_url(get_permalink()); ?>" style="color: #00e;">Login</a> to publish immediately.
        </div>
    <?php endif; ?>
    
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('pacwp_submit_listing', 'pacwp_submit_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th><label for="pacwp_title">Title *</label></th>
                <td>
                    <input type="text" id="pacwp_title" name="pacwp_title" 
                           value="<?php echo isset($_POST['pacwp_title']) ? esc_attr($_POST['pacwp_title']) : ''; ?>" 
                           maxlength="100" required>
                    <br><small>Be specific and descriptive (max 100 characters)</small>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_listing_type">Type *</label></th>
                <td>
                    <select id="pacwp_listing_type" name="pacwp_listing_type" required>
                        <option value="">Select type...</option>
                        <option value="for_sale" <?php selected(isset($_POST['pacwp_listing_type']) ? $_POST['pacwp_listing_type'] : '', 'for_sale'); ?>>For Sale</option>
                        <option value="wanted" <?php selected(isset($_POST['pacwp_listing_type']) ? $_POST['pacwp_listing_type'] : '', 'wanted'); ?>>Wanted</option>
                        <option value="job" <?php selected(isset($_POST['pacwp_listing_type']) ? $_POST['pacwp_listing_type'] : '', 'job'); ?>>Job</option>
                        <option value="housing" <?php selected(isset($_POST['pacwp_listing_type']) ? $_POST['pacwp_listing_type'] : '', 'housing'); ?>>Housing</option>
                        <option value="service" <?php selected(isset($_POST['pacwp_listing_type']) ? $_POST['pacwp_listing_type'] : '', 'service'); ?>>Service</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_category">Category *</label></th>
                <td>
                    <?php
                    wp_dropdown_categories(array(
                        'taxonomy' => 'pacwp_category',
                        'name' => 'pacwp_category',
                        'id' => 'pacwp_category',
                        'show_option_none' => 'Select category...',
                        'selected' => isset($_POST['pacwp_category']) ? $_POST['pacwp_category'] : '',
                        'hierarchical' => true,
                        'hide_empty' => false,
                        'required' => true
                    ));
                    ?>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_price">Price ($)</label></th>
                <td>
                    <input type="text" id="pacwp_price" name="pacwp_price" 
                           value="<?php echo isset($_POST['pacwp_price']) ? esc_attr($_POST['pacwp_price']) : ''; ?>" 
                           placeholder="0.00">
                    <br><small>Leave blank if not applicable</small>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_location">Location</label></th>
                <td>
                    <input type="text" id="pacwp_location" name="pacwp_location" 
                           value="<?php echo isset($_POST['pacwp_location']) ? esc_attr($_POST['pacwp_location']) : ''; ?>" 
                           placeholder="City, State">
                    <br><small>Enter your city and state</small>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_description">Description *</label></th>
                <td>
                    <textarea id="pacwp_description" name="pacwp_description" required 
                              placeholder="Describe your item, job, or service in detail..."><?php echo isset($_POST['pacwp_description']) ? esc_textarea($_POST['pacwp_description']) : ''; ?></textarea>
                    <br><small>Provide detailed information about your listing</small>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_contact_email">Contact Email *</label></th>
                <td>
                    <input type="email" id="pacwp_contact_email" name="pacwp_contact_email" 
                           value="<?php echo isset($_POST['pacwp_contact_email']) ? esc_attr($_POST['pacwp_contact_email']) : (is_user_logged_in() ? wp_get_current_user()->user_email : ''); ?>" 
                           required>
                    <br><small>This will be displayed publicly</small>
                </td>
            </tr>
            
            <tr>
                <th><label for="pacwp_contact_phone">Contact Phone</label></th>
                <td>
                    <input type="text" id="pacwp_contact_phone" name="pacwp_contact_phone" 
                           value="<?php echo isset($_POST['pacwp_contact_phone']) ? esc_attr($_POST['pacwp_contact_phone']) : ''; ?>" 
                           placeholder="(555) 123-4567">
                    <br><small>Optional - will be displayed publicly if provided</small>
                </td>
            </tr>
        </table>
        
        <div class="submit-button">
            <input type="submit" name="pacwp_submit_listing" value="<?php echo is_user_logged_in() ? 'Publish Listing' : 'Submit for Review'; ?>">
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border: 1px solid #ddd; font-size: 12px; color: #666;">
            <strong>Posting Rules:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>No spam, scams, or fraudulent listings</li>
                <li>Be honest and accurate in your descriptions</li>
                <li>Include contact information</li>
                <li>One listing per post</li>
                <li>Illegal items/services are prohibited</li>
            </ul>
            <p style="margin: 5px 0;">By submitting this form, you agree to our terms of service and posting guidelines.</p>
        </div>
    </form>
</div>