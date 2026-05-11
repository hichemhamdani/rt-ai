<?php
namespace Handl\Reports;
/**
 * Form Adapter Factory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/adapters/form-adapter-base.php';
require_once dirname( __FILE__ ) . '/adapters/gravity-forms-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/fluent-forms-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/ninja-forms-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/wpforms-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/woocommerce-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/elementor-pro-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/divi-forms-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/contact-form-7-cfdb7-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/contact-form-7-flamingo-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/formidable-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/memberpress-adapter.php';
require_once dirname( __FILE__ ) . '/adapters/ws-form-adapter.php';
use WP_Error;
use Handl\Reports\Gravity_Forms_Adapter;
use Handl\Reports\Fluent_Forms_Adapter;
use Handl\Reports\Ninja_Forms_Adapter;
use Handl\Reports\WPForms_Adapter;
use Handl\Reports\WooCommerce_Adapter;
use Handl\Reports\Elementor_Pro_Adapter;
use Handl\Reports\Divi_Forms_Adapter;
use Handl\Reports\Contact_Form_7_CFDB7_Adapter;
use Handl\Reports\Contact_Form_7_Flamingo_Adapter;
use Handl\Reports\Formidable_Forms_Adapter;
use Handl\Reports\MemberPress_Adapter;
use Handl\Reports\WS_Form_Adapter;

/**
 * Factory class for form adapters
 */
class Form_Adapter_Factory {
    /**
     * Get adapter for the specified form plugin
     *
     * @param string $form_plugin Form plugin identifier
     * @return Form_Adapter_Interface|WP_Error Adapter instance or WP_Error if adapter not found
     */
    public static function get_adapter($form_plugin) {
        switch ($form_plugin) {
            case 'gravity-form':
                return new Gravity_Forms_Adapter();
                
            case 'fluent-forms':
                return new Fluent_Forms_Adapter();
                
            case 'ninja-forms':
                return new Ninja_Forms_Adapter();
                
            case 'wpforms':
                return new WPForms_Adapter();
                
            case 'woocommerce':
                return new WooCommerce_Adapter();
                
            case 'elementor-pro':
                return new Elementor_Pro_Adapter();
                
            case 'contact-form-db-divi':
                return new Divi_Forms_Adapter();
                
            case 'contact-form-cfdb7':
                return new Contact_Form_7_CFDB7_Adapter();
                
            case 'contact-form-7-flamingo':
                return new Contact_Form_7_Flamingo_Adapter();
                
            case 'formidable':
                return new Formidable_Forms_Adapter();
                
            case 'memberpress':
                return new MemberPress_Adapter();
                
            case 'ws-form':
                return new WS_Form_Adapter();
                
            // Add more adapters here as they are implemented
            
            default:
                return new WP_Error('handl-404', $form_plugin . " is not supported yet. Please contact with us");
        }
    }
} 