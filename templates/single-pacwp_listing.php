<?php
/**
 * Single listing template
 * Template for displaying individual listings
 */

get_header(); ?>

<div class="pacwp-single-listing">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="entry-meta">
                    <span class="posted-on">
                        <?php _e('Posted on', 'pacwp-classifieds'); ?> <?php echo get_the_date(); ?>
                    </span>
                </div>
            </header>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>

            <div class="listing-meta">
                <h4><?php _e('Listing Details', 'pacwp-classifieds'); ?></h4>
                
                <?php
                $price = get_post_meta(get_the_ID(), '_listing_price', true);
                $contact_email = get_post_meta(get_the_ID(), '_listing_contact_email', true);
                $contact_phone = get_post_meta(get_the_ID(), '_listing_contact_phone', true);
                $address = get_post_meta(get_the_ID(), '_listing_address', true);
                
                if ($price) : ?>
                    <p><strong><?php _e('Price:', 'pacwp-classifieds'); ?></strong> $<?php echo esc_html($price); ?></p>
                <?php endif;
                
                if ($address) : ?>
                    <p><strong><?php _e('Location:', 'pacwp-classifieds'); ?></strong> <?php echo esc_html($address); ?></p>
                <?php endif;
                
                if ($contact_email) : ?>
                    <p><strong><?php _e('Contact Email:', 'pacwp-classifieds'); ?></strong> 
                        <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                    </p>
                <?php endif;
                
                if ($contact_phone) : ?>
                    <p><strong><?php _e('Contact Phone:', 'pacwp-classifieds'); ?></strong> 
                        <a href="tel:<?php echo esc_attr($contact_phone); ?>"><?php echo esc_html($contact_phone); ?></a>
                    </p>
                <?php endif; ?>
                
                <?php
                $categories = get_the_terms(get_the_ID(), 'listing_category');
                if ($categories && !is_wp_error($categories)) : ?>
                    <p><strong><?php _e('Category:', 'pacwp-classifieds'); ?></strong>
                        <?php
                        $category_names = array();
                        foreach ($categories as $category) {
                            $category_names[] = '<a href="' . get_term_link($category) . '">' . esc_html($category->name) . '</a>';
                        }
                        echo implode(', ', $category_names);
                        ?>
                    </p>
                <?php endif;
                
                $locations = get_the_terms(get_the_ID(), 'listing_location');
                if ($locations && !is_wp_error($locations)) : ?>
                    <p><strong><?php _e('Area:', 'pacwp-classifieds'); ?></strong>
                        <?php
                        $location_names = array();
                        foreach ($locations as $location) {
                            $location_names[] = '<a href="' . get_term_link($location) . '">' . esc_html($location->name) . '</a>';
                        }
                        echo implode(', ', $location_names);
                        ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="listing-actions">
                <a href="<?php echo get_post_type_archive_link('pacwp_listing'); ?>" class="back-to-listings">
                    &larr; <?php _e('Back to Listings', 'pacwp-classifieds'); ?>
                </a>
                
                <?php if ($contact_email) : ?>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>?subject=<?php echo urlencode('Inquiry about: ' . get_the_title()); ?>" class="contact-seller">
                        <?php _e('Contact Seller', 'pacwp-classifieds'); ?>
                    </a>
                <?php endif; ?>
            </div>

        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>