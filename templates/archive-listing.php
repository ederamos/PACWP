<?php
/**
 * Template for displaying listing archive
 */

get_header(); ?>

<div class="pacwp-archive">
    <div class="pacwp-archive-header">
        <h1>
            <?php
            if (is_tax('pacwp_category')) {
                echo 'Category: ' . single_term_title('', false);
            } elseif (is_tax('pacwp_location')) {
                echo 'Location: ' . single_term_title('', false);
            } else {
                echo 'All Classified Listings';
            }
            ?>
        </h1>
        
        <?php if (have_posts()) : ?>
            <p style="color: #666; font-size: 13px; margin: 10px 0;">
                Showing <?php echo $wp_query->post_count; ?> of <?php echo $wp_query->found_posts; ?> listings
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Search Form -->
    <div class="pacwp-search-form">
        <form method="get" action="<?php echo home_url('/'); ?>">
            <input type="hidden" name="post_type" value="pacwp_listing">
            
            <div class="form-row">
                <label for="search_query">Search:</label>
                <input type="text" id="search_query" name="s" value="<?php echo get_search_query(); ?>" placeholder="Enter keywords...">
            </div>
            
            <div class="form-row">
                <label for="search_category">Category:</label>
                <?php
                wp_dropdown_categories(array(
                    'taxonomy' => 'pacwp_category',
                    'name' => 'pacwp_category',
                    'id' => 'search_category',
                    'show_option_all' => 'All Categories',
                    'selected' => get_query_var('pacwp_category'),
                    'hierarchical' => true,
                    'hide_empty' => false
                ));
                ?>
            </div>
            
            <div class="form-row">
                <label for="search_location">Location:</label>
                <?php
                wp_dropdown_categories(array(
                    'taxonomy' => 'pacwp_location',
                    'name' => 'pacwp_location',
                    'id' => 'search_location',
                    'show_option_all' => 'All Locations',
                    'selected' => get_query_var('pacwp_location'),
                    'hierarchical' => false,
                    'hide_empty' => false
                ));
                ?>
            </div>
            
            <div class="form-row">
                <label for="search_order">Sort by:</label>
                <select id="search_order" name="orderby">
                    <option value="date" <?php selected(get_query_var('orderby'), 'date'); ?>>Newest first</option>
                    <option value="title" <?php selected(get_query_var('orderby'), 'title'); ?>>Title A-Z</option>
                    <option value="meta_value_num" <?php selected(get_query_var('orderby'), 'meta_value_num'); ?>>Price (low to high)</option>
                </select>
                <input type="hidden" name="meta_key" value="_pacwp_price">
            </div>
            
            <div class="form-row">
                <input type="submit" value="Search Listings">
                <a href="<?php echo get_post_type_archive_link('pacwp_listing'); ?>" style="margin-left: 10px; color: #00e; text-decoration: none;">Clear filters</a>
            </div>
        </form>
    </div>
    
    <!-- Categories Quick Links -->
    <div class="pacwp-categories">
        <h2>Browse by Category</h2>
        <?php
        $categories = get_terms(array(
            'taxonomy' => 'pacwp_category',
            'hide_empty' => false
        ));
        
        if ($categories && !is_wp_error($categories)) :
        ?>
            <ul>
                <?php foreach ($categories as $category) : ?>
                    <li>
                        <a href="<?php echo get_term_link($category); ?>">
                            <?php echo esc_html($category->name); ?>
                            (<?php echo $category->count; ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <!-- Listings -->
    <?php if (have_posts()) : ?>
        <div class="pacwp-listings">
            <?php while (have_posts()) : the_post(); ?>
                <?php
                $price = get_post_meta(get_the_ID(), '_pacwp_price', true);
                $listing_type = get_post_meta(get_the_ID(), '_pacwp_listing_type', true);
                $categories = get_the_terms(get_the_ID(), 'pacwp_category');
                $locations = get_the_terms(get_the_ID(), 'pacwp_location');
                ?>
                
                <div class="pacwp-listing-item">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    
                    <?php if ($price) : ?>
                        <div class="pacwp-price">$<?php echo esc_html($price); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($categories) : ?>
                        <div class="pacwp-category"><?php echo esc_html($categories[0]->name); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($locations) : ?>
                        <div class="pacwp-location"><?php echo esc_html($locations[0]->name); ?></div>
                    <?php endif; ?>
                    
                    <div class="pacwp-excerpt"><?php the_excerpt(); ?></div>
                    
                    <div class="pacwp-date"><?php echo get_the_date(); ?></div>
                </div>
                
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="pacwp-pagination" style="margin: 30px 0; text-align: center;">
            <?php
            echo paginate_links(array(
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
                'type' => 'plain'
            ));
            ?>
        </div>
        
    <?php else : ?>
        <div class="pacwp-no-listings" style="padding: 40px; text-align: center; color: #666;">
            <h3>No listings found</h3>
            <p>Try adjusting your search criteria or <a href="<?php echo home_url('/submit-listing/'); ?>" style="color: #00e;">post a new listing</a>.</p>
        </div>
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>