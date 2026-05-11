<?php
namespace Handl\Reports;
/**
 * Form Adapter Interface
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract class for form adapters
 */
abstract class Form_Adapter_Abstract implements Form_Adapter_Interface {
    public function get_fields() {
        return [
            'email',
            'utm_campaign',
            'utm_source',
            'utm_medium',
            'utm_content',
            'utm_term',
            'traffic_source'
        ];
    }
    public function get_field_labels() {
        return [
            'email' => 'Email Address',
            'utm_campaign' => 'UTM Campaign',
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_content' => 'UTM Content',
            'utm_term' => 'UTM Term',
            'traffic_source' => 'Traffic Source'
        ];
    }
    abstract public function is_active();
    
    abstract public function get_forms();
    
    abstract public function get_entries($form_ids, $search_criteria);
} 



/**
 * Interface for form adapters
 */
interface Form_Adapter_Interface {
    /**
     * Check if the plugin is active
     *
     * @return bool
     */
    public function is_active();
    
    /**
     * Get forms from the plugin
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active or no forms found
     */
    public function get_forms();
    
    /**
     * Get entries from the forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active or no entries found
     */
    public function get_entries($form_ids, $search_criteria);
    
    /**
     * Get fields to extract from entries
     *
     * @return array Array of fields
     */
    public function get_fields();
    
    /**
     * Get human-readable labels for fields
     *
     * @return array Associative array mapping field names to human-readable labels
     */
    public function get_field_labels();
} 