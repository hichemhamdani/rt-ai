<?php

namespace Handl\MCP;

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(dirname(__FILE__)) . '/reports/reports-manager.php';

use Handl\Reports\Reports_Manager;
use WP_REST_Request;

class API_Routes
{
    const NAMESPACE = 'utmgrabber/v1/mcp';

    public static function init()
    {
        $credentials = get_option('handl_mcp_credentials');

        if (empty($credentials) || !is_array($credentials) || empty($credentials['site_secret'])) {
            return;
        }

        if (!\Handl\Feature_Flags::instance()->is_enabled('mcp')) {
            return;
        }

        $instance = new self();
        $instance->register_routes();
    }

    public function register_routes()
    {
        $permission = array(Signature_Verifier::class, 'verify');

        register_rest_route(self::NAMESPACE, '/health', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'health'),
            'permission_callback' => $permission,
        ));

        register_rest_route(self::NAMESPACE, '/form-plugins', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'form_plugins'),
            'permission_callback' => $permission,
        ));

        register_rest_route(self::NAMESPACE, '/list-forms', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'list_forms'),
            'permission_callback' => $permission,
            'args' => array(
                'selected_form_plugin' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
            ),
        ));

        register_rest_route(self::NAMESPACE, '/get-entries', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'get_entries'),
            'permission_callback' => $permission,
            'args' => array(
                'selected_form_plugin' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_form_ids' => array(
                    'required' => true,
                    'type'     => 'array',
                ),
                'selected_start_date' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'selected_end_date' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type'     => 'integer',
                    'default'  => 75,
                ),
            ),
        ));
    }

    public function health()
    {
        $plugin_data = get_file_data(
            plugin_dir_path(dirname(dirname(__FILE__))) . 'handl-utm-grabber-v3.php',
            array('Version' => 'Version')
        );

        return rest_ensure_response(array(
            'status'         => 'ok',
            'plugin_version' => $plugin_data['Version'] ?? 'unknown',
        ));
    }

    public function form_plugins()
    {
        $manager = new Reports_Manager();
        return rest_ensure_response($manager->get_available_plugins());
    }

    public function list_forms(WP_REST_Request $request)
    {
        $manager = new Reports_Manager();
        $plugin  = $request->get_param('selected_form_plugin');

        $forms = $manager->get_forms($plugin);

        if (is_wp_error($forms)) {
            return $forms;
        }

        return rest_ensure_response($forms);
    }

    public function get_entries(WP_REST_Request $request)
    {
        $manager    = new Reports_Manager();
        $plugin     = $request->get_param('selected_form_plugin');
        $form_ids   = $request->get_param('selected_form_ids');
        $start_date = $request->get_param('selected_start_date');
        $end_date   = $request->get_param('selected_end_date');
        $limit      = intval($request->get_param('limit'));

        $search_criteria = array(
            'start_date' => $start_date,
            'end_date'   => $end_date,
        );

        $entries = $manager->get_entries($plugin, $form_ids, $search_criteria, $limit);

        if (is_wp_error($entries)) {
            return $entries;
        }

        return rest_ensure_response($entries);
    }
}
