<?php
namespace Handl\Reports;
/**
 * Elementor Pro Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * Elementor Pro adapter implementation
 */
class Elementor_Pro_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Elementor Pro is active and API is available
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('elementor-pro/elementor-pro.php');
    }
    
    /**
     * Get forms from Elementor Pro
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Elementor Pro is not active");
        }
        
        global $wpdb;
        $forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '__elementor_forms_snapshot'", OBJECT);
        
        $forms_res = [];
        foreach ($forms as $form) {
            $cur_values = json_decode($form->meta_value)[0];
            $forms_res[] = [
                "value" => $form->post_id, 
                "name" => $cur_values->name . " (" . $cur_values->id . ")"
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from Elementor Pro
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Elementor Pro plugin is not active");
        }
        
        global $wpdb;
        $entries_res = [];
        
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        $placeholders = implode(',', array_fill(0, count($form_ids), '%d'));

        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}e_submissions 
                WHERE post_id IN ($placeholders) 
                AND created_at BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY)",
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
            $form_id = $entry["id"];
            
            $form = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$wpdb->prefix}e_submissions_values WHERE submission_id = %d", intval($form_id)),
                ARRAY_A
            );
            
            $cur_data = [];
            $cur_data['date'] = $entry["created_at"];
            
            foreach ($form as $field) {
                if (in_array($field["key"], $this->get_fields())) {
                    $cur_data[$field["key"]] = $field["value"];
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