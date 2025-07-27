<?php
/**
 * Plugin Name: Bulk Add to Cart
 * Plugin URI: 
 * Description: A powerful WordPress plugin that allows users to bulk add products to their WooCommerce cart using CSV files. Features include support for product IDs, SKUs, slugs, and titles, variation handling, inventory checking, and import history tracking. Perfect for bulk orders and inventory management.
 * Version: 1.2.0
 * Author: Grice AI
 * Author URI: https://imprintengine.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bulk-add-to-cart
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('BULK_ADD_TO_CART_VERSION', '1.2.0');
define('BULK_ADD_TO_CART_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BULK_ADD_TO_CART_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BULK_ADD_TO_CART_UPLOAD_DIR', WP_CONTENT_DIR . '/bulk-add-to-cart-import-files/');

// Create upload directory if it doesn't exist
if (!file_exists(BULK_ADD_TO_CART_UPLOAD_DIR)) {
    wp_mkdir_p(BULK_ADD_TO_CART_UPLOAD_DIR);
}

// Add menu items to WordPress admin
function bulk_add_to_cart_add_admin_menu() {
    add_menu_page(
        __('Bulk Add to Cart', 'bulk-add-to-cart'),
        __('Bulk Add to Cart', 'bulk-add-to-cart'),
        'manage_options',
        'bulk-add-to-cart',
        'bulk_add_to_cart_settings_page',
        'dashicons-cart',
        30
    );
    
    add_submenu_page(
        'bulk-add-to-cart',
        __('Settings', 'bulk-add-to-cart'),
        __('Settings', 'bulk-add-to-cart'),
        'manage_options',
        'bulk-add-to-cart',
        'bulk_add_to_cart_settings_page'
    );

    add_submenu_page(
        'bulk-add-to-cart',
        __('Documentation', 'bulk-add-to-cart'),
        __('Documentation', 'bulk-add-to-cart'),
        'manage_options',
        'bulk-add-to-cart-docs',
        'bulk_add_to_cart_documentation_page'
    );

    add_submenu_page(
        'bulk-add-to-cart',
        __('Changelog', 'bulk-add-to-cart'),
        __('Changelog', 'bulk-add-to-cart'),
        'manage_options',
        'bulk-add-to-cart-changelog',
        'bulk_add_to_cart_changelog_page'
    );
}
add_action('admin_menu', 'bulk_add_to_cart_add_admin_menu');

// Register settings
function bulk_add_to_cart_register_settings() {
    register_setting('bulk_add_to_cart_options', 'bulk_add_to_cart_settings');
    
    add_settings_section(
        'bulk_add_to_cart_main_section',
        __('Main Settings', 'bulk-add-to-cart'),
        'bulk_add_to_cart_section_callback',
        'bulk_add_to_cart'
    );

    add_settings_field(
        'redirect_to_cart',
        __('Redirect to Cart', 'bulk-add-to-cart'),
        'bulk_add_to_cart_redirect_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );

    add_settings_field(
        'identifier_column',
        __('Product Identifier Column', 'bulk-add-to-cart'),
        'bulk_add_to_cart_identifier_column_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );

    add_settings_field(
        'identifier_type',
        __('Identifier Type', 'bulk-add-to-cart'),
        'bulk_add_to_cart_identifier_type_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );

    add_settings_field(
        'meta_field_name',
        __('Meta Field Name', 'bulk-add-to-cart'),
        'bulk_add_to_cart_meta_field_name_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );

    add_settings_field(
        'quantity_column',
        __('Quantity Column', 'bulk-add-to-cart'),
        'bulk_add_to_cart_quantity_column_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );

    add_settings_field(
        'debug_mode',
        __('Debug Mode', 'bulk-add-to-cart'),
        'bulk_add_to_cart_debug_mode_callback',
        'bulk_add_to_cart',
        'bulk_add_to_cart_main_section'
    );
}
add_action('admin_init', 'bulk_add_to_cart_register_settings');

// Section callback
function bulk_add_to_cart_section_callback() {
    echo '<p>' . __('Configure the main settings for the Bulk Add to Cart plugin.', 'bulk-add-to-cart') . '</p>';
}

// Redirect setting callback
function bulk_add_to_cart_redirect_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $redirect = isset($options['redirect_to_cart']) ? $options['redirect_to_cart'] : '1';
    ?>
    <label>
        <input type="checkbox" name="bulk_add_to_cart_settings[redirect_to_cart]" value="1" <?php checked('1', $redirect); ?>>
        <?php _e('Redirect to cart page after processing CSV file', 'bulk-add-to-cart'); ?>
    </label>
    <p class="description"><?php _e('When enabled, users will be automatically redirected to the cart page after adding items.', 'bulk-add-to-cart'); ?></p>
    <?php
}

// Identifier column setting callback
function bulk_add_to_cart_identifier_column_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
    ?>
    <input type="text" name="bulk_add_to_cart_settings[identifier_column]" value="<?php echo esc_attr($identifier_column); ?>" class="regular-text">
    <p class="description"><?php _e('Enter the exact column header name from your CSV file that contains the product identifier.', 'bulk-add-to-cart'); ?></p>
    <?php
}

// Identifier type setting callback
function bulk_add_to_cart_identifier_type_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
    ?>
    <select name="bulk_add_to_cart_settings[identifier_type]" class="regular-text" id="identifier_type">
        <option value="product_id" <?php selected($identifier_type, 'product_id'); ?>><?php _e('Product ID', 'bulk-add-to-cart'); ?></option>
        <option value="product_sku" <?php selected($identifier_type, 'product_sku'); ?>><?php _e('Product SKU', 'bulk-add-to-cart'); ?></option>
        <option value="product_slug" <?php selected($identifier_type, 'product_slug'); ?>><?php _e('Product Slug', 'bulk-add-to-cart'); ?></option>
        <option value="product_title" <?php selected($identifier_type, 'product_title'); ?>><?php _e('Product Title', 'bulk-add-to-cart'); ?></option>
        <option value="meta_field" <?php selected($identifier_type, 'meta_field'); ?>><?php _e('Custom Meta Field Value', 'bulk-add-to-cart'); ?></option>
    </select>
    <p class="description"><?php _e('Select how products should be identified in the CSV file.', 'bulk-add-to-cart'); ?></p>
    <script>
        jQuery(document).ready(function($) {
            function toggleMetaField() {
                if ($('#identifier_type').val() === 'meta_field') {
                    $('#meta_field_name_row').show();
                } else {
                    $('#meta_field_name_row').hide();
                }
            }
            toggleMetaField();
            $('#identifier_type').change(toggleMetaField);
        });
    </script>
    <?php
}

// Meta field name setting callback
function bulk_add_to_cart_meta_field_name_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $meta_field_name = isset($options['meta_field_name']) ? $options['meta_field_name'] : '';
    ?>
    <input type="text" name="bulk_add_to_cart_settings[meta_field_name]" value="<?php echo esc_attr($meta_field_name); ?>" class="regular-text">
    <p class="description"><?php _e('Enter the name of the custom meta field that contains the unique identifier.', 'bulk-add-to-cart'); ?></p>
    <?php
}

// Quantity column setting callback
function bulk_add_to_cart_quantity_column_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
    ?>
    <input type="text" name="bulk_add_to_cart_settings[quantity_column]" value="<?php echo esc_attr($quantity_column); ?>" class="regular-text">
    <p class="description"><?php _e('Enter the exact column header name from your CSV file that contains the quantity.', 'bulk-add-to-cart'); ?></p>
    <?php
}

// Debug mode setting callback
function bulk_add_to_cart_debug_mode_callback() {
    $options = get_option('bulk_add_to_cart_settings');
    $debug_mode = isset($options['debug_mode']) ? $options['debug_mode'] : '0';
    ?>
    <label>
        <input type="checkbox" name="bulk_add_to_cart_settings[debug_mode]" value="1" <?php checked('1', $debug_mode); ?>>
        <?php _e('Enable debug mode to show detailed processing information', 'bulk-add-to-cart'); ?>
    </label>
    <p class="description"><?php _e('When enabled, detailed information about the CSV processing will be displayed.', 'bulk-add-to-cart'); ?></p>
    <?php
}

// Add shortcode for bulk upload form
function bulk_add_to_cart_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>' . __('Please log in to use the bulk add to cart feature.', 'bulk-add-to-cart') . '</p>';
    }

    // Get current settings
    $options = get_option('bulk_add_to_cart_settings');
    $identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
    $identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
    $quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';

    $output = '<div class="bulk-add-to-cart-form" style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
    
    // Debug information
    if (isset($_POST['bulk_add_to_cart_submit'])) {
        $output .= '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #dc3545;">';
        $output .= '<h3 style="margin-top: 0; color: #dc3545;">' . __('Debug Information', 'bulk-add-to-cart') . '</h3>';
        
        // Check if form was submitted
        $output .= '<p><strong>' . __('Form Submission:', 'bulk-add-to-cart') . '</strong> ';
        $output .= isset($_POST['bulk_add_to_cart_submit']) ? __('Yes', 'bulk-add-to-cart') : __('No', 'bulk-add-to-cart');
        $output .= '</p>';

        // Check nonce
        $output .= '<p><strong>' . __('Nonce Verification:', 'bulk-add-to-cart') . '</strong> ';
        $output .= isset($_POST['bulk_add_to_cart_nonce']) && wp_verify_nonce($_POST['bulk_add_to_cart_nonce'], 'bulk_add_to_cart_upload') ? __('Valid', 'bulk-add-to-cart') : __('Invalid', 'bulk-add-to-cart');
        $output .= '</p>';

        // Check file upload
        $output .= '<p><strong>' . __('File Upload:', 'bulk-add-to-cart') . '</strong> ';
        if (isset($_FILES['csv_file'])) {
            $output .= __('File received', 'bulk-add-to-cart') . ' (' . esc_html($_FILES['csv_file']['name']) . ')';
            if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $output .= ' - ' . __('Error: ', 'bulk-add-to-cart') . esc_html($_FILES['csv_file']['error']);
            }
        } else {
            $output .= __('No file received', 'bulk-add-to-cart');
        }
        $output .= '</p>';

        // Check WooCommerce cart
        $output .= '<p><strong>' . __('WooCommerce Cart:', 'bulk-add-to-cart') . '</strong> ';
        $output .= WC()->cart ? __('Initialized', 'bulk-add-to-cart') : __('Not initialized', 'bulk-add-to-cart');
        $output .= '</p>';

        // Check current settings
        $output .= '<p><strong>' . __('Current Settings:', 'bulk-add-to-cart') . '</strong></p>';
        $output .= '<ul>';
        $output .= '<li>' . __('Identifier Column:', 'bulk-add-to-cart') . ' ' . esc_html($identifier_column) . '</li>';
        $output .= '<li>' . __('Identifier Type:', 'bulk-add-to-cart') . ' ' . esc_html($identifier_type) . '</li>';
        $output .= '<li>' . __('Quantity Column:', 'bulk-add-to-cart') . ' ' . esc_html($quantity_column) . '</li>';
        $output .= '</ul>';

        $output .= '</div>';
    }
    
    // Instructions
    $output .= '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba;">';
    $output .= '<h3 style="margin-top: 0;">' . __('How to Use', 'bulk-add-to-cart') . '</h3>';
    $output .= '<ol style="margin: 0; padding-left: 20px;">';
    $output .= '<li>' . __('Prepare a CSV file with these columns (in any order):', 'bulk-add-to-cart') . '</li>';
    $output .= '<ul style="margin: 10px 0;">';
    $output .= '<li>' . sprintf(__('"%s" - Contains the product %s', 'bulk-add-to-cart'), 
        esc_html($identifier_column),
        esc_html($identifier_type)
    ) . '</li>';
    $output .= '<li>' . sprintf(__('"%s" - Contains the quantity (required)', 'bulk-add-to-cart'), 
        esc_html($quantity_column)
    ) . '</li>';
    $output .= '</ul>';
    $output .= '<li>' . sprintf(__('For variations, use the variation %s', 'bulk-add-to-cart'), 
        $identifier_type === 'product_id' ? __('ID', 'bulk-add-to-cart') : 
        ($identifier_type === 'product_sku' ? __('SKU', 'bulk-add-to-cart') : 
        ($identifier_type === 'product_slug' ? __('slug', 'bulk-add-to-cart') : 
        __('title', 'bulk-add-to-cart')))
    ) . '</li>';
    $output .= '<li>' . __('Upload your CSV file and click "Add to Cart"', 'bulk-add-to-cart') . '</li>';
    $output .= '</ol>';
    $output .= '</div>';

    // Loading bar container (initially hidden)
    $output .= '<div id="bulk-add-loading" style="display: none; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">';
    $output .= '<h3 style="margin-top: 0; color: #007cba;">' . __('Processing Products...', 'bulk-add-to-cart') . '</h3>';
    $output .= '<div style="margin-bottom: 10px;">';
    $output .= '<div id="bulk-add-progress-bar" style="width: 100%; height: 20px; background-color: #e0e0e0; border-radius: 10px; overflow: hidden;">';
    $output .= '<div id="bulk-add-progress-fill" style="width: 0%; height: 100%; background: linear-gradient(90deg, #007cba, #005a87); transition: width 0.3s ease;"></div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<div id="bulk-add-progress-text" style="text-align: center; font-weight: bold; color: #007cba;">0%</div>';
    $output .= '<div id="bulk-add-current-product" style="margin-top: 10px; text-align: center; color: #666; font-style: italic;"></div>';
    $output .= '</div>';

    // Results container (initially hidden)
    $output .= '<div id="bulk-add-results" style="display: none; margin-bottom: 20px; padding: 15px; border-radius: 4px;"></div>';

    // Form
    $output .= '<form id="bulk-add-form" method="post" enctype="multipart/form-data" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
    $output .= wp_nonce_field('bulk_add_to_cart_upload', 'bulk_add_to_cart_nonce', true, false);
    $output .= '<div style="margin-bottom: 20px;">';
    $output .= '<label for="csv_file" style="display: block; margin-bottom: 10px; font-weight: bold;">' . __('Select CSV File:', 'bulk-add-to-cart') . '</label>';
    $output .= '<input type="file" name="csv_file" id="csv_file" accept=".csv" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
    $output .= '</div>';
    $output .= '<button type="submit" name="bulk_add_to_cart_submit" id="bulk-add-submit" class="button button-primary" style="padding: 10px 20px;">' . __('Add to Cart', 'bulk-add-to-cart') . '</button>';
    $output .= '</form>';

    // JavaScript for AJAX processing with enhanced progress tracking
    $output .= '<script type="text/javascript">
    jQuery(document).ready(function($) {
        $("#bulk-add-form").on("submit", function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append("action", "bulk_add_to_cart_ajax");
            formData.append("ajax_nonce", "' . wp_create_nonce('bulk_add_to_cart_ajax') . '");
            
            // Show loading bar
            $("#bulk-add-loading").show();
            $("#bulk-add-results").hide();
            $("#bulk-add-submit").prop("disabled", true).text("' . __('Processing...', 'bulk-add-to-cart') . '");
            
            // Reset progress
            $("#bulk-add-progress-fill").css("width", "0%");
            $("#bulk-add-progress-text").text("0%");
            $("#bulk-add-current-product").text("' . __('Preparing to process...', 'bulk-add-to-cart') . '");
            
            // Start progress animation
            var progressInterval = setInterval(function() {
                var currentWidth = parseInt($("#bulk-add-progress-fill").css("width"));
                var maxWidth = $("#bulk-add-progress-bar").width();
                var increment = maxWidth * 0.01; // 1% increment
                
                if (currentWidth < maxWidth * 0.95) { // Stop at 95% until completion
                    var newWidth = currentWidth + increment;
                    var percentage = Math.round((newWidth / maxWidth) * 100);
                    $("#bulk-add-progress-fill").css("width", newWidth + "px");
                    $("#bulk-add-progress-text").text(percentage + "%");
                }
            }, 100);
            
            $.ajax({
                url: "' . admin_url('admin-ajax.php') . '",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#bulk-add-current-product").text("' . __('Uploading file...', 'bulk-add-to-cart') . '");
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            var progressWidth = percentComplete * 100;
                            $("#bulk-add-progress-fill").css("width", progressWidth + "%");
                            $("#bulk-add-progress-text").text(Math.round(progressWidth) + "%");
                            
                            if (percentComplete < 0.5) {
                                $("#bulk-add-current-product").text("' . __('Uploading file...', 'bulk-add-to-cart') . '");
                            } else if (percentComplete < 0.8) {
                                $("#bulk-add-current-product").text("' . __('Processing CSV data...', 'bulk-add-to-cart') . '");
                            } else {
                                $("#bulk-add-current-product").text("' . __('Adding products to cart...', 'bulk-add-to-cart') . '");
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    clearInterval(progressInterval);
                    
                    // Complete the progress bar
                    $("#bulk-add-progress-fill").css("width", "100%");
                    $("#bulk-add-progress-text").text("100%");
                    $("#bulk-add-current-product").text("' . __('Processing complete!', 'bulk-add-to-cart') . '");
                    
                    setTimeout(function() {
                        if (response.success) {
                            var results = response.data;
                            var resultsHtml = "<div style=\\"padding: 15px; border-radius: 4px;\\">";
                            
                            if (results.success_count > 0) {
                                resultsHtml += "<div style=\\"margin-bottom: 15px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;\\">";
                                resultsHtml += "<strong>" + results.success_count + " " + (results.success_count === 1 ? "' . __('product added to cart.', 'bulk-add-to-cart') . '" : "' . __('products added to cart.', 'bulk-add-to-cart') . '") + "</strong>";
                                if (results.successful_additions && Object.keys(results.successful_additions).length > 0) {
                                    resultsHtml += "<ul style=\\"margin: 10px 0 0 20px;\\">";
                                    for (var product in results.successful_additions) {
                                        resultsHtml += "<li>" + product + ": " + results.successful_additions[product] + "</li>";
                                    }
                                    resultsHtml += "</ul>";
                                }
                                resultsHtml += "</div>";
                            }
                            
                            if (results.error_count > 0) {
                                resultsHtml += "<div style=\\"padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;\\">";
                                resultsHtml += "<strong>" + results.error_count + " " + (results.error_count === 1 ? "' . __('product could not be added.', 'bulk-add-to-cart') . '" : "' . __('products could not be added.', 'bulk-add-to-cart') . '") + "</strong>";
                                if (results.errors && results.errors.length > 0) {
                                    resultsHtml += "<ul style=\\"margin: 10px 0 0 20px;\\">";
                                    for (var i = 0; i < results.errors.length; i++) {
                                        resultsHtml += "<li>" + results.errors[i] + "</li>";
                                    }
                                    resultsHtml += "</ul>";
                                }
                                resultsHtml += "</div>";
                            }
                            
                            resultsHtml += "</div>";
                            $("#bulk-add-results").html(resultsHtml).show();
                            
                            // Redirect if enabled
                            if (results.redirect_url) {
                                setTimeout(function() {
                                    window.location.href = results.redirect_url;
                                }, 2000);
                            }
                        } else {
                            $("#bulk-add-results").html("<div style=\\"padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;\\"><strong>' . __('Error:', 'bulk-add-to-cart') . '</strong> " + response.data + "</div>").show();
                        }
                    }, 500);
                },
                error: function() {
                    clearInterval(progressInterval);
                    $("#bulk-add-results").html("<div style=\\"padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;\\"><strong>' . __('Error:', 'bulk-add-to-cart') . '</strong> ' . __('An error occurred while processing the file.', 'bulk-add-to-cart') . '</div>").show();
                },
                complete: function() {
                    setTimeout(function() {
                        $("#bulk-add-loading").hide();
                        $("#bulk-add-submit").prop("disabled", false).text("' . __('Add to Cart', 'bulk-add-to-cart') . '");
                    }, 1000);
                }
            });
        });
    });
    </script>';

    $output .= '</div>';

    return $output;
}
add_shortcode('bulk_add_to_cart', 'bulk_add_to_cart_shortcode');

// Reorder WooCommerce notices to show success before error
function bulk_add_to_cart_reorder_notices($notices) {
    if (empty($notices)) {
        return $notices;
    }

    // Separate notices by type
    $success_notices = array();
    $error_notices = array();
    $other_notices = array();

    foreach ($notices as $notice) {
        if (isset($notice['type'])) {
            if ($notice['type'] === 'success') {
                $success_notices[] = $notice;
            } elseif ($notice['type'] === 'error') {
                $error_notices[] = $notice;
            } else {
                $other_notices[] = $notice;
            }
        }
    }

    // Recombine notices with success first, then others, then errors
    return array_merge($success_notices, $other_notices, $error_notices);
}
add_filter('woocommerce_get_notices', 'bulk_add_to_cart_reorder_notices', 20);

// AJAX handler for bulk add to cart with progress updates
function bulk_add_to_cart_ajax_handler() {
    // Verify nonce
    if (!isset($_POST['ajax_nonce']) || !wp_verify_nonce($_POST['ajax_nonce'], 'bulk_add_to_cart_ajax')) {
        wp_send_json_error(__('Security check failed. Please try again.', 'bulk-add-to-cart'));
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('Please log in to use the bulk add to cart feature.', 'bulk-add-to-cart'));
    }

    // Check file upload
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(__('Error uploading file. Please try again.', 'bulk-add-to-cart'));
    }

    // Initialize WooCommerce cart if not already done
    if (!WC()->cart) {
        WC()->cart = new WC_Cart();
    }

    $file = $_FILES['csv_file'];
    $filename = sanitize_file_name($file['name']);
    $timestamp = current_time('timestamp');
    $new_filename = $timestamp . '-' . $filename;
    $upload_path = BULK_ADD_TO_CART_UPLOAD_DIR . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        wp_send_json_error(__('Error saving file. Please try again.', 'bulk-add-to-cart'));
    }

    // Process CSV file
    $handle = fopen($upload_path, 'r');
    if (!$handle) {
        wp_send_json_error(__('Error reading file. Please try again.', 'bulk-add-to-cart'));
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        wp_send_json_error(__('Invalid CSV format. Please check the file structure.', 'bulk-add-to-cart'));
    }

    // Get settings
    $options = get_option('bulk_add_to_cart_settings');
    $identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
    $identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
    $quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
    $debug_mode = isset($options['debug_mode']) ? $options['debug_mode'] : '0';

    // Find column indices
    $identifier_index = array_search(strtolower($identifier_column), array_map('strtolower', $headers));
    $quantity_index = array_search(strtolower($quantity_column), array_map('strtolower', $headers));

    if ($identifier_index === false || $quantity_index === false) {
        fclose($handle);
        wp_send_json_error(sprintf(
            __('Required columns not found. Looking for "%s" and "%s".', 'bulk-add-to-cart'),
            $identifier_column,
            $quantity_column
        ));
    }

    // Count total rows for progress calculation
    $total_rows = 0;
    $temp_handle = fopen($upload_path, 'r');
    fgetcsv($temp_handle); // Skip header
    while (fgetcsv($temp_handle) !== false) {
        $total_rows++;
    }
    fclose($temp_handle);

    // Process rows
    $success_count = 0;
    $error_count = 0;
    $errors = array();
    $successful_additions = array();
    $row_number = 1;
    $processed_rows = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $row_number++;
        $processed_rows++;
        $identifier = trim($row[$identifier_index]);
        $quantity = intval($row[$quantity_index]);

        if (empty($identifier) || $quantity <= 0) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Invalid identifier or quantity (Identifier: "%s", Quantity: "%s")', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($row[$quantity_index])
            );
            continue;
        }

        $product = null;
        switch ($identifier_type) {
            case 'product_id':
                $product = wc_get_product($identifier);
                break;
            case 'product_sku':
                $product_id = wc_get_product_id_by_sku($identifier);
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'product_slug':
                $product_id = wc_get_product_id_by_slug($identifier);
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'product_title':
                global $wpdb;
                $product_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'product'",
                    $identifier
                ));
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'meta_field':
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
                break;
        }

        if (!$product) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Product not found: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
            continue;
        }

        if (!$product->is_purchasable()) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Product not purchasable: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
            continue;
        }

        // Check stock
        if ($product->managing_stock() && !$product->has_enough_stock($quantity)) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Insufficient stock for: %s (Requested: %s, Available: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity),
                esc_html($product->get_stock_quantity())
            );
            continue;
        }

        // Add to cart
        $cart_item_key = WC()->cart->add_to_cart($product->get_id(), $quantity);
        if ($cart_item_key) {
            $success_count++;
            // Track successful additions
            $product_name = $product->get_name();
            if (!isset($successful_additions[$product_name])) {
                $successful_additions[$product_name] = 0;
            }
            $successful_additions[$product_name] += $quantity;
        } else {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Failed to add to cart: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
        }
    }

    fclose($handle);

    // Record import history
    $current_user = wp_get_current_user();
    $history = get_option('bulk_add_to_cart_history', array());
    array_unshift($history, array(
        'timestamp' => current_time('mysql'),
        'user_id' => $current_user->ID,
        'username' => $current_user->user_login,
        'filename' => $new_filename,
        'success_count' => $success_count,
        'error_count' => $error_count,
        'errors' => $errors,
        'successes' => $successful_additions
    ));
    $history = array_slice($history, 0, 100); // Keep only last 100 entries
    update_option('bulk_add_to_cart_history', $history);

    // Prepare response
    $response = array(
        'success_count' => $success_count,
        'error_count' => $error_count,
        'errors' => $errors,
        'successful_additions' => $successful_additions,
        'total_processed' => $processed_rows
    );

    // Add redirect URL if enabled
    $options = get_option('bulk_add_to_cart_settings');
    if (isset($options['redirect_to_cart']) && $options['redirect_to_cart'] === '1') {
        $response['redirect_url'] = wc_get_cart_url();
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_bulk_add_to_cart_ajax', 'bulk_add_to_cart_ajax_handler');
add_action('wp_ajax_nopriv_bulk_add_to_cart_ajax', 'bulk_add_to_cart_ajax_handler');

// Handle CSV upload and processing (legacy function for non-AJAX fallback)
function bulk_add_to_cart_process_upload() {
    // Only process on frontend
    if (is_admin()) {
        return;
    }

    // Check if this is a form submission
    if (!isset($_POST['bulk_add_to_cart_submit'])) {
        return;
    }

    if (!isset($_POST['bulk_add_to_cart_nonce']) || !wp_verify_nonce($_POST['bulk_add_to_cart_nonce'], 'bulk_add_to_cart_upload')) {
        wc_add_notice(__('Security check failed. Please try again.', 'bulk-add-to-cart'), 'error');
        return;
    }

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        wc_add_notice(__('Error uploading file. Please try again.', 'bulk-add-to-cart'), 'error');
        return;
    }

    // Initialize WooCommerce cart if not already done
    if (!WC()->cart) {
        WC()->cart = new WC_Cart();
    }

    $file = $_FILES['csv_file'];
    $filename = sanitize_file_name($file['name']);
    $timestamp = current_time('timestamp');
    $new_filename = $timestamp . '-' . $filename;
    $upload_path = BULK_ADD_TO_CART_UPLOAD_DIR . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        wc_add_notice(__('Error saving file. Please try again.', 'bulk-add-to-cart'), 'error');
        return;
    }

    // Process CSV file
    $handle = fopen($upload_path, 'r');
    if (!$handle) {
        wc_add_notice(__('Error reading file. Please try again.', 'bulk-add-to-cart'), 'error');
        return;
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        wc_add_notice(__('Invalid CSV format. Please check the file structure.', 'bulk-add-to-cart'), 'error');
        return;
    }

    // Get settings
    $options = get_option('bulk_add_to_cart_settings');
    $identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
    $identifier_type = isset($options['identifier_type']) ? $options['identifier_type'] : 'product_id';
    $quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
    $debug_mode = isset($options['debug_mode']) ? $options['debug_mode'] : '0';

    // Show debug information if debug mode is enabled
    if ($debug_mode === '1') {
        wc_add_notice('CSV Headers: ' . implode(', ', $headers), 'notice');
        wc_add_notice('Using settings - Identifier Column: ' . $identifier_column . ', Type: ' . $identifier_type . ', Quantity Column: ' . $quantity_column, 'notice');
    }

    // Find column indices
    $identifier_index = array_search(strtolower($identifier_column), array_map('strtolower', $headers));
    $quantity_index = array_search(strtolower($quantity_column), array_map('strtolower', $headers));

    if ($debug_mode === '1') {
        wc_add_notice('Column indices - Identifier: ' . ($identifier_index !== false ? $identifier_index : 'not found') . ', Quantity: ' . ($quantity_index !== false ? $quantity_index : 'not found'), 'notice');
    }

    if ($identifier_index === false || $quantity_index === false) {
        fclose($handle);
        wc_add_notice(sprintf(
            __('Required columns not found. Looking for "%s" and "%s".', 'bulk-add-to-cart'),
            $identifier_column,
            $quantity_column
        ), 'error');
        return;
    }

    // Process rows
    $success_count = 0;
    $error_count = 0;
    $errors = array();
    $successful_additions = array();
    $row_number = 1;

    while (($row = fgetcsv($handle)) !== false) {
        $row_number++;
        $identifier = trim($row[$identifier_index]);
        $quantity = intval($row[$quantity_index]);

        if ($debug_mode === '1') {
            wc_add_notice('Processing row ' . $row_number . ' - Identifier: ' . $identifier . ', Quantity: ' . $quantity, 'notice');
        }

        if (empty($identifier) || $quantity <= 0) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Invalid identifier or quantity (Identifier: "%s", Quantity: "%s")', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($row[$quantity_index])
            );
            continue;
        }

        $product = null;
        switch ($identifier_type) {
            case 'product_id':
                $product = wc_get_product($identifier);
                break;
            case 'product_sku':
                $product_id = wc_get_product_id_by_sku($identifier);
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'product_slug':
                $product_id = wc_get_product_id_by_slug($identifier);
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'product_title':
                global $wpdb;
                $product_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'product'",
                    $identifier
                ));
                $product = $product_id ? wc_get_product($product_id) : null;
                break;
            case 'meta_field':
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
                break;
        }

        if ($debug_mode === '1') {
            wc_add_notice('Product lookup for ' . $identifier . ': ' . ($product ? 'Found' : 'Not found'), 'notice');
        }

        if (!$product) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Product not found: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
            continue;
        }

        if (!$product->is_purchasable()) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Product not purchasable: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
            continue;
        }

        // Check stock
        if ($product->managing_stock() && !$product->has_enough_stock($quantity)) {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Insufficient stock for: %s (Requested: %s, Available: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity),
                esc_html($product->get_stock_quantity())
            );
            continue;
        }

        // Add to cart
        $cart_item_key = WC()->cart->add_to_cart($product->get_id(), $quantity);
        if ($cart_item_key) {
            $success_count++;
            // Track successful additions
            $product_name = $product->get_name();
            if (!isset($successful_additions[$product_name])) {
                $successful_additions[$product_name] = 0;
            }
            $successful_additions[$product_name] += $quantity;
            if ($debug_mode === '1') {
                wc_add_notice('Successfully added product ' . $identifier . ' to cart', 'success');
            }
        } else {
            $error_count++;
            $errors[] = sprintf(
                __('Row %d: Failed to add to cart: %s (Quantity: %s)', 'bulk-add-to-cart'),
                $row_number,
                esc_html($identifier),
                esc_html($quantity)
            );
            if ($debug_mode === '1') {
                wc_add_notice('Failed to add product ' . $identifier . ' to cart', 'error');
            }
        }
    }

    fclose($handle);

    // Add notices
    if ($success_count > 0) {
        // First show the total count
        wc_add_notice(sprintf(
            _n('%d product added to cart.', '%d products added to cart.', $success_count, 'bulk-add-to-cart'),
            $success_count
        ), 'success');

        // Then show the detailed list of successful additions
        if (!empty($successful_additions)) {
            $details = '<ul style="margin-left: 20px;">';
            foreach ($successful_additions as $product_name => $quantity) {
                $details .= sprintf('<li>%s: %d</li>', esc_html($product_name), $quantity);
            }
            $details .= '</ul>';
            wc_add_notice($details, 'success');
        }
    }

    if ($error_count > 0) {
        // First show the total count
        wc_add_notice(sprintf(
            _n('%d product could not be added.', '%d products could not be added.', $error_count, 'bulk-add-to-cart'),
            $error_count
        ), 'error');
        
        // Then show the detailed error messages
        if (!empty($errors)) {
            foreach ($errors as $error) {
                wc_add_notice($error, 'error');
            }
        }
    }

    // Record import history
    $current_user = wp_get_current_user();
    $history = get_option('bulk_add_to_cart_history', array());
    array_unshift($history, array(
        'timestamp' => current_time('mysql'),
        'user_id' => $current_user->ID,
        'username' => $current_user->user_login,
        'filename' => $new_filename,
        'success_count' => $success_count,
        'error_count' => $error_count,
        'errors' => $errors,
        'successes' => $successful_additions
    ));
    $history = array_slice($history, 0, 100); // Keep only last 100 entries
    update_option('bulk_add_to_cart_history', $history);

    // Redirect if enabled
    $options = get_option('bulk_add_to_cart_settings');
    if (isset($options['redirect_to_cart']) && $options['redirect_to_cart'] === '1') {
        wp_redirect(wc_get_cart_url());
        exit;
    }
}
// Change the hook from init to template_redirect
remove_action('init', 'bulk_add_to_cart_process_upload');
add_action('template_redirect', 'bulk_add_to_cart_process_upload');

// Settings page
function bulk_add_to_cart_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap" style="max-width: 100%;">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <span class="dashicons dashicons-admin-settings" style="margin-right: 5px;"></span>
                <?php _e('Plugin Settings', 'bulk-add-to-cart'); ?>
            </h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('bulk_add_to_cart_options');
                do_settings_sections('bulk_add_to_cart');
                submit_button();
                ?>
            </form>
        </div>

        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <span class="dashicons dashicons-backup" style="margin-right: 5px;"></span>
                <?php _e('Import History', 'bulk-add-to-cart'); ?>
            </h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date/Time', 'bulk-add-to-cart'); ?></th>
                        <th><?php _e('User', 'bulk-add-to-cart'); ?></th>
                        <th><?php _e('File', 'bulk-add-to-cart'); ?></th>
                        <th><?php _e('Success', 'bulk-add-to-cart'); ?></th>
                        <th><?php _e('Errors', 'bulk-add-to-cart'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $history = get_option('bulk_add_to_cart_history', array());
                    if (!empty($history)) {
                        foreach ($history as $entry) {
                            ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($entry['timestamp']))); ?></td>
                                <td><a href="<?php echo esc_url(get_edit_user_link($entry['user_id'])); ?>"><?php echo esc_html($entry['username']); ?></a></td>
                                <td>
                                    <a href="<?php echo esc_url(WP_CONTENT_URL . '/bulk-add-to-cart-import-files/' . $entry['filename']); ?>" target="_blank">
                                        <?php echo esc_html($entry['filename']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($entry['successes'])): ?>
                                        <button type="button" class="button button-small" onclick="alert('<?php 
                                            $success_details = array();
                                            foreach ($entry['successes'] as $product_name => $quantity) {
                                                $success_details[] = sprintf('%s: %d', esc_js($product_name), $quantity);
                                            }
                                            echo esc_js(implode("\n", $success_details));
                                        ?>')">
                                            <?php echo esc_html($entry['success_count']); ?>
                                        </button>
                                    <?php else: ?>
                                        <?php echo esc_html($entry['success_count']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($entry['errors'])): ?>
                                        <button type="button" class="button button-small" onclick="alert('<?php echo esc_js(implode("\n", $entry['errors'])); ?>')">
                                            <?php echo esc_html($entry['error_count']); ?>
                                        </button>
                                    <?php else: ?>
                                        <?php echo esc_html($entry['error_count']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5"><?php _e('No import history found.', 'bulk-add-to-cart'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e('CSV File Format', 'bulk-add-to-cart'); ?></h2>
            <p><?php _e('Your CSV file should contain at least two columns (in any order):', 'bulk-add-to-cart'); ?></p>
            <ol>
                <li><?php _e('A column for the product identifier (configured in settings)', 'bulk-add-to-cart'); ?></li>
                <li><?php _e('A column for the quantity (configured in settings)', 'bulk-add-to-cart'); ?></li>
            </ol>
            <p><?php _e('The column headers must exactly match what you configure in the settings above.', 'bulk-add-to-cart'); ?></p>
            <p><?php _e('Example CSV format:', 'bulk-add-to-cart'); ?></p>
            <pre style="background: #f8f9fa; padding: 15px; border: 1px solid #ddd;">
<?php
$options = get_option('bulk_add_to_cart_settings');
$identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
$quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
echo esc_html($identifier_column . ',' . $quantity_column . "\n");
echo esc_html('123,2' . "\n");
echo esc_html('ABC-123,1' . "\n");
?>
            </pre>
        </div>
    </div>
    <?php
}

// Documentation page
function bulk_add_to_cart_documentation_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap" style="max-width: 100%;">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <span class="dashicons dashicons-book" style="margin-right: 5px;"></span>
                <?php _e('How to Use', 'bulk-add-to-cart'); ?>
            </h2>
            <div style="margin-top: 15px;">
                <h3><?php _e('Setting Up the Upload Form', 'bulk-add-to-cart'); ?></h3>
                <ol>
                    <li><?php _e('Add the shortcode [bulk_add_to_cart] to any page or post where you want the upload form to appear', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Make sure the page/post is accessible to logged-in users', 'bulk-add-to-cart'); ?></li>
                </ol>

                <h3><?php _e('Configuring CSV Column Headers', 'bulk-add-to-cart'); ?></h3>
                <ol>
                    <li><?php _e('Go to Bulk Add to Cart > Settings in the WordPress admin', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Configure the following settings:', 'bulk-add-to-cart'); ?></li>
                    <ul>
                        <li><?php _e('Product Identifier Column: Enter the exact column header name from your CSV that contains the product identifier', 'bulk-add-to-cart'); ?></li>
                        <li><?php _e('Identifier Type: Select what type of identifier your CSV column contains (Product ID, SKU, Slug, Title, or Custom Meta Field Value)', 'bulk-add-to-cart'); ?></li>
                        <li><?php _e('Meta Field Name: If using Custom Meta Field Value, enter the name of the meta field that contains the unique identifier', 'bulk-add-to-cart'); ?></li>
                        <li><?php _e('Quantity Column: Enter the exact column header name from your CSV that contains the quantity', 'bulk-add-to-cart'); ?></li>
                        <li><?php _e('Debug Mode: Enable to see detailed processing information during imports', 'bulk-add-to-cart'); ?></li>
                    </ul>
                </ol>

                <h3><?php _e('Preparing Your CSV File', 'bulk-add-to-cart'); ?></h3>
                <ol>
                    <li><?php _e('Create a CSV file with the configured column headers (in any order)', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('The column headers must exactly match what you configured in the settings', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Example CSV format:', 'bulk-add-to-cart'); ?></li>
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
<?php
$options = get_option('bulk_add_to_cart_settings');
$identifier_column = isset($options['identifier_column']) ? $options['identifier_column'] : 'product_id';
$quantity_column = isset($options['quantity_column']) ? $options['quantity_column'] : 'quantity';
echo esc_html($identifier_column . ',' . $quantity_column . "\n");
echo esc_html('123,2' . "\n");
echo esc_html('ABC-123,1' . "\n");
?>
                    </pre>
                </ol>

                <h3><?php _e('Using the Upload Form', 'bulk-add-to-cart'); ?></h3>
                <ol>
                    <li><?php _e('Log in to your WordPress account', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Navigate to the page with the upload form', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Select your CSV file', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Click "Add to Cart"', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Review the results message showing successful additions and any errors', 'bulk-add-to-cart'); ?></li>
                </ol>

                <h3><?php _e('Viewing Import History', 'bulk-add-to-cart'); ?></h3>
                <ol>
                    <li><?php _e('Go to Bulk Add to Cart > Import History in the WordPress admin', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('View a list of all previous imports with success and error counts', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Click on the success or error count buttons to see detailed information about what was added or what went wrong', 'bulk-add-to-cart'); ?></li>
                </ol>
            </div>
        </div>

        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <span class="dashicons dashicons-editor-help" style="margin-right: 5px;"></span>
                <?php _e('FAQ', 'bulk-add-to-cart'); ?>
            </h2>
            <div style="margin-top: 15px;">
                <h3><?php _e('Q: What file format is supported?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: The plugin supports CSV files only. The file must have a .csv extension.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: Do the columns need to be in a specific order?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: No, the columns can be in any order as long as the header names match your configured settings.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: How do I handle variable products?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: For variable products, use the variation identifier (ID, SKU, slug, or title) based on your configured identifier type.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: What happens if a product is out of stock?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: The plugin will skip out-of-stock products and show an error message with details about the stock issue.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: How can I see what was successfully added to the cart?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: After processing, you\'ll see a list of all successfully added products with their quantities. You can also view this information in the Import History.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: What does debug mode do?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: Debug mode shows detailed information about the CSV processing, including headers found, column indices, and product lookup results.', 'bulk-add-to-cart'); ?></p>

                <h3><?php _e('Q: What is the Custom Meta Field Value identifier type?', 'bulk-add-to-cart'); ?></h3>
                <p><?php _e('A: This option allows you to identify products using a custom meta field value. You must specify the meta field name in the settings, and the CSV values must match the values stored in this meta field.', 'bulk-add-to-cart'); ?></p>
            </div>
        </div>
    </div>
    <?php
}

// Changelog page
function bulk_add_to_cart_changelog_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap" style="max-width: 100%;">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <span class="dashicons dashicons-update" style="margin-right: 5px;"></span>
                <?php _e('Changelog', 'bulk-add-to-cart'); ?>
            </h2>
            <div style="margin-top: 15px;">
                <h3><?php echo esc_html('Version 1.2.0'); ?> - <?php echo esc_html('April 1, 2024'); ?></h3>
                <ul>
                    <li><?php _e('Added AJAX-powered loading bar with real-time progress tracking', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Enhanced user experience with visual progress indicators during file processing', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added step-by-step progress messages (Uploading, Processing, Adding to Cart)', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Improved form submission with non-blocking AJAX processing', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added smooth progress animations and completion feedback', 'bulk-add-to-cart'); ?></li>
                </ul>

                <h3><?php echo esc_html('Version 1.1.1'); ?> - <?php echo esc_html('April 1, 2024'); ?></h3>
                <ul>
                    <li><?php _e('Added support for Custom Meta Field Value as an identifier type', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added Meta Field Name setting for custom meta field identification', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Updated documentation and FAQ with new identifier type information', 'bulk-add-to-cart'); ?></li>
                </ul>

                <h3><?php echo esc_html('Version 1.1.0'); ?> - <?php echo esc_html('April 1, 2024'); ?></h3>
                <ul>
                    <li><?php _e('Added detailed success and error reporting with product names and quantities', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added debug mode setting to show detailed processing information', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Improved error messages to show actual values that caused validation errors', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added support for columns in any order in the CSV file', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Enhanced import history with detailed success and error information', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Updated documentation and FAQ with new features and improvements', 'bulk-add-to-cart'); ?></li>
                </ul>

                <h3><?php echo esc_html('Version 1.0.1'); ?> - <?php echo esc_html('April 1, 2024'); ?></h3>
                <ul>
                    <li><?php _e('Added configurable redirect to cart setting', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Improved settings page layout', 'bulk-add-to-cart'); ?></li>
                </ul>

                <h3><?php echo esc_html('Version 1.0.0'); ?> - <?php echo esc_html('April 1, 2024'); ?></h3>
                <ul>
                    <li><?php _e('Initial release', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added CSV upload functionality for bulk adding products to cart', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added support for product identification by ID, SKU, slug, or title', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added variation support', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added inventory checking', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added import history tracking', 'bulk-add-to-cart'); ?></li>
                    <li><?php _e('Added comprehensive documentation', 'bulk-add-to-cart'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
} 