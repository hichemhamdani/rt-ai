<?php
//if ( version_compare(phpversion(), '7.2.5', '<'))
//	return;
//
//require_once __DIR__ . '/vendors/facebook/vendor/autoload.php'; // change path as needed
//
//use FacebookAds\Api;
//use FacebookAds\Logger\CurlLogger;
//use FacebookAds\Object\ServerSide\Event;
//use FacebookAds\Object\ServerSide\EventRequest;
//use FacebookAds\Object\ServerSide\UserData;
//use FacebookAds\Object\ServerSide\CustomData;

class HandLFacebookAds {
	public $api;
	private $HANDL_FB_API_ENDPOINT = 'https://api.handldigital.com/http/fb';
	private $auth_token;
	public $FB_ENDPOINT_URL = "https://graph.facebook.com/v11.0";

	public function __construct() {

//		$this->api = Api::init(null, null, $access_token);
		add_action( 'wp_ajax_handl_fb_login', [ $this, 'login' ] );
		add_action( 'wp_ajax_handl_fb_list_acts', [ $this, 'list_acts' ] );
		add_action( 'wp_ajax_handl_fb_list_pixels', [ $this, 'list_pixels' ] );
		add_action( 'wp_ajax_handl_fb_save_pixel_id', [ $this, 'save_pixel_id' ] );
		add_action( 'wp_ajax_handl_fb_unlink_act', [ $this, 'unlink_act' ] );
		add_action( 'wp_ajax_handl_fb_unlink_pixel', [ $this, 'unlink_pixel' ] );
		add_action( 'wp_ajax_handl_fb_unlink_fb', [ $this, 'unlink_fb' ] );
		add_action( 'wp_ajax_handl_fb_ready', [ $this, 'fb_ready' ] );
		add_action( 'wp_ajax_handl_fb_send_offline_conv', [ $this, 'send_offline_conv' ] );

		add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
		add_action( 'init', [ $this, 'auth' ] );
	}

	public function add_metabox(){
		add_meta_box( 'handl_woo_fb_offline_conversion',
			'HandL UTM Grabber FB CAPI',
			[ $this, 'woo_fb_offline_conversion' ],
			'shop_order',
			'side',
			'core'
		);
	}

	public function login(){

		if(!$this->is_authed()){
			$params = [
				'action' => 'login',
				'license' => get_option( 'license_key_handl-utm-grabber-v3' )
			];
			if (isset($_POST['current_url'])){
				$params['ref'] = urlencode($_POST['current_url']);
			}

			$url = add_query_arg($params, $this->HANDL_FB_API_ENDPOINT);

			$request = wp_remote_get( $url);
			$result = [
				"success" => true,
			];

			if( !is_wp_error( $request ) ) {
				$body = wp_remote_retrieve_body( $request );
				$data = json_decode( $body, true );
				$result = array_merge($result, $data);
			}else{
				$result['success'] = false;
			}
		}else{
			$result['success'] = false;
			$result['msg'] = 'Already authorized';
		}

		wp_send_json($result);
	}

	public function auth(){
		if (isset($_GET['access_token'])){
			update_option( 'handl_fb_access_token', $_GET['access_token'] );
			$this->auth_token = $_GET['access_token'];
			wp_redirect( remove_query_arg( array( 'access_token'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		}
	}

	public function is_authed(){
		return get_option('handl_fb_access_token') ? true : false;
	}

	public function is_pixel_saved(){
		return get_option('handl_fb_pixel_id') ? true : false;
	}

	public function is_act_id_saved(){
		return get_option('handl_fb_act_id') ? true : false;
	}

	public function getAccessToken(){
		if ($this->auth_token){
			return $this->auth_token;
		}else{
			return get_option('handl_fb_access_token');
		}
	}

	public function getAccountId(){
		return get_option('handl_fb_act_id');
	}

	public function getAccountName(){
		return get_option('handl_fb_act_name');
	}

	public function getPixelId(){
		return get_option('handl_fb_pixel_id');
	}

	public function getPixelName(){
		return get_option('handl_fb_pixel_name');
	}

	public function unlink_fb($json=true){
		$result = [
			"success" => false,
		];

		if ( delete_option('handl_fb_access_token') &&
		     delete_option('handl_fb_act_id') &&
		     delete_option('handl_fb_act_name') &&
		     delete_option('handl_fb_pixel_id') &&
		     delete_option('handl_fb_pixel_name')){
			$result['success'] = true;
		}

		if ($json)
			wp_send_json($result);
	}

	public function unlink_act(){
		$result = [
			"success" => false,
		];

		if ( delete_option('handl_fb_act_id') &&
		     delete_option('handl_fb_act_name') &&
		     delete_option('handl_fb_pixel_id') &&
		     delete_option('handl_fb_pixel_name')){
			$result['success'] = true;
		}

		wp_send_json($result);
	}

	public function unlink_pixel(){
		$result = [
			"success" => false,
		];

		if ( delete_option('handl_fb_pixel_id') &&
		     delete_option('handl_fb_pixel_name') ){
			$result['success'] = true;
		}

		wp_send_json($result);
	}

	public function list_acts(){
		if ($this->is_authed()){
			$query_args = [
				"action" => "list_acts",
				"access_token" => $this->getAccessToken()
			];

//			$query_args = [
//				"action" => "list_events",
//				'pixel_id' => '393466015206782',
//				"access_token" => $this->getAccessToken()
//			];
			$query_args_str = http_build_query($query_args);
			$request = wp_remote_get($this->HANDL_FB_API_ENDPOINT."?".$query_args_str );
			$result = [
				"success" => true,
			];

			if( !is_wp_error( $request ) ) {
				$body = wp_remote_retrieve_body( $request );
				$data = json_decode( $body, true );
				if ($accountId = $this->getAccountId()){
					if (isset($data['result'])){
						foreach ($data['result'] as $id=>$d){
							if ($d['id'] == $accountId){
								$data['result'][$id]['selected'] = true;
							}
						}
					}
				}

				$result = array_merge($result, $data);
			}else{
				$result['success'] = false;
			}
		}else{
			$result['success'] = false;
			$result['msg'] = 'Not Authorized';
		}

		wp_send_json($result);
	}

	public function list_pixels(){
		if ($this->is_authed()){

			list($act_id,$act_name) = explode("||", $_POST["act_id"]);

			if ($act_id != ""){
				update_option( 'handl_fb_act_id', $act_id );
				update_option( 'handl_fb_act_name', $act_name );

				$query_args = [
					"action" => "list_pixels",
					'act_id' => $act_id,
					"access_token" => $this->getAccessToken()
				];
				$query_args_str = http_build_query($query_args);
				$request = wp_remote_get($this->HANDL_FB_API_ENDPOINT."?".$query_args_str );
				$result = [
					"success" => true,
				];

				if( !is_wp_error( $request ) ) {
					$body = wp_remote_retrieve_body( $request );
					$data = json_decode( $body, true );
					$result = array_merge($result, $data);
				}else{
					$result['success'] = false;
				}
			}
		}else{
			$result['success'] = false;
			$result['msg'] = 'Not Authorized';
		}

		wp_send_json($result);
	}

	public function save_pixel_id(){
		$result = [
			"success" => false,
		];

		if ($this->is_authed()){

			list($pixel_id, $pixel_name) = explode("||", $_POST["pixel_id"]);

			if ($pixel_id != ""){
				update_option( 'handl_fb_pixel_id', $pixel_id );
				update_option( 'handl_fb_pixel_name', $pixel_name );

				$result['success'] = true;
			}
		}else{
			$result['success'] = false;
			$result['msg'] = 'Not Authorized';
		}

		wp_send_json($result);
	}

	public function fb_ready(){

		$result = [
			"is_authed" => false,
			"is_act_id_saved" => false,
			"is_pixel_saved" => false,
		];

		if ($this->is_authed()){
			$result['is_authed'] = true;
		}

		if ($this->is_act_id_saved()) {
			$result['is_act_id_saved'] = true;
			$result['act'] = [
				'id'=> $this->getAccountId(),
				'name' => stripslashes($this->getAccountName())
			];
		}

		if ($this->is_pixel_saved()){
			$result['is_pixel_saved'] = true;
			$result['pixel'] = [
				'id'=> $this->getPixelId(),
				'name' => stripslashes($this->getPixelName())
			];
		}

		wp_send_json($result);
	}

	function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
	{
		$str = str_replace('_', '', ucwords($string, '_'));
		return $str;
	}

	public function send_offline_conv($postID){

		if (isset($_POST['post_id'])){
			$postID = $_POST['post_id'];
		}

		if (isset($_POST['is_test']) && $_POST['is_test'] != ''){
			$test = $_POST['is_test'];
		}

		if ($this->is_authed() && is_numeric($postID)) {
			$this->sendOfflineConversion($postID, $test);
		}
	}

	public function woo_fb_offline_conversion(){

		if ($this->is_authed() && $this->is_pixel_saved()) {
			global $post;
			print "
					Click here to send it as a test event<br/>
					<input name='is_fb_offline_conv_test' type='checkbox' id='is_fb_offline_conv_test' onclick='undisableNextField(this)' />
					<input name='fb_offline_conv_code' id='fb_offline_conv_code' placeholder='TEST CODE' disabled/> <br>
					<div id='fb_offline_conv_result'></div> <br/><br>
					
					<button type='button' class='button' onclick='sendOfflineConversion($post->ID)'>Send to FB CAPI</button>
					
					<script>
					function undisableNextField(thiss){
    					jQuery(thiss).next().prop('disabled', false).focus()
					}
					
					function sendOfflineConversion(postID){
					    jQuery.post(
	                        ajaxurl,
	                        {
	                            'action': 'handl_fb_send_offline_conv',
	                            'post_id': postID,
	                            'is_test': jQuery('#fb_offline_conv_code').val()
	                        },
	                        function(response) {
	                           	var msg = ''
	                            if (response.success){
	                                msg = 'Successful'
	                            }else{
	                                msg = 'Error: '+response.error
	                            }
	                            jQuery('#fb_offline_conv_result').html(msg)
	                        }
                    	);
					}
					</script>
			";
		}else{
			print "Please go to <a href='?page=handl-utm-grabber.php&tab=woo-postback'>WooCommerce Postback</a> and authorize your Facebook account and select a data source (pixel) to use.";
		}
	}

	public function sendOfflineConversion($postID, $test = false, $hook = 'payment_complete', $action = false) {
		$result = [
			'success' => false
		];
	
		$order = wc_get_order($postID);
		$orig_args = wp_parse_args($this->getDefaultPayload());
	
		$data = [
			"user" => [],
			"event" => [],
			"custom" => []
		];
	
		foreach (['user', 'event', 'custom'] as $param) {
			if ($orig_args[$param]) {
				$data_arr = HandLWooCommerceParseQuery($orig_args[$param], $order);
				foreach ($data_arr as $key => $value) {
					if ($key == 'event_time') {
						$value = strtotime($value);
					}
	
					if (in_array($key, ['em', 'ph'])) {
						$value = (array)$value;
					}
	
					$new_value = $this->normalize($key, $value);
					$new_value = $this->hash($key, $new_value);
	
					$data[$param][$key] = $new_value;
				}
			}
		}
	
		if (!$data['event']['event_time']) {
			$date = $order->get_date_completed();
			$order_date = $date->getTimestamp();
			$data['event']['event_time'] = $order_date;
		}
	
		$payload_data = array_merge($data['event'], [
			"user_data" => $data["user"],
			"custom_data" => $data["custom"]
		]);
	
		$result = $this->sendFBConversion($payload_data, $test);
	
		if (!$action) {
			wp_send_json($result);
		}
	
		return $result;
	}
	
	private function getDefaultPayload() {
		return 'user[em]=wc|data__billing__email&user[ct]=wc|data__billing__city&user[country]=wc|data__billing__country&user[fn]=wc|data__billing__first_name&user[ln]=wc|data__billing__last_name&user[st]=wc|data__billing__state&user[zp]=wc|data__billing__postcode&user[ph]=wc|data__billing__phone&user[fbp]=wc|meta___fbp&user[fbc]=wc|meta___fbc&user[client_ip_address]=wc|data__customer_ip_address&user[client_user_agent]=wc|data__customer_user_agent&custom[currency]=wc|data__currency&custom[value]=wc|data__total&custom[order_id]=wc|data__order_key&event[event_name]=Purchase&event[event_time]=now&event[event_id]=wc|data__order_key';
	}

	private function enrichPayload($payload) {
		if (!isset($payload['user_data']) || !is_array($payload['user_data'])) {
			$payload['user_data'] = [];
		}

		$user_data = &$payload['user_data'];

		$fields_to_check = [
			'fbc' => '_fbc',
			'fbp' => '_fbp',
			'client_ip_address' => 'handl_ip',
			'client_user_agent' => 'user_agent'
		];

		foreach ($fields_to_check as $field => $cookie_name) {
			if (!isset($user_data[$field]) || empty($user_data[$field])) {
				if (isset($_COOKIE[$cookie_name])) {
					$user_data[$field] = $_COOKIE[$cookie_name];
				}
			}
		}

		// Enrich with user data if logged in
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();

			$user_fields = [
				'em' => [ $current_user->user_email ],
				'fn' => $current_user->first_name,
				'ln' => $current_user->last_name,
			];

			// Check if WooCommerce is active and functions are available
			if (function_exists('WC') && function_exists('wc_get_customer_billing_email')) {
				$customer = new WC_Customer($current_user->ID);
				$user_fields = array_merge($user_fields, [
					'ph' => [ $customer->get_billing_phone() ],
					'ct' => $customer->get_billing_city(),
					'st' => $customer->get_billing_state(),
					'zp' => $customer->get_billing_postcode(),
					'country' => $customer->get_billing_country()
				]);
			}
		}

		// Update the payload with the enriched user_data
		$payload['user_data'] = $user_data;

		return $payload;
	}

	private function normalizeAndHasUserData($user_data) {
		foreach ($user_data as $field => $value) {
			if (!empty($value)) {
				$user_data[$field] = $this->normalize($field, $value);
				$user_data[$field] = $this->hash($field, $user_data[$field]);
			}
		}
		return $user_data;
	}

	public function sendFBConversion($payload, $test = false) {
		$result = [
			'success' => false
		];
	
		$pixel_id = $this->getPixelId();
		$access_token = $this->getAccessToken();

		// Enrich the payload with additional user data
		$payload = $this->enrichPayload($payload);
		$not_hashed_payload = $payload;

		// Normalize and hash user data
		if (isset($payload['user_data']) && is_array($payload['user_data'])) {
			$payload['user_data'] = $this->normalizeAndHasUserData($payload['user_data']);
		}

		if (!isset($payload['event_time'])) {
			$payload['event_time'] = time();
		}

		$fb_payload = [
			"access_token" => $access_token,
			"data" => [json_encode($payload)],
		];
	
		if ($test) {
			$fb_payload['test_event_code'] = $test;
		}
	
		if (WP_DEBUG) {
			error_log(print_r($fb_payload, true));
		}
	
		try {
			$endpoint_url = $this->FB_ENDPOINT_URL . '/' . $pixel_id . "/events";
			$response = wp_remote_post($endpoint_url, array(
				'method' => 'POST',
				'timeout' => 45,
				'body' => $fb_payload,
			));
	
			if (WP_DEBUG) {
				error_log(print_r($response, true));
			}
	
			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$result['error'] = $error_message;
			} else {
				$body = json_decode($response['body'], true);
	
				if (isset($body['events_received'])) {
					$result['success'] = true;
				}
	
				if (isset($body['error'])) {
					$result['error'] = $body['error']["message"]." (".$body['error']["error_user_msg"].")";
				}
	
				if (isset($body['error']["code"]) && $body['error']["code"] == '190') {
					$this->unlink_fb(false);
				}
			}
		} catch (Exception $e) {
			if (WP_DEBUG) {
				error_log(print_r($e, true));
			}
			$result['error'] = $e->getMessage();
		}
	
		// Log the conversion attempt
		handl_log_fb_conversion($payload['event_name'], $not_hashed_payload, $result);
	
		return $result;
	}
	
	public function normalize($key, $value){
		if ($key == 'em'){
			foreach ((array)$value as $i=>$v){
				$value[$i] = trim(strtolower($value[$i]), " \t\r\n\0\x0B.");
			}
		}elseif (in_array($key, ['country','ct','st','fn','ln'])){
			$value = preg_replace('/[^a-z]/', '', strtolower(trim($value)));
		}elseif (in_array($key, ['zp'])){
			$value = explode('-', preg_replace('/[ ]/', '', strtolower(trim($value))))[0];
		}elseif(in_array($key,['ph','ge','db','external_id'])){
			if ($key == 'ph'){
				foreach ((array)$value as $i=>$v){
					$value[$i] = preg_replace( array('/\(/','/\)/','/-/','/\s+/','/\+/') ,'', $value[$i] );
					$value[$i] = trim(strtolower($value[$i]));
				}
			}else{
				$value = trim(strtolower($value));
			}
		}
		return $value;
	}

	public function hash($key, $value){
		if (in_array($key, ['em','ph','ge','db','ln','fn','ct','st','zp','country','external_id'])){
			if (is_array($value)){
				foreach ((array)$value as $i=>$v){
					$value[$i] = hash('sha256', $value[$i], false);
				}
			}else{
				$value = hash('sha256', $value, false);
			}
		}
		return $value;
	}
}

new HandLFacebookAds();