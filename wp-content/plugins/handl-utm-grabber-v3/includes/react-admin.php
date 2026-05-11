<?php

if ( ! class_exists( 'Handl_React_Pages_Manager' ) ) {

    class Handl_React_Pages_Manager {
        private $plugin_path;

        private $plugin_url;

        public function __construct() {
            $this->plugin_path = plugin_dir_path( __FILE__ );
            $this->plugin_url  = plugin_dir_url( __FILE__ );

            // Register admin pages
            add_action( 'admin_menu', [ $this, 'add_react_menu_pages' ] );

            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        }

        public function add_react_menu_pages() {
            add_submenu_page(
                'admin.php',
                'Onboarding - Handl Utm Grabber',
                'Onboarding - Handl Utm Grabber',
                'manage_options',
                'handl-utm-grabber-onboarding',
                [ $this, 'render_onboarding_page' ]
            );
            // How to create a menu page with link in the admin sidebar
            // add_submenu_page(
            //     'handl-utm-grabber.php',
            //     "Handl Advanced Options",
            //     "Handl Advanced Options",
            //     'manage_options',
            //     'react-settings-advanced-options',
            //     [ $this, 'render_advanced_page' ]
            // );

            $user_id = get_current_user_id();
            $has_seen_analytics = get_user_meta($user_id, 'handl_seen_analytics', true);
            
            // Add "new" badge only if user hasn't seen it (We'll keep this badge until the next release)
            $analytics_menu_title = 'Analytics';
            if (!$has_seen_analytics) {
                $analytics_menu_title = 'Analytics <span class="update-plugins count-1"><span class="update-count">new</span></span>';
            }
            
            add_submenu_page(
                'handl-utm-grabber.php',
                'Analytics',
                $analytics_menu_title,
                'manage_options',
                'handl_analytics',
                [ $this, 'render_analytics_page' ],
                1
            );

        }
        public function inject_utm_grabber_popup() {
            echo '<script type="text/javascript">
                    document.addEventListener("DOMContentLoaded", function() {
                        let reactRoot = document.getElementById("handl-react-root");
                        if (!reactRoot) {
                            reactRoot = document.createElement("div");
                            reactRoot.id = "handl-react-root";
                            reactRoot.style.display = "contents";
                            document.body.appendChild(reactRoot);
                        }
                        
                        if (!document.getElementById("handl_global_popup")) {
                            const container = document.createElement("div");
                            container.id = "handl_global_popup";
                            reactRoot.appendChild(container);
                        }
                    });
                    </script>';
        }
        /**
         * Renders the container for the Home page React app.
         */
        public function render_onboarding_page() {
            $this->render_react_container( 'handl_utm_grabber_onboarding' );
        }
        public function render_analytics_page() {
            // update_user_meta($user_id, 'handl_seen_analytics', '1');
            $this->render_react_container( 'handl_analytics' );
        }
        private function render_react_container( $container_id ) {
            printf(
                '<div style="display: contents;" id="handl-react-root"><div id="%s"></div></div>',
                esc_attr( $container_id )
            );
        }

        /**
         * Conditionally enqueue React build assets on only the relevant admin pages.
         *
         * @param string $hook_suffix  The current page hook.
         */
        public function enqueue_admin_scripts( $hook_suffix ) {
            $allowed_hooks = [
                'admin_page_handl-utm-grabber-onboarding',
                'utm_page_handl_analytics',
                'toplevel_page_handl-utm-grabber',
            ];

            if ( ! in_array( $hook_suffix, $allowed_hooks, true ) ) {
                return;
            }

            if (in_array( $hook_suffix, ['toplevel_page_handl-utm-grabber','utm_page_handl_analytics'], true )) {
                add_action( 'admin_footer', [ $this, 'inject_utm_grabber_popup' ] );
            }

            $script_asset_path = $this->plugin_path . '../admin/build/index.asset.php';
            if ( ! file_exists( $script_asset_path ) ) {
                return;
            }

            $script_asset = require $script_asset_path;

            wp_enqueue_script(
                'handl-react-main-script',
                $this->plugin_url . '../admin/build/index.js',
                array_merge( [ 'wp-api-fetch' ], $script_asset['dependencies'] ),
                $script_asset['version'],
                true
            );
            // Enqueue the runtime script if running hot refresh mode
            $runtime_script_path = $this->plugin_path . '../admin/build/runtime.asset.php';
            if ( file_exists( $runtime_script_path ) ) {
                $runtime_asset = require $runtime_script_path;
                wp_enqueue_script(
                    'handl-react-runtime-script',
                    $this->plugin_url . '../admin/build/runtime.js',
                    $runtime_asset['dependencies'],
                    $runtime_asset['version'],
                    true
                );
            }

            wp_enqueue_style(
                'handl-react-main-style',
                $this->plugin_url . '../admin/build/index.css',
                array_filter(
                    $script_asset['dependencies'],
                    function ( $handle ) {
                        return wp_style_is( $handle, 'registered' );
                    }
                ),
                $script_asset['version']
            );
            // Get current user data
            $current_user = wp_get_current_user();
            $user_name = '';
            if (!empty($current_user->first_name) || !empty($current_user->last_name)) {
                $user_name = trim($current_user->first_name . ' ' . $current_user->last_name);
            } elseif (!empty($current_user->display_name)) {
                $user_name = $current_user->display_name;
            }

            wp_localize_script('handl-react-main-script', 'wpAPIProps', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => array(
                    'license_nonce' => wp_create_nonce('license_nonce'),
                    'feedback_nonce' => wp_create_nonce('feedback_nonce'),
                ),
            ]);
            wp_localize_script('handl-react-main-script', 'appProps', [
                'license_key' => get_option('license_key_handl-utm-grabber-v3'),
                'handl_active' => $GLOBALS['handl_active'] ? "true" : "false",
            ]);
            wp_localize_script('handl-react-main-script', 'current_user', [
                'name' => $user_name,
                'email' => $current_user->user_email ?: '',
                'id' => $current_user->ID,
            ]);

        }
    }
}
if ( class_exists( 'Handl_React_Pages_Manager' ) ) {
    new Handl_React_Pages_Manager();
}