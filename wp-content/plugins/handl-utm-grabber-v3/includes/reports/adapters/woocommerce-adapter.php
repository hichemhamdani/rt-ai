<?php
namespace Handl\Reports;
/**
 * WooCommerce Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;

/**
 * WooCommerce adapter implementation
 */
class WooCommerce_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('woocommerce/woocommerce.php');
    }
    
    /**
     * Get "forms" from WooCommerce - for WooCommerce this is just a single option representing all orders
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WooCommerce is not active");
        }
        
        // WooCommerce doesn't have forms, so we just return a single option for all orders
        return [
            [
                "value" => "all", 
                "name" => "All Orders"
            ]
        ];
    }
    
    /**
     * Get entries (orders) from WooCommerce
     *
     * @param array $form_ids Form IDs to get entries from (ignored for WooCommerce)
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "WooCommerce plugin is not active");
        }
        
        $entries_res = [];
        
        // Get orders for the date range
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        
        $orders = wc_get_orders(array(
            'limit' => -1,
            'post_type' => 'shop_order',
            'date_created' => $start_date . '...' . $end_date
        ));
        
        if (empty($orders)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        foreach ($orders as $order) {
            $cur_data = [];
            $cur_data['date'] = $order->get_date_created()->date('Y-m-d H:i:s');
            $cur_data['email'] = $order->get_billing_email();
            
            // Get UTM fields from order meta
            foreach ($this->get_fields() as $field) {
                if ($field !== 'email') { // Email is already handled above
                    $cur_data[$field] = $order->get_meta($field) ?: "";
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