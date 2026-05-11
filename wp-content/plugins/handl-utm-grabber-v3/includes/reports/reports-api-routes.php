<?php
/**
 * Reports API Routes
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once dirname( __FILE__ ) . '/reports-manager.php';
use Handl\Reports\Reports_Manager;

add_action( 'rest_api_init', function () {
    // List forms endpoint
    register_rest_route(
        'handl-reports/v1',
        '/list-forms',
        array(
            'methods'             => 'POST',
            'callback'            => 'handl_reports_api_list_forms',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'selected_form_plugin'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        )
    );

    // Get available plugins endpoint
    register_rest_route(
        'handl-reports/v1',
        '/available-plugins',
        array(
            'methods'             => 'GET',
            'callback'            => 'handl_reports_api_get_available_plugins',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        )
    );

    // Get entries endpoint
    register_rest_route(
        'handl-reports/v1',
        '/get-entries',
        array(
            'methods'             => 'POST',
            'callback'            => 'handl_reports_api_get_entries',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'selected_form_plugin'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_form_ids'  => array(
                    'required' => true,
                    'type'     => 'array',
                ),
                'selected_start_date'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_end_date'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        )
    );

    // Generate insight endpoint
    register_rest_route(
        'handl-reports/v1',
        '/generate-insight',
        array(
            'methods'             => 'POST',
            'callback'            => 'handl_reports_api_generate_insight',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'selected_form_plugin'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_form_ids'  => array(
                    'required' => true,
                    'type'     => 'array',
                ),
                'selected_start_date'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_end_date'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        )
    );

    // Delete report endpoint
    register_rest_route(
        'handl-reports/v1',
        '/delete-report',
        array(
            'methods'             => 'DELETE',
            'callback'            => 'handl_reports_api_delete_report',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'report_id'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        )
    );

    // Get saved reports endpoint
    register_rest_route(
        'handl-reports/v1',
        '/saved-reports',
        array(
            'methods'             => 'GET',
            'callback'            => 'handl_reports_api_get_saved_reports',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        )
    );

    // Get specific report endpoint
    register_rest_route(
        'handl-reports/v1',
        '/report/(?P<id>\d+)',
        array(
            'methods'             => 'GET',
            'callback'            => 'handl_reports_api_get_report',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'id'  => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        )
    );
});

/**
 * API handler for listing forms
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response
 */
function handl_reports_api_list_forms( WP_REST_Request $request ) {
    $reports_manager = new Reports_Manager();
    $selected_form_plugin = $request->get_param( 'selected_form_plugin' );
    
    $forms = $reports_manager->get_forms($selected_form_plugin);
    
    if (is_wp_error($forms)) {
        return $forms;
    }
    
    return rest_ensure_response($forms);
}

/**
 * API handler for getting entries
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response
 */
function handl_reports_api_get_entries( WP_REST_Request $request ) {
    $reports_manager = new Reports_Manager();
    $selected_form_plugin = $request->get_param( 'selected_form_plugin' );
    $selected_form_ids = $request->get_param( 'selected_form_ids' );
    $selected_start_date = $request->get_param( 'selected_start_date' );
    $selected_end_date = $request->get_param( 'selected_end_date' );
    
    $search_criteria = [
        'start_date' => $selected_start_date,
        'end_date' => $selected_end_date
    ];
    
    $entries_data = $reports_manager->get_entries($selected_form_plugin, $selected_form_ids, $search_criteria);
    
    if (is_wp_error($entries_data)) {
        return $entries_data;
    }
    
    return rest_ensure_response($entries_data);
}

/**
 * API handler for generating insights
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response
 */
function handl_reports_api_generate_insight( WP_REST_Request $request ) {
    $reports_manager = new Reports_Manager();
    $selected_form_plugin = $request->get_param( 'selected_form_plugin' );
    $selected_form_ids = $request->get_param( 'selected_form_ids' );
    $selected_start_date = $request->get_param( 'selected_start_date' );
    $selected_end_date = $request->get_param( 'selected_end_date' );
    
    $search_criteria = [
        'start_date' => $selected_start_date,
        'end_date' => $selected_end_date
    ];
    
    $response = $reports_manager->generate_insight($selected_form_plugin, $selected_form_ids, $search_criteria);
    
    return rest_ensure_response($response);
}

/**
 * API handler for deleting reports
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response
 */
function handl_reports_api_delete_report( WP_REST_Request $request ) {
    $reports_manager = new Reports_Manager();
    $report_id = $request->get_param( 'report_id' );
    
    $response = $reports_manager->delete_report($report_id);
    
    return rest_ensure_response($response);
}

/**
 * API handler for getting saved reports
 *
 * @return WP_REST_Response The response
 */
function handl_reports_api_get_saved_reports() {
    $reports_manager = new Reports_Manager();
    $saved_reports = $reports_manager->get_saved_reports();
    
    return rest_ensure_response($saved_reports);
}

/**
 * API handler for getting a specific report
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response
 */
function handl_reports_api_get_report( WP_REST_Request $request ) {
    $reports_manager = new Reports_Manager();
    $report_id = $request->get_param( 'id' );
    
    $report = $reports_manager->get_report($report_id);
    
    if (is_wp_error($report)) {
        return $report;
    }
    
    return rest_ensure_response($report);
}

/**
 * API handler for getting available form plugins
 *
 * @return WP_REST_Response The response containing available plugins and their status
 */
function handl_reports_api_get_available_plugins() {
    $reports_manager = new Reports_Manager();
    $available_plugins = $reports_manager->get_available_plugins();
    
    return rest_ensure_response($available_plugins);
} 