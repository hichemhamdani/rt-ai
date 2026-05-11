<?php
namespace Handl\Reports;
/**
 * Gravity Forms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;
use GFAPI;
/**
 * Gravity Forms adapter implementation
 */
class Gravity_Forms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Gravity Forms is active and API is available
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('gravityforms/gravityforms.php') && class_exists('GFAPI');
    }
    
    /**
     * Get forms from Gravity Forms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Gravity Forms is not active");
        }
        
        $forms = GFAPI::get_forms();
        
        $forms_res = [];
        foreach ($forms as $form) {
            $forms_res[] = [
                "value" => $form['id'], 
                "name" => $form['title'] . " (" . $form['id'] . ")"
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from Gravity Forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Gravity Forms plugin is not active");
        }
        
        $entries_res = [];
        $entries = GFAPI::get_entries($form_ids, $search_criteria);
        
        if (empty($entries)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        $form_fields = [];
        foreach ($entries as $entry) {
            $form_id = $entry["form_id"];
            
            if (!isset($form_fields[$form_id])) {
                $form = GFAPI::get_form($form_id);
                
                $cur_form_fields = [];
                foreach ($form["fields"] as $field) {
                    $cur_form_fields[$field["id"]] = $field["inputName"];
                }
                
                $form_fields[$form_id] = $cur_form_fields;
            }
            
            $cur_data = [];
            $cur_data['date'] = $entry["date_created"];
            foreach ($this->get_fields() as $field) {
                $field_index = array_search($field, $form_fields[$form_id]);
                $cur_data[$field] = isset($entry[$field_index]) ? $entry[$field_index] : "";
            }
            $entries_res[] = $cur_data;
        }
        
        return [
            'entries' => $entries_res,
            'field_labels' => $this->get_field_labels()
        ];
    }
} 