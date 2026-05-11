<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Initialize the Reports API
 */
function handl_reports_init() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    require_once plugin_dir_path( __FILE__ ) . '/reports/reports-api-routes.php';
}

add_action( 'plugins_loaded', 'handl_reports_init' ); 