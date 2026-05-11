<?php
namespace Handl\PluginUtils;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class Plugin_Onboarding_Tracker {

    private $option_name = 'handl_utmgrabber_onboarding_steps';

    /**
     * Array that holds the onboarding data loaded from the database.
     *
     * Structure example:
     * [
     *   'gravity' => [
     *       'step1' => true,
     *       'step2' => true,
     *   ],
     *   'calcom' => [
     *       'step1' => true,
     *   ],
     * ]
     *
     * @var array
     */
    private $data = array();

    /**
     * Singleton instance.
     *
     * @var Plugin_Onboarding_Tracker|null
     */
    private static $instance = null;

    /**
     * Private constructor.
     *
     * Loads the existing onboarding data from the database.
     */
    private function __construct() {
        $this->data = get_option( $this->option_name, array() );
    }

    /**
     * Retrieve the singleton instance.
     *
     * @return Plugin_Onboarding_Tracker
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Mark an onboarding step as complete for a specific plugin.
     *
     * @param string $plugin_id Unique ID of the plugin.
     * @param string $step      Identifier for the onboarding step.
     *
     * @return void
     */
    public function complete_step( $plugin_id, $step = 'initial_configuration' ) {
        if ( ! isset( $this->data[ $plugin_id ] ) ) {
            $this->data[ $plugin_id ] = array();
        }

        $this->data[ $plugin_id ][ $step ] = true;

        // Save the updated data back to the database.
        update_option( $this->option_name, $this->data );
    }

    /**
     * Check if a specific onboarding step is complete for a given plugin.
     *
     * @param string $plugin_id Unique ID of the plugin.
     * @param string $step      Identifier for the onboarding step.
     *
     * @return bool True if the step is marked as complete, false otherwise.
     */
    public function is_step_complete( $plugin_id, $step = 'initial_configuration' ) {
        return isset( $this->data[ $plugin_id ] ) && isset( $this->data[ $plugin_id ][ $step ] );
    }

    /**
     * Retrieve all completed onboarding steps for a specific plugin.
     *
     * @param string $plugin_id Unique ID of the plugin.
     *
     * @return array An array of steps with their completion timestamps (or true values).
     */
    public function get_completed_steps( $plugin_id ) {
        return isset( $this->data[ $plugin_id ] ) ? $this->data[ $plugin_id ] : array();
    }

    /**
     * Reset the onboarding data for a specific plugin.
     *
     * @param string $plugin_id Unique ID of the plugin.
     *
     * @return void
     */
    public function reset_onboarding( $plugin_id ) {
        if ( isset( $this->data[ $plugin_id ] ) ) {
            unset( $this->data[ $plugin_id ] );
            update_option( $this->option_name, $this->data );
        }
    }
}
