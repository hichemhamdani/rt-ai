<?php

namespace handl\Premiums;

if (!defined('ABSPATH')) {
    exit;
}

class CustomFields {
    
    public function __construct() {
        // Add tab to admin
        add_filter('filter_admin_tabs', array($this, 'addToTabs'), 10, 1);
        add_filter('get_admin_tab_content_custom-fields', array($this, 'renderReactApp'), 10);
        
        add_filter('filter_handl_parameters', array($this, 'addHandlCustomParams'), 10, 1);
        
        add_action('handl_utm_grabber_enqueue_action', array($this, 'enqueueFrontendLogic'));
        
        // AJAX endpoints 
        add_action('wp_ajax_handl_get_custom_fields', array($this, 'ajaxGetCustomFields'));
        add_action('wp_ajax_handl_update_custom_fields', array($this, 'ajaxUpdateCustomFields'));
        add_action('wp_ajax_handl_delete_custom_field', array($this, 'ajaxDeleteCustomField'));
    }
    
    public function addToTabs($tabs) {
        array_push($tabs, array('custom-fields' => __('Custom Fields', 'handlutmgrabber')));
        return $tabs;
    }
    
    public function renderReactApp() {
        ?>
        <div id='handl-react-root'>
            <div id="handl-custom-fields">
            </div>
        </div>
        <?php
    }
    
    public function addHandlCustomParams($params) {
        $customParams = $this->getCustomParams();
        $customParams = array_filter($customParams, function($v) {
            return $v != '';
        });
        return array_merge($params, $customParams);
    }
    
    /**
     * Enqueue custom params for JS
     */
    public function enqueueFrontendLogic() {
        wp_localize_script('handl-utm-grabber', 'handl_utm_custom_params', $this->getCustomParams());
    }
    
    /**
     * Get custom parameters from database
     */
    public function getCustomParams() {
        $params = get_option('custom_params');
        if (empty($params) || !is_array($params)) {
            return array();
        }
        // Filter out empty values
        return array_values(array_filter($params, function($v) {
            return !empty(trim($v));
        }));
    }
    
    /**
     * AJAX endpoint to get custom fields
     */
    public function ajaxGetCustomFields() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $customFields = $this->getCustomParams();
        
        wp_send_json_success(array(
            'custom_fields' => $customFields,
            'fields_count' => count($customFields)
        ));
    }
    
    /**
     * AJAX endpoint to update custom fields
     */
    public function ajaxUpdateCustomFields() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $customFieldsData = isset($_POST['custom_fields_data']) ? $_POST['custom_fields_data'] : '';
        
        if ($customFieldsData === '') {
            wp_send_json_error('No data provided.');
            return;
        }
        
        $decodedData = json_decode(stripslashes($customFieldsData), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data provided.');
            return;
        }
        
        // Validate data structure (should be array of strings)
        if (!is_array($decodedData)) {
            wp_send_json_error('Data must be an array.');
            return;
        }
        
        // Sanitize and validate each field
        $sanitizedFields = array();
        foreach ($decodedData as $field) {
            if (!is_string($field)) {
                wp_send_json_error('Each field must be a string.');
                return;
            }
            $sanitized = sanitize_text_field(trim($field));
            if (!empty($sanitized)) {
                // Validate field name (alphanumeric, underscores, hyphens only)
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]*$/', $sanitized)) {
                    wp_send_json_error("Invalid field name: '{$sanitized}'. Use only letters, numbers, underscores, and hyphens. Must start with a letter or underscore.");
                    return;
                }
                $sanitizedFields[] = $sanitized;
            }
        }
        
        // Remove duplicates
        $sanitizedFields = array_values(array_unique($sanitizedFields));
        
        // Get current option value to compare
        $currentData = get_option('custom_params', array());
        
        // Save to WordPress options
        $result = update_option('custom_params', $sanitizedFields);
        
        if ($result !== false || $currentData === $sanitizedFields) {
            $message = ($result !== false) ? 'Custom fields updated successfully.' : 'Custom fields are already up to date.';
            wp_send_json_success(array(
                'message' => $message,
                'fields_count' => count($sanitizedFields),
                'custom_fields' => $sanitizedFields,
                'updated' => $result !== false
            ));
        } else {
            wp_send_json_error('Failed to save custom fields data.');
        }
    }
    
    /**
     * AJAX endpoint to delete a single custom field
     */
    public function ajaxDeleteCustomField() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $fieldName = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
        
        if (empty($fieldName)) {
            wp_send_json_error('Field name is required.');
            return;
        }
        
        $customFields = $this->getCustomParams();
        
        $key = array_search($fieldName, $customFields);
        if ($key === false) {
            wp_send_json_error("Field '{$fieldName}' not found.");
            return;
        }
        
        unset($customFields[$key]);
        $customFields = array_values($customFields); // Re-index array
        
        $result = update_option('custom_params', $customFields);
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => "Field '{$fieldName}' deleted successfully.",
                'custom_fields' => $customFields,
                'fields_count' => count($customFields)
            ));
        } else {
            wp_send_json_error('Failed to delete custom field.');
        }
    }
}

new CustomFields();