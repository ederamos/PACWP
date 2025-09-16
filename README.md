# PACWP Classifieds

A Craigslist-style classified ads plugin for WordPress that allows users to post and browse listings similar to Craigslist.

## Features

- **Custom Post Type**: Classified listings with custom fields
- **Categories & Locations**: Organize listings by category and location
- **Frontend Submission**: Users can submit listings from the frontend
- **Search & Filter**: Search listings and filter by category/location
- **Responsive Design**: Mobile-friendly Craigslist-style interface
- **Admin Approval**: Listings require admin approval before going live
- **Contact Integration**: Built-in contact information and email links

## Installation

1. Upload the plugin files to the `/wp-content/plugins/pacwp-classifieds/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create default categories and locations
4. Use shortcodes to display listings and forms on your pages

## Usage

### Shortcodes

**Display Listings:**
```
[pacwp_listings]
[pacwp_listings category="cars-trucks" limit="5"]
[pacwp_listings location="new-york" limit="10"]
```

**Submission Form:**
```
[pacwp_submit_form]
```

**Search Form:**
```
[pacwp_search_form]
[pacwp_search_form show_categories="false"]
[pacwp_search_form show_locations="false"]
```

### Pages Setup

1. **Listings Page**: Create a new page and add `[pacwp_listings]` shortcode
2. **Submit Listing Page**: Create a new page and add `[pacwp_submit_form]` shortcode
3. **Search Page**: Create a new page and add `[pacwp_search_form]` and `[pacwp_listings]` shortcodes

### Default Categories

The plugin creates these default categories on activation:
- **For Sale**: cars-trucks, electronics, furniture, general
- **Housing**: apartments-housing, rooms-shared, housing-wanted  
- **Jobs**: accounting-finance, admin-office, art-media-design, general-labor
- **Services**: automotive, beauty, computer, household
- **Community**: activities, artists, childcare, general

### Default Locations

Default locations include: New York, Los Angeles, Chicago, Houston, Phoenix

## Customization

### Templates

The plugin includes custom templates that override WordPress defaults:
- `templates/single-pacwp_listing.php` - Individual listing display
- `templates/archive-pacwp_listing.php` - Listings archive and category pages

### Styling

The plugin includes Craigslist-style CSS in `assets/css/style.css`. You can override styles in your theme's CSS.

### Adding Custom Fields

You can extend the plugin by adding more custom fields in the `listing_details_meta_box()` function and handling them in `save_listing_meta()`.

## Admin Features

- Manage listings from WordPress admin
- Approve/reject pending listings
- Edit listing details and meta information
- Manage categories and locations
- View all submissions and contact information

## URL Structure

- All listings: `/listings/`
- Category archive: `/listing-category/category-name/`
- Location archive: `/location/location-name/`
- Individual listing: `/listings/listing-title/`

## Requirements

- WordPress 5.0+
- PHP 7.4+

## Development

The plugin is structured with:
- Main plugin file: `pacwp-classifieds.php`
- CSS styles: `assets/css/style.css`
- JavaScript: `assets/js/script.js`
- Templates: `templates/`

## Support

For support and customization, refer to the WordPress Codex for custom post types and taxonomies.