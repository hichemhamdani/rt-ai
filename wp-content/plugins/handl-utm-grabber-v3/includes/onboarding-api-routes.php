<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . '/utils/class-active-plugins-checker.php';
use Handl\PluginUtils\Active_Plugins_Checker;
require_once plugin_dir_path( __FILE__ ) . '/utils/class-onboarding-tracker.php';
use Handl\PluginUtils\Plugin_Onboarding_Tracker;

add_action( 'rest_api_init', function () {
    register_rest_route(
        'handl-onboarding/v1',
        '/cookie_domain',
        array(
            'methods'             => 'POST',
            'callback'            => 'handl_set_cookie_domain',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
            'args'=> array(
                'domain_name'  => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        )
    );
    register_rest_route(
        'handl-onboarding/v1',
        '/setup',
        array(
            'methods'             => 'GET',
            'callback'            => 'handl_get_onboarding_setup',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        )
    );
});

function handl_get_onboarding_setup(WP_REST_Request $request) {
    $active_plugins = Active_Plugins_Checker::get_active_supported_plugins();
    $tracker = Plugin_Onboarding_Tracker::get_instance();
    foreach ( $active_plugins as &$plugin ) {
        if ( isset( $plugin['id'] ) && $tracker->is_step_complete( $plugin['id'] ) ) {
            $plugin['setup_complete'] = true;
        } else {
            $plugin['setup_complete'] = false;
        }
    }
    return rest_ensure_response(array(
        'license_key'             => get_option('license_key_handl-utm-grabber-v3', null),
        'domain_name'             => get_option('handl_cookie_domain', null),
        'active_supported_plugins' => $active_plugins
    ));
}

function handl_set_cookie_domain( WP_REST_Request $request ) {

    $domain_name  = $request->get_param( 'domain_name' );
    $domain_name = handl_get_clean_domain($domain_name);
    update_option( 'handl_cookie_domain', $domain_name );
    return rest_ensure_response( array( 'success' => true ) );
}
function handl_get_clean_domain($url) {
    // Remove protocol and 'www.'
    $domain = preg_replace('/^(https?:\/\/)?(www\.)?/i', '', $url);
    
    // Remove any path, query, or hash
    $domain = explode('/', $domain)[0];
    $domain = explode('?', $domain)[0];
    $domain = explode('#', $domain)[0];

    // Trim any whitespace
    $domain = trim($domain);

    return $domain;
}