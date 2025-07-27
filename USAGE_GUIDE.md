# Bulk Add to Cart Plugin - Usage Guide

## Quick Start Guide

### 1. Installation and Setup

1. **Install the Plugin:**
   - Upload `bulk-add-to-cart.php` to your WordPress plugins directory
   - Activate the plugin in WordPress admin

2. **Configure Settings:**
   - Go to **Bulk Add to Cart > Settings** in WordPress admin
   - Configure your CSV column headers and identifier types
   - Save settings

3. **Add the Upload Form:**
   - Add the shortcode `[bulk_add_to_cart]` to any page or post
   - Ensure the page is accessible to logged-in users

### 2. Basic Usage

1. **Prepare Your CSV File:**
   ```
   product_id,quantity
   123,2
   456,1
   789,3
   ```

2. **Upload and Process:**
   - Navigate to the page with the upload form
   - Select your CSV file
   - Click "Add to Cart"
   - Review the results

---

## Detailed Configuration

### CSV Column Configuration

#### Setting Up Column Headers

1. **Go to Settings:**
   ```
   WordPress Admin → Bulk Add to Cart → Settings
   ```

2. **Configure Identifier Column:**
   - **Identifier Column:** Enter the exact column header from your CSV
   - **Identifier Type:** Choose how to identify products
   - **Quantity Column:** Enter the exact column header for quantities

#### Supported Identifier Types

| Type | Description | CSV Example | Use Case |
|------|-------------|-------------|----------|
| `product_id` | Product ID | `123` | Direct product ID lookup |
| `product_sku` | Product SKU | `ABC-123` | SKU-based identification |
| `product_slug` | Product slug | `my-product` | URL-friendly identifiers |
| `product_title` | Product title | `My Product Name` | Human-readable names |
| `meta_field` | Custom meta field | `CUSTOM-001` | Custom identifiers |

### CSV File Format Examples

#### Example 1: Product ID with Quantity
```csv
product_id,quantity
123,2
456,1
789,3
```

#### Example 2: SKU with Quantity
```csv
sku,quantity
ABC-123,2
DEF-456,1
GHI-789,3
```

#### Example 3: Product Title with Quantity
```csv
product_title,quantity
"Widget A",2
"Widget B",1
"Widget C",3
```

#### Example 4: Custom Meta Field
```csv
custom_id,quantity
CUSTOM-001,2
CUSTOM-002,1
CUSTOM-003,3
```

### Advanced Configuration

#### Debug Mode

Enable debug mode to see detailed processing information:

1. **Go to Settings:**
   ```
   WordPress Admin → Bulk Add to Cart → Settings
   ```

2. **Enable Debug Mode:**
   - Check the "Debug Mode" checkbox
   - Save settings

3. **Debug Information Shows:**
   - CSV headers detected
   - Column indices found
   - Product lookup results
   - Processing details for each row

#### Redirect to Cart

Enable automatic redirect to cart after successful import:

1. **Go to Settings:**
   ```
   WordPress Admin → Bulk Add to Cart → Settings
   ```

2. **Enable Redirect:**
   - Check the "Redirect to Cart" checkbox
   - Save settings

---

## Usage Examples

### Example 1: Basic Product ID Import

**CSV File (`products.csv`):**
```csv
product_id,quantity
123,2
456,1
789,3
```

**Settings Configuration:**
- Identifier Column: `product_id`
- Identifier Type: `product_id`
- Quantity Column: `quantity`

**Process:**
1. Upload `products.csv`
2. Click "Add to Cart"
3. Review results

**Expected Output:**
```
✓ 3 products added to cart.
• Product A: 2
• Product B: 1  
• Product C: 3
```

### Example 2: SKU-Based Import

**CSV File (`inventory.csv`):**
```csv
sku,quantity
ABC-123,2
DEF-456,1
GHI-789,3
```

**Settings Configuration:**
- Identifier Column: `sku`
- Identifier Type: `product_sku`
- Quantity Column: `quantity`

**Process:**
1. Upload `inventory.csv`
2. Click "Add to Cart"
3. Review results

### Example 3: Custom Meta Field Import

**CSV File (`custom_products.csv`):**
```csv
custom_id,quantity
CUSTOM-001,2
CUSTOM-002,1
CUSTOM-003,3
```

**Settings Configuration:**
- Identifier Column: `custom_id`
- Identifier Type: `meta_field`
- Meta Field Name: `custom_product_id`
- Quantity Column: `quantity`

**Process:**
1. Upload `custom_products.csv`
2. Click "Add to Cart"
3. Review results

---

## Error Handling and Troubleshooting

### Common Error Scenarios

#### 1. "Product not found" Errors

**Cause:** Product identifier doesn't match any products in WooCommerce

**Solutions:**
- Verify the product exists in WooCommerce
- Check the identifier type setting
- Ensure CSV column headers match settings
- Verify the identifier format (ID, SKU, slug, title)

**Example Error:**
```
Row 2: Product not found: ABC-123 (Quantity: 2)
```

**Debug Steps:**
1. Enable debug mode
2. Check CSV headers match settings
3. Verify product exists in WooCommerce
4. Check identifier format

#### 2. "Insufficient stock" Errors

**Cause:** Requested quantity exceeds available stock

**Solutions:**
- Check product stock levels
- Verify stock management is enabled
- Reduce requested quantities
- Restock products

**Example Error:**
```
Row 3: Insufficient stock for: ABC-123 (Requested: 5, Available: 2)
```

**Debug Steps:**
1. Check product stock in WooCommerce admin
2. Verify stock management settings
3. Adjust quantities in CSV file

#### 3. "Security check failed" Errors

**Cause:** Nonce verification failure or form submission issues

**Solutions:**
- Ensure form is submitted from correct page
- Check for JavaScript conflicts
- Verify nonce field is present
- Clear browser cache

**Debug Steps:**
1. Disable browser extensions
2. Clear browser cache
3. Try different browser
4. Check for JavaScript errors

#### 4. "Invalid CSV format" Errors

**Cause:** CSV file format issues

**Solutions:**
- Ensure file is actually CSV format
- Check for empty rows at beginning
- Verify column headers match settings
- Use proper CSV encoding

**Debug Steps:**
1. Open CSV in text editor
2. Check for empty first row
3. Verify column headers
4. Ensure proper CSV format

### Debug Mode Usage

#### Enabling Debug Mode

1. **Go to Settings:**
   ```
   WordPress Admin → Bulk Add to Cart → Settings
   ```

2. **Enable Debug:**
   - Check "Debug Mode" checkbox
   - Save settings

#### Debug Information

When debug mode is enabled, you'll see:

**CSV Headers:**
```
CSV Headers: product_id, quantity
```

**Settings Information:**
```
Using settings - Identifier Column: product_id, Type: product_id, Quantity Column: quantity
```

**Column Indices:**
```
Column indices - Identifier: 0, Quantity: 1
```

**Processing Details:**
```
Processing row 2 - Identifier: 123, Quantity: 2
Product lookup for 123: Found
Successfully added product 123 to cart
```

---

## Import History

### Viewing Import History

1. **Go to Settings:**
   ```
   WordPress Admin → Bulk Add to Cart → Settings
   ```

2. **View History Table:**
   - Date/Time of import
   - User who performed import
   - File name
   - Success count
   - Error count

### History Details

Click on success or error count buttons to see detailed information:

**Success Details:**
```
Product A: 2
Product B: 1
Product C: 3
```

**Error Details:**
```
Row 2: Product not found: ABC-123 (Quantity: 2)
Row 4: Insufficient stock for: DEF-456 (Requested: 5, Available: 2)
```

---

## Advanced Usage

### Custom Meta Field Integration

#### Setting Up Custom Meta Fields

1. **Add Custom Meta Field to Products:**
   ```php
   // Add custom meta field to products
   add_action('woocommerce_product_options_general_product_data', 'add_custom_product_field');
   function add_custom_product_field() {
       woocommerce_wp_text_input(
           array(
               'id' => 'custom_product_id',
               'label' => 'Custom Product ID',
               'desc_tip' => true,
               'description' => 'Enter custom product identifier'
           )
       );
   }
   ```

2. **Save Custom Meta Field:**
   ```php
   add_action('woocommerce_process_product_meta', 'save_custom_product_field');
   function save_custom_product_field($post_id) {
       $custom_id = $_POST['custom_product_id'];
       if (!empty($custom_id)) {
           update_post_meta($post_id, 'custom_product_id', sanitize_text_field($custom_id));
       }
   }
   ```

3. **Configure Plugin Settings:**
   - Identifier Type: `meta_field`
   - Meta Field Name: `custom_product_id`

4. **Use in CSV:**
   ```csv
   custom_id,quantity
   CUSTOM-001,2
   CUSTOM-002,1
   ```

### Programmatic Usage

#### Accessing Plugin Settings

```php
// Get current settings
$options = get_option('bulk_add_to_cart_settings');

// Access specific settings
$identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
$identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
$quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
$debug_mode = isset($options['debug_mode']) ? $options['debug_mode'] : '0';
```

#### Accessing Import History

```php
// Get import history
$history = get_option('bulk_add_to_cart_history', array());

// Process history entries
foreach ($history as $entry) {
    echo "Date: " . $entry['timestamp'] . "\n";
    echo "User: " . $entry['username'] . "\n";
    echo "Success Count: " . $entry['success_count'] . "\n";
    echo "Error Count: " . $entry['error_count'] . "\n";
    
    // Access detailed information
    if (!empty($entry['successes'])) {
        foreach ($entry['successes'] as $product_name => $quantity) {
            echo "Success: $product_name - $quantity\n";
        }
    }
    
    if (!empty($entry['errors'])) {
        foreach ($entry['errors'] as $error) {
            echo "Error: $error\n";
        }
    }
}
```

#### Custom Product Lookup

```php
// Example of custom product lookup function
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

## Best Practices

### CSV File Preparation

1. **Use Consistent Format:**
   - Always include headers
   - Use consistent column names
   - Avoid empty rows

2. **Data Validation:**
   - Verify product identifiers exist
   - Check stock levels before import
   - Use appropriate quantities

3. **File Management:**
   - Use descriptive file names
   - Keep backup copies
   - Archive processed files

### Performance Optimization

1. **File Size:**
   - Process files in smaller batches
   - Monitor memory usage
   - Use appropriate file sizes

2. **Processing:**
   - Enable debug mode for troubleshooting
   - Monitor import history
   - Review error logs

3. **Security:**
   - Use secure file uploads
   - Validate file types
   - Implement proper access controls

### Error Prevention

1. **Pre-Import Checks:**
   - Verify product existence
   - Check stock levels
   - Validate CSV format

2. **Testing:**
   - Test with small files first
   - Verify settings configuration
   - Check error handling

3. **Monitoring:**
   - Review import history
   - Monitor error rates
   - Track success rates

---

## Support and Troubleshooting

### Getting Help

1. **Check Documentation:**
   - Review plugin documentation page
   - Consult this usage guide
   - Check FAQ section

2. **Enable Debug Mode:**
   - Enable debug mode for detailed information
   - Review debug output
   - Check processing details

3. **Review Import History:**
   - Check recent imports
   - Review error details
   - Monitor success rates

### Common Issues

1. **"Product not found" errors:**
   - Verify product exists in WooCommerce
   - Check identifier type setting
   - Ensure CSV headers match settings

2. **"Insufficient stock" errors:**
   - Check product stock levels
   - Verify stock management settings
   - Adjust quantities in CSV

3. **"Security check failed" errors:**
   - Ensure form submission from correct page
   - Check for JavaScript conflicts
   - Clear browser cache

4. **"Invalid CSV format" errors:**
   - Verify file is CSV format
   - Check for empty rows
   - Ensure proper column headers

### Performance Tips

1. **File Processing:**
   - Use smaller file sizes
   - Process in batches
   - Monitor memory usage

2. **Database Operations:**
   - Use appropriate indexes
   - Monitor query performance
   - Optimize product lookups

3. **User Experience:**
   - Provide clear instructions
   - Show progress indicators
   - Display helpful error messages