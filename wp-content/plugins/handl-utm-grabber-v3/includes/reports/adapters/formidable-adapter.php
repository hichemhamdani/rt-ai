<?php
namespace Handl\Reports;
/**
 * Formidable Forms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * Formidable Forms adapter implementation
 */
class Formidable_Forms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Formidable Forms is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('formidable/formidable.php');
    }
    
    /**
     * Get forms from Formidable Forms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Formidable Forms is not active");
        }
        
        global $wpdb;
        $forms = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}frm_forms WHERE is_template=0", OBJECT);
        
        $forms_res = [];
        foreach ($forms as $form) {
            $forms_res[] = [
                "value" => $form->id, 
                "name" => $form->name . " (" . $form->id . ")"
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from Formidable Forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Formidable Forms plugin is not active");
        }
        
        $entries_res = [];
        global $wpdb;
        
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        $placeholders = implode(',', array_fill(0, count($form_ids), '%d'));

        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}frm_items 
                WHERE created_at BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY) 
                AND form_id IN ($placeholders)",
                array_merge([$start_date, $end_date], array_map('intval', $form_ids))
            ),
            ARRAY_A
        );
        
        if (empty($entries)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        $form_id_2_fields = [];
        foreach ($entries as $entry) {
            $cur_data = [];
            $cur_data['date'] = $entry["created_at"];
            $item_id = $entry["id"];
            $form_id = $entry["form_id"];
            
            if (!isset($form_id_2_fields[$form_id])) {
                $utm_fields_arr = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}frm_fields WHERE form_id = %d", intval($form_id)),
                    ARRAY_A
                );
                
                $utm_fields_obj = [];
                foreach ($utm_fields_arr as $utm_field) {
                    $default_value_clean = $utm_field["field_key"];
                    $field_match = array_filter($this->get_fields(), function($field) use ($default_value_clean) {
                        return strpos($default_value_clean, $field) === 0;
                    });
                    
                    if ($field_match) {
                        $utm_fields_obj[$utm_field['id']] = array_values($field_match)[0];
                    } elseif ($utm_field["type"] == "email") {
                        $utm_fields_obj[$utm_field['id']] = "email";
                    }
                }
                
                $form_id_2_fields[$form_id] = $utm_fields_obj;
            }
            
            $utm_fields = $form_id_2_fields[$form_id];
            $utm_fields_ids = array_keys($utm_fields);
            
            if (!empty($utm_fields_ids)) {
                $field_placeholders = implode(',', array_fill(0, count($utm_fields_ids), '%d'));
                $cur_utm_fields = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}frm_item_metas 
                        WHERE item_id = %d AND field_id IN ($field_placeholders)",
                        array_merge([intval($item_id)], array_map('intval', $utm_fields_ids))
                    ),
                    ARRAY_A
                );
                
                foreach ($cur_utm_fields as $cur_utm_field) {
                    $cur_key = $utm_fields[$cur_utm_field["field_id"]];
                    if (in_array($cur_key, $this->get_fields())) {
                        $cur_data[$cur_key] = $cur_utm_field["meta_value"];
                    }
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