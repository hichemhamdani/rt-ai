<?php
namespace Handl\Reports;
/**
 * Fluent Forms Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * Fluent Forms adapter implementation
 */
class Fluent_Forms_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Fluent Forms is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('fluentform/fluentform.php') && function_exists('wpFluent');
    }
    
    /**
     * Get forms from Fluent Forms
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Fluent Forms is not active");
        }
        
        $forms_res = [];
        
        try {
            $forms = wpFluent()->table('fluentform_forms')->where('status', 'published')->get();
            
            if (empty($forms)) {
                return new WP_Error('handl-404', "No forms found");
            }
            
            foreach ($forms as $form) {
                $forms_res[] = [
                    "value" => $form->id, 
                    "name" => $form->title . " (" . $form->id . ")"
                ];
            }
            
            return $forms_res;
        } catch (\Exception $e) {
            return new WP_Error('handl-500', $e->getMessage());
        }
    }
    
    /**
     * Get entries from Fluent Forms
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Fluent Forms plugin is not active");
        }
        
        $entries_res = [];
        $start_date = isset($search_criteria['start_date']) ? $search_criteria['start_date'] : '';
        $end_date = isset($search_criteria['end_date']) ? $search_criteria['end_date'] : '';
        
        try {
            // Get submissions using Fluent Forms ORM
            $submissions = wpFluent()->table('fluentform_submissions')
                ->whereIn('form_id', $form_ids)
                ->where('created_at', '>=', $start_date . ' 00:00:00')
                ->where('created_at', '<=', $end_date . ' 23:59:59')
                ->orderBy('id', 'DESC')
                ->get();
            
            foreach ($submissions as $submission) {
                $cur_data = [];
                $cur_data['date'] = $submission->created_at;
                
                $response_data = json_decode($submission->response, true);
                
                foreach ($this->get_fields() as $field) {
                    $cur_data[$field] = isset($response_data[$field]) ? $response_data[$field] : "";
                }
                
                $entries_res[] = $cur_data;
            }
            
            $entries_res = array_slice($entries_res, 0, 75);
            
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        } catch (\Exception $e) {
            return new WP_Error('handl-500', $e->getMessage());
        }
    }
} 