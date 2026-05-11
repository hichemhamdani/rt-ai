<?php

namespace handl\Premiums;

if (!defined('ABSPATH')) {
    exit;
}

class McpConnection
{

    const MCP_BASE_URL = 'https://api.utmgrabber.com';
    // const MCP_BASE_URL = 'http://localhost:4000';
    const OPTION_KEY = 'handl_mcp_credentials';

    public function __construct()
    {
        add_filter('filter_admin_tabs', array($this, 'addToTabs'), 9999, 1);
        add_filter('get_admin_tab_content_mcp_connection', array($this, 'renderReactApp'), 10);

        add_action('wp_ajax_handl_mcp_get_status', array($this, 'ajaxGetStatus'));
        add_action('wp_ajax_handl_mcp_connect_site', array($this, 'ajaxConnectSite'));
        add_action('wp_ajax_handl_mcp_disconnect_site', array($this, 'ajaxDisconnectSite'));
    }

    public function addToTabs($tabs)
    {
        array_push($tabs, array('mcp_connection' => 'MCP <span style="background:#ef4444;color:#fff;font-size:10px;padding:1px 6px;border-radius:3px;font-weight:600;vertical-align:middle;margin-left:2px;">beta</span>'));
        return $tabs;
    }

    public function renderReactApp()
    {
?>
        <div id='handl-react-root'>
            <div id="handl-mcp-connection"></div>
        </div>
<?php
    }

    private function buildMcpUrl($site_secret)
    {
        return self::MCP_BASE_URL . '/http/mcp/utmgrabber-report?access_code=' . $site_secret;
    }

    public function ajaxGetStatus()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }

        $flags = \Handl\Feature_Flags::instance();
        $mcp_enabled = $flags->is_enabled('mcp');
        $mcp_feature = $flags->get_feature('mcp');

        $data = array(
            'mcp_enabled' => $mcp_enabled,
            'plan' => $flags->get_plan(),
        );

        if (!empty($mcp_feature['expires_at'])) {
            $data['mcp_expires_at'] = $mcp_feature['expires_at'];
        }

        $credentials = get_option(self::OPTION_KEY);

        if (empty($credentials) || !is_array($credentials) || empty($credentials['site_secret'])) {
            $data['connected'] = false;
            wp_send_json_success($data);
            return;
        }

        $data['connected'] = true;
        $data['mcp_url'] = $this->buildMcpUrl($credentials['site_secret']);
        $data['connected_at'] = $credentials['connected_at'] ?? null;

        wp_send_json_success($data);
    }

    public function ajaxConnectSite()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }

        if (!\Handl\Feature_Flags::instance()->is_enabled('mcp')) {
            wp_send_json_error('MCP is not available on your current plan. Please upgrade to Starter+ or Premium.');
            return;
        }

        $license_key = get_option('license_key_handl-utm-grabber-v3');
        if (empty($license_key)) {
            wp_send_json_error('No license key found. Please activate your license first.');
            return;
        }

        $site_url = site_url();

        $response = wp_remote_post(self::MCP_BASE_URL . '/http/mcp/connect-site', array(
            'body' => json_encode(array(
                'license_key' => $license_key,
                'site_url' => $site_url,
            )),
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to connect to MCP server: ' . $response->get_error_message());
            return;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code === 401) {
            wp_send_json_error($body['error'] ?? 'Invalid license key.');
            return;
        }

        if ($status_code === 403) {
            wp_send_json_error($body['error'] ?? 'License is not active.');
            return;
        }

        if ($status_code === 400) {
            $message = $body['error'] ?? 'Bad request.';
            if (!empty($body['details'])) {
                $detail_messages = array();
                foreach ($body['details'] as $field => $errors) {
                    $detail_messages[] = $field . ': ' . implode(', ', $errors);
                }
                $message .= ' ' . implode('; ', $detail_messages);
            }
            wp_send_json_error($message);
            return;
        }

        if ($status_code !== 200 || empty($body['site_id']) || empty($body['site_secret'])) {
            wp_send_json_error('Unexpected response from MCP server.');
            return;
        }

        $credentials = array(
            'site_id' => $body['site_id'],
            'site_secret' => $body['site_secret'],
            'site_url' => $body['site_url'],
            'connected_at' => $body['connected_at'],
        );

        update_option(self::OPTION_KEY, $credentials);

        wp_send_json_success(array(
            'connected' => true,
            'mcp_url' => $this->buildMcpUrl($credentials['site_secret']),
            'connected_at' => $credentials['connected_at'],
            // TODO Remove later
            'raw_credentials' => $body,
        ));
    }
    public function ajaxDisconnectSite()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }

        $credentials = get_option(self::OPTION_KEY);

        if (empty($credentials) || !is_array($credentials) || empty($credentials['site_id']) || empty($credentials['site_secret'])) {
            // Already disconnected, clear option to be safe and return success
            delete_option(self::OPTION_KEY);
            wp_send_json_success(array('disconnected' => true));
            return;
        }

        $response = wp_remote_post(self::MCP_BASE_URL . '/http/mcp/disconnect-site', array(
            'body' => json_encode(array(
                'site_id' => $credentials['site_id'],
                'site_secret' => $credentials['site_secret'],
            )),
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to reach MCP server: ' . $response->get_error_message());
            return;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            wp_send_json_error($body['error'] ?? 'Failed to disconnect from MCP server.');
            return;
        }

        delete_option(self::OPTION_KEY);

        wp_send_json_success(array('disconnected' => true));
    }
}

new McpConnection();
