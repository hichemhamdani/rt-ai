<?php
namespace Handl\Reports;
/**
 * WPForms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * WPForms adapter implementation
 */
class WPForms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if WPForms is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('wpforms/wpforms.php') || is_plugin_active('wpforms-lite/wpforms.php');
    }
    
    /**
     * Get forms from WPForms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WPForms is not active");
        }
        
        global $wpdb;
        $forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'wpforms'", OBJECT );
        
        $forms_res = [];
        foreach ($forms as $form) {
            $forms_res[] = [
                "value" => $form->ID, 
                "name" => $form->post_title . " (" . $form->ID . ")"
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from WPForms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WPForms plugin is not active");
        }
        
        global $wpdb;
        $entries_res = [];
        
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        $placeholders = implode(',', array_fill(0, count($form_ids), '%d'));

        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpforms_entries 
                WHERE form_id IN ($placeholders) 
                AND date BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY)",
                array_merge(array_map('intval', $form_ids), [$start_date, $end_date])
            ),
            ARRAY_A
        );
        
        if (empty($entries)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        foreach ($entries as $entry) {
            $form_data = json_decode($entry["fields"], true);
            $cur_data = [];
            $cur_data['date'] = $entry["date"];
            
            // Process form fields and extract UTM data
            foreach ($form_data as $field) {
                $field_name_norm = strtolower(str_replace(" ", "_", $field["name"]));
                if (in_array($field_name_norm, $this->get_fields())) {
                    $cur_data[$field_name_norm] = $field["value"];
                }
                
                // Look for email field
                if ($field_name_norm === 'email' || $field["type"] === 'email') {
                    $cur_data['email'] = $field["value"];
                }
            }
            
            // Make sure all required fields are present, even if empty
            foreach ($this->get_fields() as $field) {
                if (!isset($cur_data[$field])) {
                    $cur_data[$field] = '';
                }
            }
            
            $entries_res[] = $cur_data;
        }
        
        return [
            'entries' => $entries_res,
            'field_labels' => $this->get_field_labels()
        ];
    }
} 