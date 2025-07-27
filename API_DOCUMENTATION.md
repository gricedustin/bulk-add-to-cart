# Bulk Add to Cart Plugin - API Documentation

## Overview

The Bulk Add to Cart plugin is a WordPress plugin that allows users to bulk add products to their WooCommerce cart using CSV files. This documentation covers all public APIs, functions, and components available in the plugin.

**Version:** 1.1.1  
**Author:** Grice AI  
**License:** GPL v2 or later  
**Requires:** WordPress 5.0+, PHP 7.2+, WooCommerce 3.0+

## Table of Contents

1. [Plugin Constants](#plugin-constants)
2. [Admin Menu Functions](#admin-menu-functions)
3. [Settings Management](#settings-management)
4. [Shortcode Functions](#shortcode-functions)
5. [File Processing Functions](#file-processing-functions)
6. [Utility Functions](#utility-functions)
7. [Admin Page Functions](#admin-page-functions)
8. [Hooks and Filters](#hooks-and-filters)
9. [Usage Examples](#usage-examples)
10. [Error Handling](#error-handling)

---

## Plugin Constants

### `BULK_ADD_TO_CART_VERSION`
**Type:** String  
**Value:** '1.1.1'  
**Description:** Current plugin version number.

### `BULK_ADD_TO_CART_PLUGIN_DIR`
**Type:** String  
**Value:** Plugin directory path  
**Description:** Absolute path to the plugin directory.

### `BULK_ADD_TO_CART_PLUGIN_URL`
**Type:** String  
**Value:** Plugin URL  
**Description:** URL to the plugin directory.

### `BULK_ADD_TO_CART_UPLOAD_DIR`
**Type:** String  
**Value:** `WP_CONTENT_DIR . '/bulk-add-to-cart-import-files/'`  
**Description:** Directory where uploaded CSV files are stored.

---

## Admin Menu Functions

### `bulk_add_to_cart_add_admin_menu()`

**Description:** Registers the admin menu pages for the plugin.  
**Hook:** `admin_menu`  
**Capability:** `manage_options`

**Menu Structure:**
- **Main Menu:** "Bulk Add to Cart" (dashicons-cart, priority 30)
  - **Settings:** Main settings page
  - **Documentation:** Plugin documentation
  - **Changelog:** Version history

**Usage:**
```php
// Automatically called via admin_menu hook
// No manual calling required
```

---

## Settings Management

### `bulk_add_to_cart_register_settings()`

**Description:** Registers all plugin settings with WordPress.  
**Hook:** `admin_init`  
**Capability:** `manage_options`

**Registered Settings:**
- `bulk_add_to_cart_settings` (array)

**Settings Fields:**
- `redirect_to_cart` - Redirect to cart after import
- `identifier_column` - CSV column name for product identifier
- `identifier_type` - Type of identifier (product_id, product_sku, product_slug, product_title, meta_field)
- `meta_field_name` - Custom meta field name for meta_field identifier type
- `quantity_column` - CSV column name for quantity
- `debug_mode` - Enable debug mode for detailed processing information

**Usage:**
```php
// Automatically called via admin_init hook
// No manual calling required
```

### `bulk_add_to_cart_section_callback()`

**Description:** Callback function for the main settings section.  
**Output:** HTML description for the settings section.

### `bulk_add_to_cart_redirect_callback()`

**Description:** Renders the redirect to cart setting field.  
**Output:** HTML checkbox input for redirect setting.

### `bulk_add_to_cart_identifier_column_callback()`

**Description:** Renders the identifier column setting field.  
**Output:** HTML text input for CSV column name.

### `bulk_add_to_cart_identifier_type_callback()`

**Description:** Renders the identifier type setting field.  
**Output:** HTML select dropdown with identifier type options.

**Available Options:**
- `product_id` - Product ID
- `product_sku` - Product SKU
- `product_slug` - Product slug
- `product_title` - Product title
- `meta_field` - Custom meta field value

### `bulk_add_to_cart_meta_field_name_callback()`

**Description:** Renders the meta field name setting field.  
**Output:** HTML text input for custom meta field name.

### `bulk_add_to_cart_quantity_column_callback()`

**Description:** Renders the quantity column setting field.  
**Output:** HTML text input for CSV column name.

### `bulk_add_to_cart_debug_mode_callback()`

**Description:** Renders the debug mode setting field.  
**Output:** HTML checkbox input for debug mode.

---

## Shortcode Functions

### `bulk_add_to_cart_shortcode()`

**Description:** Renders the bulk add to cart upload form.  
**Shortcode:** `[bulk_add_to_cart]`  
**Capability:** Requires user to be logged in

**Features:**
- User authentication check
- Debug information display (when form is submitted)
- Instructions for CSV format
- File upload form
- Nonce security

**Usage:**
```php
// Add to any page or post
[bulk_add_to_cart]
```

**Output:** HTML form with:
- Instructions for CSV format
- File upload input
- Submit button
- Debug information (if enabled and form submitted)

**Security Features:**
- Nonce verification
- User authentication check
- File type validation (.csv only)

---

## File Processing Functions

### `bulk_add_to_cart_process_upload()`

**Description:** Handles CSV file upload and processing.  
**Hook:** `template_redirect`  
**Capability:** Requires user to be logged in

**Process Flow:**
1. **Security Checks:**
   - Form submission verification
   - Nonce verification
   - File upload validation

2. **File Handling:**
   - File upload to `BULK_ADD_TO_CART_UPLOAD_DIR`
   - CSV parsing and header detection
   - Column index identification

3. **Product Processing:**
   - Product lookup based on identifier type
   - Stock validation
   - Cart addition
   - Error tracking

4. **Results:**
   - Success/error counting
   - WooCommerce notices
   - Import history recording
   - Optional cart redirect

**Supported Identifier Types:**
- **Product ID:** Direct product ID lookup
- **Product SKU:** SKU-based product lookup
- **Product Slug:** Slug-based product lookup
- **Product Title:** Title-based product lookup
- **Meta Field:** Custom meta field value lookup

**Error Handling:**
- Invalid CSV format
- Missing required columns
- Product not found
- Insufficient stock
- Non-purchasable products
- Cart addition failures

**Usage:**
```php
// Automatically called via template_redirect hook
// No manual calling required
```

---

## Utility Functions

### `bulk_add_to_cart_reorder_notices($notices)`

**Description:** Reorders WooCommerce notices to show success before errors.  
**Hook:** `woocommerce_get_notices`  
**Priority:** 20

**Parameters:**
- `$notices` (array) - Array of WooCommerce notices

**Returns:** Array - Reordered notices with success first, then others, then errors

**Usage:**
```php
// Automatically called via woocommerce_get_notices filter
// No manual calling required
```

---

## Admin Page Functions

### `bulk_add_to_cart_settings_page()`

**Description:** Renders the main settings page.  
**Capability:** `manage_options`

**Features:**
- Settings form
- Import history table
- CSV format example
- File download links

**Displayed Information:**
- Plugin settings form
- Import history with success/error counts
- CSV format instructions
- Example CSV structure

### `bulk_add_to_cart_documentation_page()`

**Description:** Renders the documentation page.  
**Capability:** `manage_options`

**Content Sections:**
- Setup instructions
- CSV configuration guide
- Usage instructions
- FAQ section

### `bulk_add_to_cart_changelog_page()`

**Description:** Renders the changelog page.  
**Capability:** `manage_options`

**Content:**
- Version history
- Feature updates
- Bug fixes
- Release dates

---

## Hooks and Filters

### Actions

#### `admin_menu`
- **Function:** `bulk_add_to_cart_add_admin_menu()`
- **Purpose:** Register admin menu pages

#### `admin_init`
- **Function:** `bulk_add_to_cart_register_settings()`
- **Purpose:** Register plugin settings

#### `template_redirect`
- **Function:** `bulk_add_to_cart_process_upload()`
- **Purpose:** Handle CSV file processing

### Filters

#### `woocommerce_get_notices`
- **Function:** `bulk_add_to_cart_reorder_notices($notices)`
- **Priority:** 20
- **Purpose:** Reorder WooCommerce notices

---

## Usage Examples

### Basic Shortcode Usage

```php
// Add to any page or post
[bulk_add_to_cart]
```

### Programmatic Settings Access

```php
// Get current plugin settings
$options = get_option('bulk_add_to_cart_settings');

// Access specific settings
$identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
$identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
$quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
$debug_mode = isset($options['debug_mode']) ? $options['debug_mode'] : '0';
```

### Import History Access

```php
// Get import history
$history = get_option('bulk_add_to_cart_history', array());

// Access specific import entry
foreach ($history as $entry) {
    echo "Date: " . $entry['timestamp'];
    echo "User: " . $entry['username'];
    echo "Success Count: " . $entry['success_count'];
    echo "Error Count: " . $entry['error_count'];
}
```

### Custom Product Lookup

```php
// Example of how the plugin looks up products
function custom_product_lookup($identifier, $type) {
    switch ($type) {
        case 'product_id':
            return wc_get_product($identifier);
        case 'product_sku':
            $product_id = wc_get_product_id_by_sku($identifier);
            return $product_id ? wc_get_product($product_id) : null;
        case 'product_slug':
            $product_id = wc_get_product_id_by_slug($identifier);
            return $product_id ? wc_get_product($product_id) : null;
        case 'product_title':
            global $wpdb;
            $product_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'product'",
                $identifier
            ));
            return $product_id ? wc_get_product($product_id) : null;
        case 'meta_field':
            global $wpdb;
            $meta_field_name = 'your_meta_field_name';
            $product_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
                $meta_field_name,
                $identifier
            ));
            return $product_id ? wc_get_product($product_id) : null;
    }
    return null;
}
```

---

## Error Handling

### Common Error Scenarios

1. **Security Errors:**
   - Nonce verification failure
   - User not logged in
   - Insufficient permissions

2. **File Upload Errors:**
   - Invalid file type (non-CSV)
   - File upload failure
   - File read errors

3. **CSV Processing Errors:**
   - Invalid CSV format
   - Missing required columns
   - Empty or invalid data

4. **Product Lookup Errors:**
   - Product not found
   - Invalid identifier type
   - Meta field not configured

5. **Cart Addition Errors:**
   - Product not purchasable
   - Insufficient stock
   - WooCommerce cart errors

### Error Messages

The plugin provides detailed error messages for:
- Row-specific errors with line numbers
- Product identification failures
- Stock availability issues
- Cart addition failures

### Debug Mode

Enable debug mode in settings to see:
- CSV headers found
- Column indices
- Product lookup results
- Processing details for each row

---

## Security Considerations

### Authentication
- All functions require user authentication
- Admin functions require `manage_options` capability

### Data Validation
- Nonce verification for all form submissions
- File type validation (.csv only)
- Input sanitization and escaping

### File Handling
- Secure file upload directory
- File name sanitization
- Temporary file cleanup

### Database Operations
- Prepared statements for all database queries
- Input sanitization for meta field lookups

---

## Performance Considerations

### File Processing
- CSV files are processed row by row
- Large files may take time to process
- Memory usage scales with file size

### History Management
- Import history limited to last 100 entries
- Old entries are automatically removed

### Caching
- Settings are cached via WordPress options
- Product lookups use WooCommerce's built-in caching

---

## Troubleshooting

### Common Issues

1. **"Product not found" errors:**
   - Check identifier type setting
   - Verify CSV column headers match settings
   - Ensure product exists in WooCommerce

2. **"Insufficient stock" errors:**
   - Check product stock levels
   - Verify stock management is enabled
   - Check quantity values in CSV

3. **"Security check failed" errors:**
   - Ensure form is submitted from correct page
   - Check for JavaScript conflicts
   - Verify nonce field is present

4. **"Invalid CSV format" errors:**
   - Check file is actually CSV format
   - Verify column headers match settings
   - Ensure no empty rows at beginning

### Debug Mode

Enable debug mode to see:
- CSV headers detected
- Column indices found
- Product lookup results
- Processing details for each row

### Support

For additional support:
- Check the documentation page in admin
- Review import history for error details
- Enable debug mode for detailed processing information