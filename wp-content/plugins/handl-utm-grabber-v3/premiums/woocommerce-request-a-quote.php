<?php

/**
 * HandL UTM Grabber V3 - WooCommerce Request a Quote Integration
 * 
 * This file integrates UTM tracking with the WooCommerce Request a Quote plugin.
 * It adds hidden UTM fields to the quote form and saves UTM data as quote meta.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if Request a Quote plugin is active
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('woocommerce-request-a-quote/class-addify-request-for-quote.php')) {
    return;
}

// Do not register any hooks during cron jobs - function definitions load later in this file
if (wp_doing_cron()) {
    return;
}

add_action('add_meta_boxes', 'handl_rfq_add_utm_meta_boxes');
add_filter('manage_addify_quote_posts_columns', 'handl_rfq_add_utm_columns');
add_action('manage_addify_quote_posts_custom_column', 'handl_rfq_display_utm_columns', 10, 2);
add_action('restrict_manage_posts', 'handl_rfq_add_utm_search_field');
add_action('pre_get_posts', 'handl_rfq_add_utm_to_search');
add_action('admin_init', 'handl_register_append_quote_meta_to_rfq');
add_filter("insert_rows_to_handl_options", "append_quote_meta_to_rfq", 10);

/**
 * Add UTM meta box to quote admin page
 */
if (!function_exists('handl_rfq_add_utm_meta_boxes')) {
    function handl_rfq_add_utm_meta_boxes() {
        // Only add meta box in admin context
        if (!is_admin()) {
            return;
        }
        
        add_meta_box(
            'handl_rfq_utm_fields', 
            __('HandL UTM Grabber V3', 'handl-utm-grabber'), 
            'handl_rfq_utm_fields_callback', 
            'addify_quote', 
            'side', 
            'core'
        );
    }
}

/**
 * Display UTM fields in quote admin meta box
 */
if (!function_exists('handl_rfq_utm_fields_callback')) {
    function handl_rfq_utm_fields_callback() {
        print "<style>
        .handl-rfq-field label{
            color: #0084ff;
        }
    
        .handl-rfq-field span{
          overflow-wrap: anywhere;
        }
        </style>";
        
        global $post;
        $post_ID = $post->ID;
        
        $fields = generateUTMFields();
        
        foreach ($fields as $field) {
            $humanField = parseFieldToLabel($field);
            $meta_field_data = get_post_meta($post_ID, $field, true) ? get_post_meta($post_ID, $field, true) : 'NA';
            
            print "
            <p class='form-field form-field-wide handl-rfq-field'>
                <label><b>$humanField</b></label><br/>
                <span>$meta_field_data</span>
            </p>";
        }
    }
}

/**
 * Add UTM fields to quote request form
 */
add_action('addify_after_quote_fields', 'handl_rfq_add_utm_hidden_fields');
if (!function_exists('handl_rfq_add_utm_hidden_fields')) {
    function handl_rfq_add_utm_hidden_fields() {
        // Check if we're in a context where we should add UTM fields
        if (is_admin() || wp_doing_cron()) {
            return;
        }
        
        // Skip AJAX requests that are not related to quote forms
        if (wp_doing_ajax() && !isset($_POST['afrfq_action'])) {
            return;
        }
        
        // Check if WooCommerce session is available
        if (!function_exists('WC') || !WC() || !WC()->session) {
            return;
        }
        
        $fields = generateUTMFields();
        
        echo '<div class="handl-rfq-utm-fields" style="display: none;">';
        foreach ($fields as $field) {
            $value = "";
            if (isset($_COOKIE[$field]) && $_COOKIE[$field] != '') {
                $value = $_COOKIE[$field];
            }
            echo '<input type="hidden" name="' . esc_attr($field) . '" id="' . esc_attr($field) . '" value="' . esc_attr($value) . '" />';
        }
        echo '</div>';
    }
}

/**
 * Save UTM data when quote is submitted
 */
add_action('addify_quote_created', 'handl_rfq_save_utm_data', 10, 1);
if (!function_exists('handl_rfq_save_utm_data')) {
    function handl_rfq_save_utm_data($quote_id) {
        // Validate quote ID
        if (empty($quote_id) || !is_numeric($quote_id)) {
            return;
        }
        
        // Check if UTM fields function exists
        if (!function_exists('generateUTMFields')) {
            return;
        }
        
        $fields = generateUTMFields();
        
        foreach ($fields as $field) {
            $value = "";
            
            // First try to get from POST data (form submission)
            if (!empty($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
            }
            // Fallback to cookie if POST data is not available
            elseif (isset($_COOKIE[$field]) && $_COOKIE[$field] != '') {
                $value = sanitize_text_field($_COOKIE[$field]);
            }
            
            // Save UTM data as quote meta
            if ($value != "") {
                update_post_meta($quote_id, $field, $value);
            }
        }
    }
}

/**
 * Add UTM columns to quote admin list
 */
if (!function_exists('handl_rfq_add_utm_columns')) {
    function handl_rfq_add_utm_columns($columns) {
        // Only modify columns in admin context
        if (!is_admin()) {
            return $columns;
        }
        
        $utm_data = [
            "utm_campaign" => __('Campaign', 'handl-utm-grabber'),
            "utm_source" => __('Source', 'handl-utm-grabber'),
            'utm_medium' => __('Medium', 'handl-utm-grabber')
        ];
        
        $new_columns = array();
        
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            
            // Insert UTM columns before the date column
            if ('date' === $column_name) {
                foreach ($utm_data as $k => $v) {
                    $new_columns[$k] = $v;
                }
            }
        }
        
        return $new_columns;
    }
}

/**
 * Display UTM data in quote admin list columns
 */
if (!function_exists('handl_rfq_display_utm_columns')) {
    function handl_rfq_display_utm_columns($column, $post_id) {
        // Only display columns in admin context
        if (!is_admin()) {
            return;
        }
        
        $utm_data = [
            "utm_campaign" => __('Campaign', 'handl-utm-grabber'),
            "utm_source" => __('Source', 'handl-utm-grabber'),
            'utm_medium' => __('Medium', 'handl-utm-grabber')
        ];
        
        foreach ($utm_data as $k => $v) {
            if ($k === $column) {
                $value = get_post_meta($post_id, $k, true);
                echo $value ? esc_html($value) : 'N/A';
            }
        }
    }
}

/**
 * Add UTM data to quote emails
 */
add_action('addify_rfq_send_quote_email_to_admin', 'handl_rfq_add_utm_to_admin_email', 10, 1);
if (!function_exists('handl_rfq_add_utm_to_admin_email')) {
    function handl_rfq_add_utm_to_admin_email($quote_id) {
        if (get_option('handl_append_quote_meta_to_rfq') == '1') {
            $fields = generateUTMFields();
            $utm_data_html = "<h3>HandL UTM Grabber Parameters</h3><ul>";
            $utm_data_text = "\r\nHandL UTM Grabber Parameters\r\n";
            
            foreach ($fields as $field) {
                $humanField = parseFieldToLabel($field);
                $meta_field_data = get_post_meta($quote_id, $field, true) ? get_post_meta($quote_id, $field, true) : 'NA';
                
                $utm_data_html .= "<li>$humanField: $meta_field_data</li>";
                $utm_data_text .= "$humanField: $meta_field_data\r\n";
            }
            
            $utm_data_html .= "</ul>";
            
            // Store UTM data for email template
            update_post_meta($quote_id, '_handl_utm_email_data_html', $utm_data_html);
            update_post_meta($quote_id, '_handl_utm_email_data_text', $utm_data_text);
        }
    }
}

/**
 * Register setting for quote email UTM data
 */
function handl_register_append_quote_meta_to_rfq() {
    // Only register settings in admin context
    if (!is_admin()) {
        return;
    }
    
    register_setting('handl-utm-grabber-settings-group', 'handl_append_quote_meta_to_rfq', ['default' => 1]);
}

/**
 * Add admin setting for quote email UTM data
 */
function append_quote_meta_to_rfq() {
    // Only add settings in admin context
    if (!is_admin()) {
        return;
    }
    
    global $handl_fields_disabled;
    if (is_plugin_active('woocommerce-request-a-quote/class-addify-request-for-quote.php')):
    ?>
    <tr>
        <th scope='row'>Request a Quote Admin Emails</th>
        <td>
            <fieldset>
                <legend class='screen-reader-text'>
                    <span>Append Quote Meta to Request a Quote Admin Emails</span>
                </legend>
                <label for='handl_append_quote_meta_to_rfq'>
                    <input name='handl_append_quote_meta_to_rfq' id='handl_append_quote_meta_to_rfq' type='checkbox' value='1' <?php print checked('1', get_option('handl_append_quote_meta_to_rfq')); ?> <?php print $handl_fields_disabled; ?> />
                    Click here to append quote meta to Request a Quote admin emails
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
    endif;
}

/**
 * Add UTM data to quote PDF generation
 */
add_action('addify_before_quote', 'handl_rfq_add_utm_to_pdf');
if (!function_exists('handl_rfq_add_utm_to_pdf')) {
    function handl_rfq_add_utm_to_pdf() {
        // This hook runs before quote display, we can add UTM data to PDF context
        if (isset($_GET['af_rfq_download_pdf_with_qoute_id_admin'])) {
            $quote_id = sanitize_text_field($_GET['af_rfq_download_pdf_with_qoute_id_admin']);
            
            if ($quote_id) {
                $fields = generateUTMFields();
                $utm_data = array();
                
                foreach ($fields as $field) {
                    $value = get_post_meta($quote_id, $field, true);
                    if ($value) {
                        $utm_data[$field] = $value;
                    }
                }
                
                // Store UTM data for PDF generation
                update_post_meta($quote_id, '_handl_utm_pdf_data', $utm_data);
            }
        }
    }
}

/**
 * Helper function to parse field name to human readable label
 */
if (!function_exists('parseFieldToLabel')) {
    function parseFieldToLabel($field) {
        return ucwords(implode(" ", explode("_", $field)));
    }
}

/**
 * Add UTM data to quote export functionality
 */
add_action('addify_quote_created', 'handl_rfq_add_utm_to_export', 20, 1);
if (!function_exists('handl_rfq_add_utm_to_export')) {
    function handl_rfq_add_utm_to_export($quote_id) {
        // Store UTM data for potential export functionality
        $fields = generateUTMFields();
        $utm_export_data = array();
        
        foreach ($fields as $field) {
            $value = get_post_meta($quote_id, $field, true);
            if ($value) {
                $utm_export_data[$field] = $value;
            }
        }
        
        if (!empty($utm_export_data)) {
            update_post_meta($quote_id, '_handl_utm_export_data', $utm_export_data);
        }
    }
}

/**
 * Add UTM tracking to quote conversion to order
 */
add_action('addify_quote_converted_to_order', 'handl_rfq_transfer_utm_to_order', 10, 2);
if (!function_exists('handl_rfq_transfer_utm_to_order')) {
    function handl_rfq_transfer_utm_to_order($quote_id, $order_id) {
        $fields = generateUTMFields();
        
        foreach ($fields as $field) {
            $utm_value = get_post_meta($quote_id, $field, true);
            
            if ($utm_value) {
                // Transfer UTM data from quote to order
                update_post_meta($order_id, $field, $utm_value);
            }
        }
    }
}

/**
 * Add JavaScript to populate UTM fields on page load
 */
add_action('wp_footer', 'handl_rfq_utm_javascript');
if (!function_exists('handl_rfq_utm_javascript')) {
    function handl_rfq_utm_javascript() {
        // Only add script on quote request page and when not in admin/cron
        if (is_admin() || wp_doing_cron()) {
            return;
        }
        
        // Skip AJAX requests that are not related to quote forms
        if (wp_doing_ajax() && !isset($_POST['afrfq_action'])) {
            return;
        }
        
        // Check if we're on a page with the quote request shortcode
        if (is_page() && has_shortcode(get_post()->post_content, 'addify-quote-request-page')) {
            // Check if UTM fields function exists
            if (!function_exists('generateUTMFields')) {
                return;
            }
            
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Function to get cookie value
                function getCookie(name) {
                    var value = "; " + document.cookie;
                    var parts = value.split("; " + name + "=");
                    if (parts.length == 2) return parts.pop().split(";").shift();
                    return "";
                }
                
                // Populate UTM fields from cookies
                var utmFields = <?php echo json_encode(generateUTMFields()); ?>;
                
                utmFields.forEach(function(field) {
                    var cookieValue = getCookie(field);
                    if (cookieValue) {
                        $('#' + field).val(cookieValue);
                    }
                });
            });
            </script>
            <?php
        }
    }
}

/**
 * Add UTM data to quote REST API responses
 */
add_filter('rest_prepare_addify_quote', 'handl_rfq_add_utm_to_rest_api', 10, 3);
if (!function_exists('handl_rfq_add_utm_to_rest_api')) {
    function handl_rfq_add_utm_to_rest_api($response, $post, $request) {
        $fields = generateUTMFields();
        $utm_data = array();
        
        foreach ($fields as $field) {
            $value = get_post_meta($post->ID, $field, true);
            if ($value) {
                $utm_data[$field] = $value;
            }
        }
        
        if (!empty($utm_data)) {
            $response->data['utm_data'] = $utm_data;
        }
        
        return $response;
    }
}

/**
 * Add UTM data to quote search functionality
 */
if (!function_exists('handl_rfq_add_utm_to_search')) {
    function handl_rfq_add_utm_to_search($query) {
        // Only process search in admin context and when it's the main query
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // Skip if this is an AJAX request that's not related to quote search
        if (wp_doing_ajax() && !isset($_GET['post_type'])) {
            return;
        }
        
        // Check if we're searching quotes
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'addify_quote') {
            if (isset($_GET['utm_search']) && !empty($_GET['utm_search'])) {
                $search_term = sanitize_text_field($_GET['utm_search']);
                
                // Check if UTM fields function exists
                if (!function_exists('generateUTMFields')) {
                    return;
                }
                
                // Add meta query to search in UTM fields
                $meta_query = array(
                    'relation' => 'OR',
                );
                
                $fields = generateUTMFields();
                foreach ($fields as $field) {
                    $meta_query[] = array(
                        'key' => $field,
                        'value' => $search_term,
                        'compare' => 'LIKE'
                    );
                }
                
                $query->set('meta_query', $meta_query);
            }
        }
    }
}

/**
 * Add UTM search field to quote admin
 */
if (!function_exists('handl_rfq_add_utm_search_field')) {
    function handl_rfq_add_utm_search_field() {
        // Only add search field in admin context
        if (!is_admin()) {
            return;
        }
        
        global $typenow;
        
        if ($typenow === 'addify_quote') {
            $current_value = isset($_GET['utm_search']) ? $_GET['utm_search'] : '';
            echo '<input type="text" name="utm_search" placeholder="Search UTM data..." value="' . esc_attr($current_value) . '" />';
        }
    }
}
