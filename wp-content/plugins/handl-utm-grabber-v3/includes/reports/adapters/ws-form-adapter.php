<?php
namespace Handl\Reports;
/**
 * WS Form Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * WS Form adapter implementation
 */
class WS_Form_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if WS Form is active
     *
     * @return bool
     */
    public function is_active() {
        return class_exists('WS_Form') || class_exists('WS_Form_PRO');
    }
    
    /**
     * Get forms from WS Form
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WS Form plugin is not active");
        }
        
        $forms = wsf_form_get_all();
        
        $forms_res = [];
        if (is_array($forms)) {
            foreach ($forms as $form) {
                $forms_res[] = [
                    "value" => $form['id'], 
                    "name" => $form['label']
                ];
            }
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from WS Form
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WS Form plugin is not active");
        }
        
        global $wpdb;
        $entries_res = [];
        
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        
        // Query WS Form submissions table
        $placeholders = implode(',', array_fill(0, count($form_ids), '%d'));
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wsf_submit WHERE form_id IN ($placeholders) AND DATE(date_added) >= %s AND DATE(date_added) <= %s",
            array_merge($form_ids, [$start_date, $end_date])
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        if (empty($results)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        // Cache form objects to avoid repeated queries
        $form_objects = [];
        
        foreach ($results as $row) {
            $submit_object = wsf_submit_get_object($row['id']);
            
            if (!$submit_object) {
                continue;
            }
            
            // Get form object (cache it if we haven't seen this form before)
            $form_id = $row['form_id'];
            if (!isset($form_objects[$form_id])) {
                try {
                    $form_objects[$form_id] = wsf_form_get_object($form_id, true, true);
                } catch (\Exception $e) {
                    // Skip this submission if we can't get the form object
                    continue;
                }
            }
            
            $form_object = $form_objects[$form_id];
            
            $cur_data = [];
            $cur_data['date'] = $row['date_added'];
            
            foreach ($this->get_fields() as $field) {
                $cur_data[$field] = '';
                
                // Try to get value by field class (Works for utmgrabber hidden fields. Check how to integrate utmgrabber with ws-form guide)
                try {
                    $utm_value = wsf_submit_get_value_by_field_class($form_object, $submit_object, $field);
                    if (is_array($utm_value)) {
                        $utm_value = $utm_value[0];
                    }
                    if (!empty($utm_value)) {
                        $cur_data[$field] = $utm_value;
                        continue;
                    }
                } catch (\Exception $e) {
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