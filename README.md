# AltOffCanvas Plugin

Alternative off-canvas cart plugin for Shopware 6 with enhanced product display and cross-selling functionality.

## Overview

AltOffCanvas replaces the default Shopware 6 off-canvas cart with a customized version that displays the most recently added item prominently along with relevant cross-selling products. The plugin provides a streamlined cart experience focused on highlighting the newly added product and encouraging additional purchases through targeted product recommendations.

## Features

- **Enhanced Item Display**: Shows the most recently added item with product image, name, quantity, and price
- **Cross-selling Integration**: Displays relevant cross-selling products based on the added item
- **Flash Message Detection**: Intelligently detects add-to-cart actions through flash message analysis
- **Bootstrap Styling**: Uses Bootstrap classes for consistent theming with Shopware's default design
- **Responsive Design**: Mobile-friendly layout with proper grid system implementation

## System Requirements

- Shopware 6.4.0 or higher
- PHP 8.0 or higher
- Modern web browser with JavaScript support

## Installation

### Via Admin Panel (Recommended)

1. **Create Plugin Archive**
   - Create a ZIP file containing the entire plugin directory
   - Ensure the ZIP contains the folder structure: `AltOffCanvas/` (with all plugin files inside)
   - The ZIP file should be named `AltOffCanvas.zip`

2. **Upload via Administration**
   - Log into your Shopware 6 administration panel
   - Navigate to `Extensions` > `My extensions`
   - Click `Upload extension` button
   - Select your `AltOffCanvas.zip` file and upload
   - Wait for the upload to complete

3. **Install and Activate**
   - After upload, the plugin will appear in the extension list
   - Click `Install` next to the AltOffCanvas plugin
   - Once installed, click `Activate` to enable the plugin
   - The system will automatically clear necessary caches

### Manual File Installation

1. Download or clone the plugin files to your Shopware installation:
   ```
   custom/plugins/AltOffCanvas/
   ```

2. Install the plugin via Shopware CLI:
   ```bash
   bin/console plugin:refresh
   bin/console plugin:install --activate AltOffCanvas
   ```

3. Clear the cache:
   ```bash
   bin/console cache:clear
   ```

### Composer Installation

Add the plugin to your `composer.json` and run:
```bash
composer install
bin/console plugin:refresh
bin/console plugin:install --activate AltOffCanvas
bin/console cache:clear
```

## Configuration

### Cross-selling Setup

The plugin automatically displays cross-selling products for the most recently added item. To configure cross-selling:

1. **Navigate to Administration Panel**
   - Go to `Catalogues` > `Products`
   - Select the product you want to configure

2. **Configure Cross-selling Groups**
   - Open the product detail page
   - Navigate to the `Cross-selling` tab
   - Click `Add cross-selling`

3. **Set Cross-selling Properties**
   - **Name**: Enter a descriptive name for the cross-selling group
   - **Type**: Select the appropriate cross-selling type:
     - Manual selection
     - Dynamic product group
     - Product stream
   - **Display Type**: Choose how products should be displayed
   - **Sorting**: Configure product sorting within the group

4. **Add Products to Cross-selling**
   - For manual selection: Click `Add products` and select items
   - For dynamic groups: Configure the product selection rules
   - For product streams: Select an existing product stream

5. **Configure Display Settings**
   - Set the maximum number of products to display (recommended: 3-6)
   - Configure product sorting (price, name, popularity, etc.)
   - Enable/disable specific cross-selling groups as needed

### Plugin Configuration

The plugin includes a configuration system accessible through:

1. **Administration Panel**
   - Navigate to `Extensions` > `My extensions`
   - Find `AltOffCanvas` and click the configuration button

2. **Available Settings**
   - **Cross-selling Index**: Configure which cross-selling group to prioritize
   - **Display Options**: Customize the appearance of the off-canvas cart
   - **Product Limit**: Set maximum number of cross-selling products to display

## Technical Details

### Architecture

The plugin consists of several key components:

- **OffcanvasCartSubscriber**: Handles the off-canvas cart page loaded event and provides cross-selling data
- **CrossSellingService**: Manages cross-selling product retrieval and filtering
- **Template Override**: Custom Twig template that replaces the default off-canvas cart display

### Event Handling

The plugin listens for the `OffcanvasCartPageLoadedEvent` to:
1. Identify the most recently added product
2. Retrieve relevant cross-selling products
3. Pass data to the template for display

### Cross-selling Logic

The cross-selling functionality works by:
1. Determining the last added item in the cart
2. Loading the product's configured cross-selling groups
3. Filtering and sorting cross-selling products
4. Limiting results to the configured maximum number

### Template Structure

The custom template provides:
- Product information display (image, name, quantity, price)
- Cart summary with total and item count
- Action buttons (Continue Shopping, View Cart)
- Cross-selling product grid with add-to-cart functionality

## Troubleshooting

### Common Issues

**Off-canvas not showing custom template**
- Ensure the plugin is activated: `bin/console plugin:list`
- Clear cache: `bin/console cache:clear`
- Check template inheritance in browser developer tools

**Cross-selling products not appearing**
- Verify cross-selling is configured for the product in administration
- Check that cross-selling products are active and available
- Ensure cross-selling groups have products assigned

**Wrong product shown as "recently added"**
- This is a known limitation when adding the same product multiple times consecutively
- The plugin works correctly when adding different products

**Styling issues**
- Verify Bootstrap is loaded in your theme
- Check for CSS conflicts in browser developer tools
- Ensure theme compatibility with Bootstrap classes

### Debug Mode

Enable debug logging by adding to your `.env.local`:
```
APP_LOG_LEVEL=debug
```

Check logs at `var/log/dev.log` for plugin-specific messages.

### Performance Considerations

- Cross-selling queries are optimized with proper criteria filtering
- Products are limited to prevent excessive database queries  
- Caching is utilized where possible for improved performance

## Support

For technical support or bug reports:
1. Check the troubleshooting section above
2. Review Shopware logs for error messages
3. Ensure all system requirements are met
4. Verify plugin configuration is correct

## License

This plugin is licensed under the MIT License. See the LICENSE file for details.

## Compatibility

- Shopware 6.4.x: Fully supported
- Shopware 6.5.x: Fully supported  
- Shopware 6.6.x and higher: Compatible with minor adjustments

## Changelog

### Version 1.0.0
- Initial release
- Basic off-canvas cart replacement
- Cross-selling integration
- Bootstrap styling implementation
- Flash message-based add-to-cart detection