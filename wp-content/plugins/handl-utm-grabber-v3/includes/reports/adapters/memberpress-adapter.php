<?php
namespace Handl\Reports;
/**
 * MemberPress Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;
use MeprDb;
/**
 * MemberPress adapter implementation
 */
class MemberPress_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if MemberPress is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('memberpress/memberpress.php') && class_exists('MeprProduct') && class_exists('MeprTransaction');
    }
    
    /**
     * Get Memeberpress products
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "MemberPress is not active");
        }
        
        $forms_res = [
            [
                "value" => "all",
                "name" => "All Memberships"
            ]
        ];
        
        return $forms_res;
    }
    
    
    /**
     * Get entries from MemberPress
     *
     * @param array $form_ids Form IDs (not used in this implementation)
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "MemberPress plugin is not active");
        }
        
        global $wpdb;
        $mepr_db = MeprDb::fetch();
        $entries_res = [];
        
        $start_date = isset($search_criteria['start_date']) ? $search_criteria['start_date'] : null;
        $end_date = isset($search_criteria['end_date']) ? $search_criteria['end_date'] : null;
        
        $where_clauses = [];
        $query_params = [];
        
        if ($start_date) {
            $where_clauses[] = "m.created_at >= %s";
            $query_params[] = $start_date;
        }
        
        if ($end_date) {
            $where_clauses[] = "m.created_at <= %s";
            $query_params[] = $end_date . ' 23:59:59';
        }
        
        $where_clause = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
        
        $query = $wpdb->prepare(
            "SELECT m.user_id, m.created_at, u.user_email
            FROM {$mepr_db->members} m
            JOIN {$wpdb->users} u ON m.user_id = u.ID
            {$where_clause}
            ORDER BY m.created_at DESC",
            $query_params
        );
        
        $members = $wpdb->get_results($query);
        
        foreach ($members as $member) {
            $user_id = $member->user_id;
            
            $entry_data = [
                'date' => $member->created_at,
                'email' => $member->user_email,
            ];
            
            foreach ($this->get_fields() as $field) {
                if ($field === 'email') {
                    continue;
                }
                
                $value = get_user_meta($user_id, $field, true);
                $entry_data[$field] = $value;
            }
            
            $entries_res[] = $entry_data;
        }
        
        $field_labels = $this->get_field_labels();
        
        return [
            'entries' => $entries_res,
            'field_labels' => $field_labels
        ];
    }
} 