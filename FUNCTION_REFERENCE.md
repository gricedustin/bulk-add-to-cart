# Bulk Add to Cart Plugin - Function Reference

## Complete Function Documentation

This document provides detailed information about every function in the Bulk Add to Cart plugin, including parameters, return values, hooks, and usage examples.

---

## Core Functions

### `bulk_add_to_cart_add_admin_menu()`

**File:** `bulk-add-to-cart.php` (lines 35-75)  
**Hook:** `admin_menu`  
**Capability:** `manage_options`

**Description:**  
Registers all admin menu pages for the plugin in the WordPress admin dashboard.

**Functionality:**
- Creates main menu item "Bulk Add to Cart" with cart icon
- Adds submenu pages for Settings, Documentation, and Changelog
- Sets appropriate capabilities and menu structure

**Menu Structure Created:**
```
Bulk Add to Cart (Main Menu)
├── Settings
├── Documentation  
└── Changelog
```

**Code Example:**
```php
// This function is automatically called via the admin_menu hook
// No manual calling required
add_action('admin_menu', 'bulk_add_to_cart_add_admin_menu');
```

---

### `bulk_add_to_cart_register_settings()`

**File:** `bulk-add-to-cart.php` (lines 76-136)  
**Hook:** `admin_init`  
**Capability:** `manage_options`

**Description:**  
Registers all plugin settings with WordPress Settings API.

**Registered Settings:**
- `bulk_add_to_cart_settings` (array) - Main settings array

**Settings Fields:**
1. `redirect_to_cart` - Checkbox for redirect to cart after import
2. `identifier_column` - Text field for CSV column name
3. `identifier_type` - Select dropdown for identifier type
4. `meta_field_name` - Text field for custom meta field name
5. `quantity_column` - Text field for quantity column name
6. `debug_mode` - Checkbox for debug mode

**Code Example:**
```php
// This function is automatically called via the admin_init hook
// No manual calling required
add_action('admin_init', 'bulk_add_to_cart_register_settings');
```

---

## Settings Callback Functions

### `bulk_add_to_cart_section_callback()`

**File:** `bulk-add-to-cart.php` (lines 137-141)  
**Hook:** Settings section callback  
**Capability:** `manage_options`

**Description:**  
Renders the main settings section description.

**Output:** HTML description for the settings section.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_redirect_callback()`

**File:** `bulk-add-to-cart.php` (lines 142-154)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the redirect to cart setting field.

**Output:** HTML checkbox input with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_identifier_column_callback()`

**File:** `bulk-add-to-cart.php` (lines 155-164)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the identifier column setting field.

**Output:** HTML text input with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_identifier_type_callback()`

**File:** `bulk-add-to-cart.php` (lines 165-193)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the identifier type setting field.

**Available Options:**
- `product_id` - Product ID
- `product_sku` - Product SKU  
- `product_slug` - Product slug
- `product_title` - Product title
- `meta_field` - Custom meta field value

**Output:** HTML select dropdown with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_meta_field_name_callback()`

**File:** `bulk-add-to-cart.php` (lines 194-203)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the meta field name setting field.

**Output:** HTML text input with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_quantity_column_callback()`

**File:** `bulk-add-to-cart.php` (lines 204-213)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the quantity column setting field.

**Output:** HTML text input with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

### `bulk_add_to_cart_debug_mode_callback()`

**File:** `bulk-add-to-cart.php` (lines 214-226)  
**Hook:** Settings field callback  
**Capability:** `manage_options`

**Description:**  
Renders the debug mode setting field.

**Output:** HTML checkbox input with current setting value.

**Code Example:**
```php
// Called automatically by WordPress Settings API
// No manual calling required
```

---

## Shortcode Functions

### `bulk_add_to_cart_shortcode()`

**File:** `bulk-add-to-cart.php` (lines 227-322)  
**Shortcode:** `[bulk_add_to_cart]`  
**Capability:** Requires user to be logged in

**Description:**  
Renders the bulk add to cart upload form.

**Features:**
- User authentication check
- Debug information display (when form submitted)
- Instructions for CSV format
- File upload form with security
- Nonce verification

**Parameters:** None

**Returns:** String - HTML form markup

**Security Features:**
- Nonce verification
- User authentication check
- File type validation (.csv only)

**Usage Example:**
```php
// Add to any page or post
[bulk_add_to_cart]

// Or programmatically
echo do_shortcode('[bulk_add_to_cart]');
```

**Output Structure:**
```html
<div class="bulk-add-to-cart-form">
    <!-- Debug information (if enabled and form submitted) -->
    <!-- Instructions for CSV format -->
    <!-- File upload form -->
    <!-- Submit button -->
</div>
```

---

## Processing Functions

### `bulk_add_to_cart_process_upload()`

**File:** `bulk-add-to-cart.php` (lines 351-624)  
**Hook:** `template_redirect`  
**Capability:** Requires user to be logged in

**Description:**  
Handles CSV file upload and processing for bulk cart addition.

**Process Flow:**

1. **Security Validation:**
   ```php
   // Check if form was submitted
   if (!isset($_POST['bulk_add_to_cart_submit'])) {
       return;
   }
   
   // Verify nonce
   if (!wp_verify_nonce($_POST['bulk_add_to_cart_nonce'], 'bulk_add_to_cart_upload')) {
       wc_add_notice(__('Security check failed. Please try again.', 'bulk-add-to-cart'), 'error');
       return;
   }
   ```

2. **File Handling:**
   ```php
   // Upload file to secure directory
   $upload_path = BULK_ADD_TO_CART_UPLOAD_DIR . $new_filename;
   move_uploaded_file($file['tmp_name'], $upload_path);
   
   // Parse CSV headers
   $headers = fgetcsv($handle);
   ```

3. **Product Processing:**
   ```php
   // Look up product based on identifier type
   switch ($identifier_type) {
       case 'product_id':
           $product = wc_get_product($identifier);
           break;
       case 'product_sku':
           $product_id = wc_get_product_id_by_sku($identifier);
           $product = $product_id ? wc_get_product($product_id) : null;
           break;
       // ... other cases
   }
   ```

4. **Cart Addition:**
   ```php
   // Add to cart and track results
   $cart_item_key = WC()->cart->add_to_cart($product->get_id(), $quantity);
   ```

**Supported Identifier Types:**
- `product_id` - Direct product ID lookup
- `product_sku` - SKU-based product lookup  
- `product_slug` - Slug-based product lookup
- `product_title` - Title-based product lookup
- `meta_field` - Custom meta field value lookup

**Error Handling:**
- Invalid CSV format
- Missing required columns
- Product not found
- Insufficient stock
- Non-purchasable products
- Cart addition failures

**Returns:** Void (adds WooCommerce notices)

**Usage Example:**
```php
// Automatically called via template_redirect hook
// No manual calling required
add_action('template_redirect', 'bulk_add_to_cart_process_upload');
```

---

## Utility Functions

### `bulk_add_to_cart_reorder_notices($notices)`

**File:** `bulk-add-to-cart.php` (lines 323-350)  
**Hook:** `woocommerce_get_notices`  
**Priority:** 20

**Description:**  
Reorders WooCommerce notices to show success messages before error messages.

**Parameters:**
- `$notices` (array) - Array of WooCommerce notices

**Returns:** Array - Reordered notices with success first, then others, then errors

**Process:**
1. Separates notices by type (success, error, other)
2. Recombines with success first, then others, then errors
3. Returns reordered array

**Code Example:**
```php
// Automatically called via woocommerce_get_notices filter
// No manual calling required
add_filter('woocommerce_get_notices', 'bulk_add_to_cart_reorder_notices', 20);
```

---

## Admin Page Functions

### `bulk_add_to_cart_settings_page()`

**File:** `bulk-add-to-cart.php` (lines 625-739)  
**Capability:** `manage_options`

**Description:**  
Renders the main settings page with form, import history, and CSV format example.

**Features:**
- Settings form with all plugin options
- Import history table with success/error counts
- CSV format instructions and example
- File download links for uploaded files

**Displayed Information:**
- Plugin settings form
- Import history with detailed success/error information
- CSV format instructions
- Example CSV structure based on current settings

**Code Example:**
```php
// Called automatically by WordPress admin menu
// No manual calling required
```

**Output Structure:**
```html
<div class="wrap">
    <!-- Settings form -->
    <div class="card">
        <h2>Plugin Settings</h2>
        <form method="post" action="options.php">
            <!-- Settings fields -->
        </form>
    </div>
    
    <!-- Import history -->
    <div class="card">
        <h2>Import History</h2>
        <table class="wp-list-table">
            <!-- History entries -->
        </table>
    </div>
    
    <!-- CSV format example -->
    <div>
        <h2>CSV File Format</h2>
        <!-- Instructions and example -->
    </div>
</div>
```

---

### `bulk_add_to_cart_documentation_page()`

**File:** `bulk-add-to-cart.php` (lines 740-840)  
**Capability:** `manage_options`

**Description:**  
Renders the comprehensive documentation page.

**Content Sections:**
1. **Setup Instructions:**
   - How to add the shortcode
   - Page accessibility requirements

2. **CSV Configuration:**
   - Settings configuration guide
   - Column header setup
   - Identifier type selection

3. **Usage Instructions:**
   - Step-by-step usage guide
   - File preparation
   - Form submission process

4. **FAQ Section:**
   - Common questions and answers
   - Troubleshooting tips

**Code Example:**
```php
// Called automatically by WordPress admin menu
// No manual calling required
```

---

### `bulk_add_to_cart_changelog_page()`

**File:** `bulk-add-to-cart.php` (lines 841-892)  
**Capability:** `manage_options`

**Description:**  
Renders the changelog page with version history.

**Content:**
- Version history with dates
- Feature updates and improvements
- Bug fixes and changes
- Release information

**Code Example:**
```php
// Called automatically by WordPress admin menu
// No manual calling required
```

---

## Helper Functions

### Product Lookup Functions

The plugin includes several product lookup methods within the `bulk_add_to_cart_process_upload()` function:

#### Product ID Lookup
```php
$product = wc_get_product($identifier);
```

#### SKU Lookup
```php
$product_id = wc_get_product_id_by_sku($identifier);
$product = $product_id ? wc_get_product($product_id) : null;
```

#### Slug Lookup
```php
$product_id = wc_get_product_id_by_slug($identifier);
$product = $product_id ? wc_get_product($product_id) : null;
```

#### Title Lookup
```php
global $wpdb;
$product_id = $wpdb->get_var($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'product'",
    $identifier
));
$product = $product_id ? wc_get_product($product_id) : null;
```

#### Meta Field Lookup
```php
global $wpdb;
$meta_field_name = isset($options['meta_field_name']) ? $options['meta_field_name'] : '';
if (!empty($meta_field_name)) {
    $product_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
        $meta_field_name,
        $identifier
    ));
    $product = $product_id ? wc_get_product($product_id) : null;
}
```

---

## Data Storage

### WordPress Options

The plugin uses the following WordPress options:

#### `bulk_add_to_cart_settings`
**Type:** Array  
**Description:** Main plugin settings

**Structure:**
```php
array(
    'redirect_to_cart' => '0|1',
    'identifier_column' => 'string',
    'identifier_type' => 'product_id|product_sku|product_slug|product_title|meta_field',
    'meta_field_name' => 'string',
    'quantity_column' => 'string',
    'debug_mode' => '0|1'
)
```

#### `bulk_add_to_cart_history`
**Type:** Array  
**Description:** Import history (last 100 entries)

**Structure:**
```php
array(
    array(
        'timestamp' => 'mysql_datetime',
        'user_id' => 'int',
        'username' => 'string',
        'filename' => 'string',
        'success_count' => 'int',
        'error_count' => 'int',
        'errors' => array(),
        'successes' => array()
    )
)
```

---

## Error Handling

### Error Types and Messages

1. **Security Errors:**
   ```php
   wc_add_notice(__('Security check failed. Please try again.', 'bulk-add-to-cart'), 'error');
   ```

2. **File Upload Errors:**
   ```php
   wc_add_notice(__('Error uploading file. Please try again.', 'bulk-add-to-cart'), 'error');
   ```

3. **CSV Processing Errors:**
   ```php
   wc_add_notice(__('Invalid CSV format. Please check the file structure.', 'bulk-add-to-cart'), 'error');
   ```

4. **Product Lookup Errors:**
   ```php
   wc_add_notice(sprintf(
       __('Row %d: Product not found: %s (Quantity: %s)', 'bulk-add-to-cart'),
       $row_number,
       esc_html($identifier),
       esc_html($quantity)
   ), 'error');
   ```

5. **Stock Errors:**
   ```php
   wc_add_notice(sprintf(
       __('Row %d: Insufficient stock for: %s (Requested: %s, Available: %s)', 'bulk-add-to-cart'),
       $row_number,
       esc_html($identifier),
       esc_html($quantity),
       esc_html($product->get_stock_quantity())
   ), 'error');
   ```

---

## Performance Considerations

### File Processing
- CSV files are processed row by row to manage memory usage
- Large files may take time to process
- Memory usage scales with file size

### History Management
- Import history limited to last 100 entries
- Old entries are automatically removed to prevent database bloat

### Caching
- Settings are cached via WordPress options
- Product lookups use WooCommerce's built-in caching mechanisms

---

## Security Features

### Authentication
- All functions require user authentication
- Admin functions require `manage_options` capability
- Shortcode requires logged-in user

### Data Validation
- Nonce verification for all form submissions
- File type validation (.csv only)
- Input sanitization and escaping throughout

### File Handling
- Secure file upload directory outside web root
- File name sanitization
- Temporary file cleanup

### Database Operations
- Prepared statements for all database queries
- Input sanitization for meta field lookups
- Proper escaping for output