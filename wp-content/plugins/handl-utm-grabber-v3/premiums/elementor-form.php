<?php
if ( ! defined( 'ABSPATH' ) ) exit;

#we do not need this any more, because \Elementor\Core\DynamicTags\Tag does the same thing, but still do not remove yes for compatibility issues.
 add_filter( "elementor_pro/forms/render/item/hidden", function( $item, $item_index, $form ){
    $new_version = true;
    if (isset($item['custom_id']) && $item['custom_id'] != ""){
        $field = $item['custom_id'];
    }elseif (isset($item['_id']) && $item['_id'] != ""){
        $field = $item['_id'];
        $new_version = false;
    }

    if (isset($_COOKIE[$field]) && $_COOKIE[$field] != ''){
        $item['field_value'] = $_COOKIE[$field];
        if ($new_version)
            $form->add_render_attribute( 'input' . $item_index, 'value', $item['field_value'] );
    }
    return $item;
}, 10, 3 );


if (class_exists('\Elementor\Core\DynamicTags\Tag')) {
	class Cookies extends \Elementor\Core\DynamicTags\Tag {
		public function get_name() {
			return 'cookies';
		}

		public function get_title() {
			return __( 'Parameters', 'elementor-pro' );
		}

		public function get_group() {
			return 'cookie-variables';
		}

		public function get_categories() {
			return [
				\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
				\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
				\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
				\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
			];
		}

		protected function _register_controls() {
			$params = generateUTMFields();
			$options = array_combine($params, $params);

			$this->add_control(
				'cookies',
				[
					'label'   => __( 'Parameter', 'handl-utm-grabber' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $options,
					'default' => 'utm_source',
				]
			);
		}

		public function render() {
			$settings = $this->get_settings();

			if ( empty( $settings['cookies'] ) ) {
				return;
			}

			$param = $settings['cookies'];

			$value = isset( $_COOKIE[ $param ] ) ? $_COOKIE[ $param ] : '';

			echo esc_html( $value );
		}
	}

	add_action( 'elementor/dynamic_tags/register_tags', function ( $dynamic_tags ) {

		\Elementor\Plugin::$instance->dynamic_tags->register_group( 'cookie-variables', [
			'title' => 'HandL UTM Grabber'
		] );

		// Finally register the tag
		$dynamic_tags->register_tag( 'Cookies' );
	} );
}