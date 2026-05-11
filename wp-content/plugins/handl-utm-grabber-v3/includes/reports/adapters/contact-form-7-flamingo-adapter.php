<?php
namespace Handl\Reports;
/**
 * Contact Form 7 with Flamingo Adapter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/form-adapter-base.php';
use Handl\Reports\Form_Adapter_Abstract;
use WP_Error;
use WPCF7_ContactForm;

/**
 * Contact Form 7 with Flamingo adapter implementation
 */
class Contact_Form_7_Flamingo_Adapter extends Form_Adapter_Abstract {
    /**
     * Check if Contact Form 7 and Flamingo are active
     *
     * @return bool
     */
    public function is_active() {
        return is_plugin_active('contact-form-7/wp-contact-form-7.php') && 
               is_plugin_active('flamingo/flamingo.php') && 
               class_exists('WPCF7_ContactForm') &&
               class_exists('Flamingo_Inbound_Message');
    }
    
    /**
     * Get forms from Contact Form 7
     *
     * @return array|WP_Error Array of forms or WP_Error if plugin not active
     */
    public function get_forms() {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Contact Form 7 or Flamingo is not active");
        }
        
        $forms = WPCF7_ContactForm::find();
        
        $forms_res = [];
        foreach ($forms as $form) {
            $forms_res[] = [
                "value" => $form->id(), 
                "name" => $form->title()
            ];
        }
        
        return $forms_res;
    }
    
    /**
     * Get entries from Contact Form 7 via Flamingo
     *
     * @param array $form_ids Form IDs to get entries from
     * @param array $search_criteria Search criteria for entries
     * @return array|WP_Error Array of entries or WP_Error if plugin not active
     */
    public function get_entries($form_ids, $search_criteria) {
        if (!$this->is_active()) {
            return new WP_Error('handl-404', "Contact Form 7 or Flamingo plugin is not active");
        }
        
        $entries_res = [];
        $start_date = $search_criteria['start_date'];
        $end_date = $search_criteria['end_date'];
        
        // Get channel term IDs for the selected forms
        $channel_ids = [];
        foreach ($form_ids as $form_id) {
            $form = WPCF7_ContactForm::get_instance($form_id);
            if ($form) {
                $post_meta = get_post_meta($form_id, '_flamingo', true);
                if (isset($post_meta['channel'])) {
                    $channel_ids[] = (int) $post_meta['channel'];
                }
            }
        }
        
        if (empty($channel_ids)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        // Use Flamingo's built-in find method 
        $messages = \Flamingo_Inbound_Message::find(array(
            'posts_per_page' => -1, // Get all entries
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => \Flamingo_Inbound_Message::channel_taxonomy,
                    'terms' => $channel_ids,
                    'field' => 'term_id',
                    'operator' => 'IN'
                )
            ),
            'date_query' => array(
                array(
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true
                )
            )
        ));
        
        if (empty($messages)) {
            return [
                'entries' => $entries_res,
                'field_labels' => $this->get_field_labels()
            ];
        }
        
        foreach ($messages as $message) {
            $cur_data = [];
            
            $post_date = get_post_datetime($message->id());
            $cur_data['date'] = $post_date ? $post_date->format('Y-m-d H:i:s') : '';
            
            $cur_data['email'] = $message->from_email ?: '';
            
            foreach ($this->get_fields() as $field) {
                if ($field === 'email') {
                    continue;
                }
                
                $cur_data[$field] = '';
                
                // Check in the fields array first
                if (isset($message->fields[$field])) {
                    $cur_data[$field] = $message->fields[$field];
                } else {
                    // Check for fields that start with the UTM parameter
                    foreach ($message->fields as $key => $value) {
                        if (strpos($key, $field) === 0) {
                            $cur_data[$field] = $value;
                            break;
                        }
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