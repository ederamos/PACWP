<?php
/**
 * Archive template for listings
 * Template for displaying all listings and category/location archives
 */

get_header(); ?>

<div class="pacwp-listings-archive">
    <header class="page-header">
        <?php
        if (is_tax('listing_category')) {
            echo '<h1 class="page-title">' . single_term_title('', false) . ' ' . __('Listings', 'pacwp-classifieds') . '</h1>';
        } elseif (is_tax('listing_location')) {
            echo '<h1 class="page-title">' . __('Listings in', 'pacwp-classifieds') . ' ' . single_term_title('', false) . '</h1>';
        } else {
            echo '<h1 class="page-title">' . __('All Listings', 'pacwp-classifieds') . '</h1>';
        }
        
        if (term_description()) {
            echo '<div class="archive-description">' . term_description() . '</div>';
        }
        ?>
    </header>

    <!-- Search and Filter Form -->
    <div class="pacwp-search-form">
        <form id="pacwp-search-form" method="GET">
            <div class="form-row">
                <input type="text" id="pacwp-search-input" name="search" placeholder="<?php _e('Search listings...', 'pacwp-classifieds'); ?>" value="<?php echo esc_attr(get_query_var('search')); ?>" />
                
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
                
                <button type="submit"><?php _e('Search', 'pacwp-classifieds'); ?></button>
            </div>
        </form>
    </div>

    <!-- Categories Navigation -->
    <div class="pacwp-categories">
        <h3><?php _e('Browse by Category', 'pacwp-classifieds'); ?></h3>
        <ul>
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'listing_category',
                'parent' => 0, // Only top-level categories
                'hide_empty' => false,
            ));
            
            foreach ($categories as $category) {
                echo '<li>';
                echo '<a href="' . get_term_link($category) . '">' . esc_html($category->name) . '</a>';
                
                // Get child categories
                $children = get_terms(array(
                    'taxonomy' => 'listing_category',
                    'parent' => $category->term_id,
                    'hide_empty' => false,
                ));
                
                if (!empty($children)) {
                    echo '<ul>';
                    foreach ($children as $child) {
                        echo '<li><a href="' . get_term_link($child) . '">' . esc_html($child->name) . '</a></li>';
                    }
                    echo '</ul>';
                }
                echo '</li>';
            }
            ?>
        </ul>
    </div>

    <!-- Listings -->
    <div class="pacwp-listings">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <div class="pacwp-listing-item">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    
                    <?php
                    $price = get_post_meta(get_the_ID(), '_listing_price', true);
                    if ($price) {
                        echo '<div class="listing-price">$' . esc_html($price) . '</div>';
                    }
                    
                    $address = get_post_meta(get_the_ID(), '_listing_address', true);
                    if ($address) {
                        echo '<div class="listing-address">' . esc_html($address) . '</div>';
                    }
                    ?>
                    
                    <div class="listing-excerpt"><?php the_excerpt(); ?></div>
                    
                    <div class="listing-meta-small">
                        <?php
                        $categories = get_the_terms(get_the_ID(), 'listing_category');
                        if ($categories && !is_wp_error($categories)) {
                            echo '<span class="listing-category">' . esc_html($categories[0]->name) . '</span> • ';
                        }
                        ?>
                        <span class="listing-date"><?php echo get_the_date(); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'prev_text' => __('&laquo; Previous', 'pacwp-classifieds'),
                    'next_text' => __('Next &raquo;', 'pacwp-classifieds'),
                ));
                ?>
            </div>
            
        <?php else : ?>
            <p class="no-listings"><?php _e('No listings found.', 'pacwp-classifieds'); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
/* Additional styles for archive page */
.pacwp-listings-archive .page-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.pacwp-categories h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #333;
}

.pacwp-categories ul {
    margin-left: 20px;
}

.pacwp-categories ul ul {
    margin-left: 20px;
    margin-top: 5px;
}

.listing-meta-small {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
}

.listing-category {
    color: #666;
}

.pagination {
    margin-top: 30px;
    text-align: center;
}

.pagination a,
.pagination span {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 2px;
    text-decoration: none;
    border: 1px solid #ddd;
    color: #0645AD;
}

.pagination .current {
    background-color: #0645AD;
    color: white;
    border-color: #0645AD;
}

.no-listings {
    text-align: center;
    font-style: italic;
    color: #666;
    margin: 40px 0;
}
</style>

<?php get_footer(); ?>