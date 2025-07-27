# Bulk Add to Cart Plugin

A powerful WordPress plugin that allows users to bulk add products to their WooCommerce cart using CSV files. Features include support for product IDs, SKUs, slugs, titles, and custom meta fields, variation handling, inventory checking, and import history tracking.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Usage Examples](#usage-examples)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [Support](#support)
- [Changelog](#changelog)

## ✨ Features

### Core Functionality
- **CSV Import**: Upload CSV files to bulk add products to cart
- **Multiple Identifier Types**: Support for Product ID, SKU, Slug, Title, and Custom Meta Fields
- **Stock Validation**: Automatic stock checking and validation
- **Variation Support**: Handle product variations seamlessly
- **Import History**: Track all import activities with detailed logs

### Advanced Features
- **Configurable Columns**: Customize CSV column headers to match your data
- **Debug Mode**: Detailed processing information for troubleshooting
- **Error Handling**: Comprehensive error reporting with row-specific details
- **Security**: Nonce verification and file type validation
- **User Management**: Role-based access control

### User Experience
- **Shortcode Integration**: Easy integration with `[bulk_add_to_cart]` shortcode
- **Admin Interface**: Clean WordPress admin interface
- **Import History**: View detailed import logs and results
- **Documentation**: Built-in documentation and FAQ pages

## 🔧 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **WooCommerce**: 3.0 or higher
- **Browser**: Modern browser with JavaScript enabled

## 📦 Installation

1. **Upload Plugin:**
   ```bash
   # Upload bulk-add-to-cart.php to your WordPress plugins directory
   wp-content/plugins/bulk-add-to-cart/
   ```

2. **Activate Plugin:**
   - Go to **WordPress Admin → Plugins**
   - Find "Bulk Add to Cart" and click "Activate"

3. **Configure Settings:**
   - Go to **Bulk Add to Cart → Settings**
   - Configure your CSV column headers and identifier types
   - Save settings

## 🚀 Quick Start

### 1. Basic Setup

1. **Add Upload Form:**
   ```php
   // Add to any page or post
   [bulk_add_to_cart]
   ```

2. **Prepare CSV File:**
   ```csv
   product_id,quantity
   123,2
   456,1
   789,3
   ```

3. **Upload and Process:**
   - Navigate to the page with the upload form
   - Select your CSV file
   - Click "Add to Cart"
   - Review the results

### 2. Advanced Configuration

Configure different identifier types based on your needs:

| Type | Description | CSV Example |
|------|-------------|-------------|
| `product_id` | Product ID | `123` |
| `product_sku` | Product SKU | `ABC-123` |
| `product_slug` | Product slug | `my-product` |
| `product_title` | Product title | `My Product Name` |
| `meta_field` | Custom meta field | `CUSTOM-001` |

## 📚 Documentation

### Complete Documentation

- **[API Documentation](API_DOCUMENTATION.md)** - Complete API reference
- **[Function Reference](FUNCTION_REFERENCE.md)** - Detailed function documentation
- **[Usage Guide](USAGE_GUIDE.md)** - Comprehensive usage instructions

### Key Documentation Sections

#### API Documentation
- Plugin constants and configuration
- Admin menu functions
- Settings management
- Shortcode functions
- File processing functions
- Utility functions
- Admin page functions
- Hooks and filters
- Usage examples
- Error handling

#### Function Reference
- Complete function documentation
- Parameters and return values
- Hook information
- Code examples
- Security considerations
- Performance optimization

#### Usage Guide
- Quick start guide
- Detailed configuration
- Usage examples
- Error handling and troubleshooting
- Advanced usage
- Best practices

## 💡 Usage Examples

### Example 1: Product ID Import

**CSV File (`products.csv`):**
```csv
product_id,quantity
123,2
456,1
789,3
```

**Settings:**
- Identifier Column: `product_id`
- Identifier Type: `product_id`
- Quantity Column: `quantity`

### Example 2: SKU-Based Import

**CSV File (`inventory.csv`):**
```csv
sku,quantity
ABC-123,2
DEF-456,1
GHI-789,3
```

**Settings:**
- Identifier Column: `sku`
- Identifier Type: `product_sku`
- Quantity Column: `quantity`

### Example 3: Custom Meta Field

**CSV File (`custom_products.csv`):**
```csv
custom_id,quantity
CUSTOM-001,2
CUSTOM-002,1
CUSTOM-003,3
```

**Settings:**
- Identifier Column: `custom_id`
- Identifier Type: `meta_field`
- Meta Field Name: `custom_product_id`
- Quantity Column: `quantity`

## ⚙️ Configuration

### Settings Page

Access settings at **WordPress Admin → Bulk Add to Cart → Settings**

#### Available Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Redirect to Cart | Redirect to cart after import | Disabled |
| Identifier Column | CSV column name for product identifier | `product_id` |
| Identifier Type | Type of identifier to use | `product_id` |
| Meta Field Name | Custom meta field name | Empty |
| Quantity Column | CSV column name for quantity | `quantity` |
| Debug Mode | Enable detailed processing information | Disabled |

### CSV Format Requirements

1. **Headers Required:** CSV must include column headers
2. **Column Flexibility:** Columns can be in any order
3. **Data Validation:** Empty identifiers or invalid quantities are skipped
4. **File Type:** Only CSV files are supported

## 🔍 Troubleshooting

### Common Issues

#### 1. "Product not found" Errors
- Verify product exists in WooCommerce
- Check identifier type setting
- Ensure CSV column headers match settings

#### 2. "Insufficient stock" Errors
- Check product stock levels
- Verify stock management is enabled
- Reduce requested quantities

#### 3. "Security check failed" Errors
- Ensure form is submitted from correct page
- Check for JavaScript conflicts
- Clear browser cache

#### 4. "Invalid CSV format" Errors
- Ensure file is actually CSV format
- Check for empty rows at beginning
- Verify column headers match settings

### Debug Mode

Enable debug mode in settings to see:
- CSV headers detected
- Column indices found
- Product lookup results
- Processing details for each row

### Import History

View detailed import history at **Bulk Add to Cart → Settings**:
- Date/time of imports
- User who performed import
- Success and error counts
- Detailed error messages

## 🛠️ Support

### Getting Help

1. **Check Documentation:**
   - Review built-in documentation page
   - Consult usage guide
   - Check FAQ section

2. **Enable Debug Mode:**
   - Enable debug mode for detailed information
   - Review debug output
   - Check processing details

3. **Review Import History:**
   - Check recent imports
   - Review error details
   - Monitor success rates

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

## 📝 Changelog

### Version 1.1.1 (April 1, 2024)
- Added support for Custom Meta Field Value as an identifier type
- Added Meta Field Name setting for custom meta field identification
- Updated documentation and FAQ with new identifier type information

### Version 1.1.0 (April 1, 2024)
- Added detailed success and error reporting with product names and quantities
- Added debug mode setting to show detailed processing information
- Improved error messages to show actual values that caused validation errors
- Added support for columns in any order in the CSV file
- Enhanced import history with detailed success and error information
- Updated documentation and FAQ with new features and improvements

### Version 1.0.1 (April 1, 2024)
- Added configurable redirect to cart setting
- Improved settings page layout

### Version 1.0.0 (April 1, 2024)
- Initial release
- Added CSV upload functionality for bulk adding products to cart
- Added support for product identification by ID, SKU, slug, or title
- Added variation support
- Added inventory checking
- Added import history tracking
- Added comprehensive documentation

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 👨‍💻 Author

**Grice AI**  
Website: https://imprintengine.com

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For support and questions:
- Check the documentation pages in the plugin admin
- Review the troubleshooting section
- Enable debug mode for detailed information

---

**Note:** This plugin requires WooCommerce to be installed and activated for full functionality.