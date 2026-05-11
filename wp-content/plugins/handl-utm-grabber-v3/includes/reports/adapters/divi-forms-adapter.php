<?php
namespace Handl\Reports;
/**
 * Divi Forms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * Divi Forms adapter implementation
 */
class Divi_Forms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Divi Forms plugin is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('contact-form-db-divi/index.php');
    }
    
    /**
     * Get forms from Divi Forms
     * For Divi Forms, we return a single entry representing all forms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Divi Forms plugin is not active");
        }
        
        // For Divi Forms, we return a single entry representing all forms
        $forms_res = [
            [
                "value" => "all", 
                "name" => "All the forms"
            ]
        ];
        
        return $forms_res;
    }
    
    /**
     * Get entries from Divi Forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Divi Forms plugin is not active");
        }
        
        $entries_res = [];
        
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'lwp_form_submission',
            'date_query'  => array(
                array(
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ),
            ),
        );

        $posts = get_posts($args);
        foreach ($posts as $post) {
            $submission_details = get_post_meta($post->ID, 'processed_fields_values', true);
            $cur_data = [];
            $cur_data['date'] = $post->post_date;
            
            // Get email if available
            if (isset($submission_details["email"])) {
                $cur_data['email'] = $submission_details["email"]["value"];
            }
            
            // Process UTM fields
            foreach ($this->get_fields() as $field) {
                $cur_data[$field] = isset($submission_details[$field]) ? $submission_details[$field]["value"] : "";
            }
            
            $entries_res[] = $cur_data;
        }
        
        return [
            'entries' => $entries_res,
            'field_labels' => $this->get_field_labels()
        ];
    }
} 