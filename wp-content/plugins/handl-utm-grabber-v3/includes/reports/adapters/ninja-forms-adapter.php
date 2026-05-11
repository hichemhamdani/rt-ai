<?php
namespace Handl\Reports;
/**
 * Ninja Forms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * Ninja Forms adapter implementation
 */
class Ninja_Forms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Ninja Forms is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('ninja-forms/ninja-forms.php');
    }
    
    /**
     * Get forms from Ninja Forms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Ninja Forms is not active");
        }
        
        global $wpdb;
        $forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nf3_forms", OBJECT);
        
        $forms_res = [];
        foreach ($forms as $form) {
            $forms_res[] = [
                "value" => $form->id, 
                "name" => $form->title . " (" . $form->id . ")"
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from Ninja Forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Ninja Forms plugin is not active");
        }
        
        global $wpdb;
        $entries_res = [];
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];

        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}posts p 
                WHERE post_date BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY) 
                AND post_type='nf_sub'",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );
        
        if (empty($submissions)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        $form_id_2_fields = [];
        
        foreach ($submissions as $submission) {
            $post_id = $submission["ID"];
            
            // Check if this submission belongs to one of our selected forms
            $form_placeholders = implode(',', array_fill(0, count($form_ids), '%s'));
            $form_id_check = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}postmeta p 
                    WHERE post_id = %d 
                    AND meta_key = '_form_id' 
                    AND meta_value IN ($form_placeholders)",
                    array_merge([intval($post_id)], array_map('strval', $form_ids))
                ),
                ARRAY_A
            );
            
            if (empty($form_id_check)) {
                continue;
            }
            
            $form_id = $form_id_check[0]["meta_value"];
            
            // Cache field mapping for performance
            if (!isset($form_id_2_fields[$form_id])) {
                $fields_arr = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nf3_fields WHERE parent_id = %d", intval($form_id)),
                    ARRAY_A
                );
                
                $field_mapping = [];
                foreach ($fields_arr as $field) {
                    $default_value_clean = str_replace(array('{', '}', 'handl:'), '', $field["default_value"]);
                    if (in_array($default_value_clean, $this->get_fields())) {
                        $field_mapping[$field['id']] = $default_value_clean;
                    } elseif ($field["type"] == "email") {
                        $field_mapping[$field['id']] = "email";
                    }
                }
                
                $form_id_2_fields[$form_id] = $field_mapping;
            }
            
            $field_mapping = $form_id_2_fields[$form_id];
            $field_ids = array_keys($field_mapping);
            
            // Only continue if we found mappable fields
            if (empty($field_ids)) {
                continue;
            }
            
            // Get field values for this submission
            $field_ids_prefix = array_map(function($id) { return "_field_" . intval($id); }, $field_ids);
            $meta_placeholders = implode(',', array_fill(0, count($field_ids_prefix), '%s'));

            $field_values = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}postmeta p 
                    WHERE post_id = %d 
                    AND meta_key IN ($meta_placeholders)",
                    array_merge([intval($post_id)], $field_ids_prefix)
                ),
                ARRAY_A
            );
            
            // Build entry data
            $entry_data = [];
            $entry_data['date'] = $submission["post_date"];
            
            foreach ($field_values as $field_value) {
                $field_id = (int)str_replace("_field_", "", $field_value["meta_key"]);
                $field_key = $field_mapping[$field_id];
                
                if (in_array($field_key, $this->get_fields())) {
                    $entry_data[$field_key] = $field_value["meta_value"];
                }
            }
            
            // Ensure all expected fields are present (even if empty)
            foreach ($this->get_fields() as $field) {
                if (!isset($entry_data[$field])) {
                    $entry_data[$field] = "";
                }
            }
            
            $entries_res[] = $entry_data;
        }
        
        return [
            'entries' => $entries_res,
            'field_labels' => $this->get_field_labels()
        ];
    }
} 