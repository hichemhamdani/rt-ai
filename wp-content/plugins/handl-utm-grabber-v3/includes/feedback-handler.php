<?php

namespace Handl\Feedback;

/**
 * Class Feedback_Handler
 * Handles feature request submissions
 */
class Feedback_Handler {
    
    /**
     * Initialize the feedback handler
     */
    public static function init() {
        add_action( 'wp_ajax_handl_submit_feature_request', [ __CLASS__, 'handle_feature_request' ] );
    }
    
    /**
     * Handle feature request AJAX submission
     */
    public static function handle_feature_request() {
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'feedback_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed. Please refresh the page and try again.' ] );
            return;
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'You do not have permission to submit feedback.' ] );
            return;
        }
        
        // Get form data
        $name = sanitize_text_field( $_POST['name'] );
        $email = sanitize_email( $_POST['email'] );
        $feature_idea = sanitize_textarea_field( $_POST['feature_idea'] );
        
        if ( empty( $name ) || empty( $email ) || empty( $feature_idea ) ) {
            wp_send_json_error( [ 'message' => 'All fields are required' ] );
            return;
        }
        
        $feedback_data = [
            'name' => $name,
            'email' => $email,
            'feature_idea' => $feature_idea,
            'source' => 'utm-grabber-plugin',
            'timestamp' => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
            'site_url' => get_site_url()
        ];
        
        $result = self::send_feedback_to_external_service( $feedback_data );
        
        if ( $result['success'] ) {
            wp_send_json_success( [ 
                'message' => $result['message'] 
            ] );
        } else {
            wp_send_json_error( [ 
                'message' => $result['message'] 
            ] );
        }
    }
    
    /**
     * Send feedback to external service
     * 
     * @param array $data Feedback data
     * @return array Array with 'success' (bool) and 'message' (string) keys
     */
    private static function send_feedback_to_external_service( $data ) {
        $endpoint = 'https://api-dev.utmgrabber.com/http/feedback';
        $args = [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'UTM-Grabber-Plugin/1.0'
            ],
            'body' => wp_json_encode( $data )
        ];
        
        $response = wp_remote_post( $endpoint, $args );
    
        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'message' => 'Failed to submit feature request. Please try again later.'
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( $response_code >= 200 && $response_code < 300 ) {
            return [
                'success' => true,
                'message' => 'Feature request submitted successfully! Thank you for your feedback.'
            ];
        } else {
            return [
                'success' => false,
                'message' => "Failed to submit feature request. Please try again later."
            ];
        }
    }
}

Feedback_Handler::init();
