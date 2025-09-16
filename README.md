# PACWP - Craigslist-like WordPress Classifieds

A complete classified ads system for WordPress that recreates the functionality and aesthetic of Craigslist. This plugin allows users to post, browse, and search classified listings with categories like jobs, housing, for sale items, services, and more.

## Features

### Core Functionality
- **Custom Post Type**: Dedicated "Classified Listings" post type with full WordPress functionality
- **Taxonomies**: Categories (Jobs, Housing, For Sale, Services, etc.) and Locations for organizing listings
- **Custom Fields**: Price, contact email, phone, and listing type fields
- **Frontend Submission**: User-friendly form for submitting new listings
- **Search & Filter**: Advanced search with category, location, and keyword filtering
- **Craigslist-style Design**: Simple, functional styling that mimics Craigslist's aesthetic

### Admin Features
- **Dashboard Statistics**: Overview of listings with charts and metrics
- **Custom Admin Columns**: Enhanced listing management with sortable columns
- **Settings Panel**: Configure auto-publishing, user requirements, and notifications
- **Bulk Management**: Easy management of listings, categories, and locations

### Frontend Features
- **Archive Pages**: Category and location-based listing archives
- **Single Listing Pages**: Detailed listing view with contact information
- **Shortcodes**: Flexible display options for any page or post
- **Responsive Design**: Mobile-friendly layout
- **Search Form**: Integrated search functionality

## Installation

### Method 1: Manual Installation
1. Download or clone this repository
2. Upload the `PACWP` folder to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Configure settings under "Classifieds" > "Settings"

### Method 2: Direct Upload
1. Download the plugin files as a ZIP
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin

## Setup

### 1. Initial Configuration
- Go to **Classifieds > Settings** in your WordPress admin
- Configure auto-publishing preferences
- Set up admin notification email
- Adjust listings per page

### 2. Create Categories
- Navigate to **Classifieds > Categories**
- Add categories like "Jobs", "Housing", "For Sale", "Services", etc.
- The plugin creates default categories on activation

### 3. Create Pages
Create the following pages for the best user experience:

#### Listings Archive Page
- Create a new page titled "Classified Listings"
- Set the page slug to "listings"
- Leave content empty (it will auto-populate)

#### Submit Listing Page
- Create a new page titled "Post a Listing"
- Add the shortcode: `[pacwp_submit_form]`
- Publish the page

#### Browse Categories Page
- Create a new page titled "Browse Categories"
- Add the shortcode: `[pacwp_listings]`
- Customize with category-specific shortcodes if needed

## Shortcodes

### Display Listings
```
[pacwp_listings] - Show all listings
[pacwp_listings category="for-sale" limit="5"] - Show 5 listings from "for-sale" category
[pacwp_listings location="new-york" orderby="price" order="ASC"] - Show listings from New York, sorted by price
[pacwp_listings orderby="date" order="DESC" limit="10"] - Show 10 newest listings
```

### Submit Form
```
[pacwp_submit_form] - Display the listing submission form
```

## Customization

### Template Override
You can override plugin templates by copying them to your theme:

1. Copy files from `/plugins/PACWP/templates/` to `/your-theme/pacwp/`
2. Modify the templates as needed
3. The plugin will automatically use your theme's versions

### CSS Customization
Add custom CSS to your theme's `style.css` or through the WordPress Customizer:

```css
/* Override plugin styles */
.pacwp-listings {
    /* Your custom styles */
}

.pacwp-listing-item {
    /* Custom listing item styles */
}
```

## File Structure

```
PACWP/
├── pacwp-classifieds.php     # Main plugin file
├── README.md                 # This file
├── assets/
│   ├── css/
│   │   └── pacwp-style.css  # Frontend styles
│   └── js/
│       └── pacwp-script.js  # Frontend JavaScript
├── includes/
│   └── admin.php            # Admin functionality
└── templates/
    ├── archive-listing.php   # Listings archive template
    ├── single-listing.php    # Single listing template
    └── submit-form.php       # Listing submission form
```

## Usage Examples

### Creating a Real Estate Section
1. Create a category called "Real Estate"
2. Use shortcode: `[pacwp_listings category="real-estate" orderby="price"]`
3. Style with custom CSS for property-specific layout

### Job Board
1. Create categories for different job types
2. Use shortcode: `[pacwp_listings category="jobs" orderby="date"]`
3. Add custom fields for salary ranges and job requirements

### Marketplace
1. Create "For Sale" categories (Electronics, Furniture, etc.)
2. Use shortcode: `[pacwp_listings category="for-sale" orderby="price" order="ASC"]`
3. Enable price-based sorting and filtering

## Admin Guide

### Managing Listings
- **View All Listings**: Go to Classifieds > All Listings
- **Edit Listing**: Click on any listing title or use the "Edit" link
- **Bulk Actions**: Select multiple listings for bulk approval/deletion
- **Filter by Status**: Use the status filters (Published, Pending, Draft)

### Categories and Locations
- **Manage Categories**: Classifieds > Categories
- **Manage Locations**: Classifieds > Locations
- **Hierarchical Categories**: Create parent/child category relationships
- **Bulk Import**: Use WordPress's standard term import tools

### Statistics and Reports
- **Dashboard**: Classifieds > Statistics
- **View Charts**: Category distribution and listing trends
- **Recent Activity**: Monitor latest listings and user activity

## Troubleshooting

### Common Issues

#### Listings Not Displaying
1. Check that the plugin is activated
2. Verify shortcodes are correctly placed
3. Ensure listings exist and are published
4. Check theme compatibility

#### Search Not Working
1. Verify search form is properly configured
2. Check for JavaScript errors in browser console
3. Ensure categories and locations are properly assigned

#### Styling Issues
1. Check for theme conflicts
2. Verify CSS files are loading
3. Use browser developer tools to inspect styles
4. Add custom CSS if needed

## Security Features

- **Nonce Verification**: All forms use WordPress nonces
- **Input Sanitization**: All user input is properly sanitized
- **Capability Checks**: Proper permission checks for admin functions
- **SQL Injection Protection**: Uses WordPress database methods
- **XSS Prevention**: All output is properly escaped

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Complete Craigslist-like functionality
- Custom post types and taxonomies
- Frontend submission and search
- Admin dashboard and statistics
- Responsive design
- Security features implemented