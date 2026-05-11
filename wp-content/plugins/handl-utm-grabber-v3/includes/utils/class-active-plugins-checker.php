<?php
namespace Handl\PluginUtils;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Active_Plugins_Checker {

    /**
     * Utility function to check if any identifier is active.
     *
     * For check type 'constant', each identifier is treated as a constant name and checked with defined().
     * Otherwise, each identifier is assumed to be a plugin file path and checked using is_plugin_active().
     *
     * @param array  $identifiers Array of strings (plugin file paths or constant names).
     * @param string $check_type  The type of check to perform ('constant' for constant check, anything else for plugin check).
     * @return bool True if at least one identifier is active; false otherwise.
     */
    public static function is_plugin_active(array $identifiers, $check_type = 'plugin') {
        if ( empty( $identifiers ) ) {
            return false;
        }

        if ( $check_type === 'constant' ) {
            foreach ( $identifiers as $identifier ) {
                if ( defined( $identifier ) ) {
                    return true;
                }
            }
        } else {
            // Ensure the is_plugin_active function is available.
            if ( ! function_exists( 'is_plugin_active' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            foreach ( $identifiers as $plugin_file ) {
                if ( is_plugin_active( $plugin_file ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns an array of active supported plugin objects read from supported_integrations.json.
     *
     * @return array Array of active plugin objects.
     */
    public static function get_active_supported_plugins() {
        $active_plugins = array();

        // Define the path to the JSON file.
        $json_file = __DIR__ . '/supported_integrations.json';

        if ( ! file_exists( $json_file ) ) {
            return $active_plugins;
        }

        // Read and decode the JSON file.
        $json_content = file_get_contents( $json_file );
        $plugins_data = json_decode( $json_content, true );

        if ( ! is_array( $plugins_data ) ) {
            return $active_plugins;
        }

        // Iterate over each plugin object from the JSON file.
        foreach ( $plugins_data as $plugin ) {
            $check_type = ( isset( $plugin['check_type'] ) && $plugin['check_type'] === 'constant' )
                ? 'constant'
                : 'plugin';

            if ( isset( $plugin['plugin_id'] ) && is_array( $plugin['plugin_id'] ) ) {
                if ( self::is_plugin_active( $plugin['plugin_id'], $check_type ) ) {
                    $active_plugins[] = $plugin;
                }
            }
        }

        return $active_plugins;
    }
}
