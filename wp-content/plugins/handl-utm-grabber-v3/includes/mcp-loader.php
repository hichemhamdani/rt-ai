<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'mcp/mcp-signature-verifier.php';
require_once plugin_dir_path(__FILE__) . 'mcp/mcp-api-routes.php';

add_action('rest_api_init', array('Handl\MCP\API_Routes', 'init'));