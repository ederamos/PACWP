#!/bin/bash

# PACWP Classifieds Installation Script
# This script helps set up the plugin in a WordPress installation

echo "PACWP Classifieds - WordPress Plugin Installer"
echo "=============================================="

# Check if WordPress directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 /path/to/wordpress"
    echo "Example: $0 /var/www/html/wordpress"
    exit 1
fi

WORDPRESS_DIR="$1"
PLUGIN_DIR="$WORDPRESS_DIR/wp-content/plugins/pacwp-classifieds"

# Check if WordPress directory exists
if [ ! -d "$WORDPRESS_DIR" ]; then
    echo "Error: WordPress directory '$WORDPRESS_DIR' does not exist!"
    exit 1
fi

# Check if wp-config.php exists
if [ ! -f "$WORDPRESS_DIR/wp-config.php" ]; then
    echo "Error: wp-config.php not found in '$WORDPRESS_DIR'!"
    echo "Please ensure this is a valid WordPress installation."
    exit 1
fi

echo "WordPress installation found at: $WORDPRESS_DIR"

# Create plugin directory
echo "Creating plugin directory..."
mkdir -p "$PLUGIN_DIR"

# Copy plugin files
echo "Copying plugin files..."
cp pacwp-classifieds.php "$PLUGIN_DIR/"
cp -r assets "$PLUGIN_DIR/"
cp -r templates "$PLUGIN_DIR/"
cp README.md "$PLUGIN_DIR/"

# Set proper permissions
echo "Setting permissions..."
find "$PLUGIN_DIR" -type f -exec chmod 644 {} \;
find "$PLUGIN_DIR" -type d -exec chmod 755 {} \;

# Check if installation was successful
if [ -f "$PLUGIN_DIR/pacwp-classifieds.php" ]; then
    echo "✓ Plugin installed successfully!"
    echo ""
    echo "Next steps:"
    echo "1. Log in to your WordPress admin panel"
    echo "2. Go to Plugins > Installed Plugins"
    echo "3. Find 'PACWP Classifieds' and click 'Activate'"
    echo "4. Create pages for your classifieds site:"
    echo "   - Listings page: Add [pacwp_listings] shortcode"
    echo "   - Submit page: Add [pacwp_submit_form] shortcode"
    echo "   - Search page: Add [pacwp_search_form] shortcode"
    echo ""
    echo "Plugin location: $PLUGIN_DIR"
    echo ""
    echo "For more information, see README.md or demo.html"
else
    echo "✗ Installation failed!"
    exit 1
fi