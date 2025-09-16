<?php
/**
 * Template for displaying single listing
 */

get_header(); ?>

<div class="pacwp-single-listing">
    <?php while (have_posts()) : the_post(); ?>
        
        <div class="pacwp-listing-header">
            <h1 class="pacwp-listing-title"><?php the_title(); ?></h1>
            
            <div class="pacwp-listing-meta">
                <?php
                $price = get_post_meta(get_the_ID(), '_pacwp_price', true);
                $listing_type = get_post_meta(get_the_ID(), '_pacwp_listing_type', true);
                $categories = get_the_terms(get_the_ID(), 'pacwp_category');
                $locations = get_the_terms(get_the_ID(), 'pacwp_location');
                ?>
                
                <?php if ($price) : ?>
                    <span class="pacwp-price">Price: $<?php echo esc_html($price); ?></span>
                <?php endif; ?>
                
                <?php if ($categories) : ?>
                    <span class="pacwp-category">Category: <?php echo esc_html($categories[0]->name); ?></span>
                <?php endif; ?>
                
                <?php if ($locations) : ?>
                    <span class="pacwp-location">Location: <?php echo esc_html($locations[0]->name); ?></span>
                <?php endif; ?>
                
                <span class="pacwp-date">Posted: <?php echo get_the_date(); ?></span>
            </div>
        </div>
        
        <div class="pacwp-listing-content">
            <?php the_content(); ?>
        </div>
        
        <?php if (has_post_thumbnail()) : ?>
            <div class="pacwp-listing-image">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>
        
        <div class="pacwp-listing-details">
            <h4>Listing Details</h4>
            
            <?php if ($listing_type) : ?>
                <p><strong>Type:</strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $listing_type))); ?></p>
            <?php endif; ?>
            
            <?php if ($categories) : ?>
                <p><strong>Category:</strong> 
                    <?php foreach ($categories as $category) : ?>
                        <a href="<?php echo get_term_link($category); ?>"><?php echo esc_html($category->name); ?></a>
                        <?php if ($category !== end($categories)) echo ', '; ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            
            <?php if ($locations) : ?>
                <p><strong>Location:</strong> 
                    <?php foreach ($locations as $location) : ?>
                        <a href="<?php echo get_term_link($location); ?>"><?php echo esc_html($location->name); ?></a>
                        <?php if ($location !== end($locations)) echo ', '; ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            
            <p><strong>Posted by:</strong> <?php the_author(); ?></p>
            <p><strong>Posted on:</strong> <?php echo get_the_date(); ?> at <?php echo get_the_time(); ?></p>
        </div>
        
        <?php
        $contact_email = get_post_meta(get_the_ID(), '_pacwp_contact_email', true);
        $contact_phone = get_post_meta(get_the_ID(), '_pacwp_contact_phone', true);
        ?>
        
        <?php if ($contact_email || $contact_phone) : ?>
            <div class="pacwp-contact-info">
                <h4>Contact Information</h4>
                
                <?php if ($contact_email) : ?>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></p>
                <?php endif; ?>
                
                <?php if ($contact_phone) : ?>
                    <p><strong>Phone:</strong> <a href="tel:<?php echo esc_attr(preg_replace('/[^\d]/', '', $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
                <?php endif; ?>
                
                <p><em>Do NOT contact with unsolicited services or offers.</em></p>
            </div>
        <?php endif; ?>
        
        <div class="pacwp-listing-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc;">
            <a href="<?php echo get_post_type_archive_link('pacwp_listing'); ?>" style="color: #00e; text-decoration: none;">&larr; Back to all listings</a>
            
            <?php if ($categories) : ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="<?php echo get_term_link($categories[0]); ?>" style="color: #00e; text-decoration: none;">More in <?php echo esc_html($categories[0]->name); ?></a>
            <?php endif; ?>
            
            <?php if (current_user_can('edit_post', get_the_ID())) : ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="<?php echo get_edit_post_link(); ?>" style="color: #00e; text-decoration: none;">Edit this listing</a>
            <?php endif; ?>
        </div>
        
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>