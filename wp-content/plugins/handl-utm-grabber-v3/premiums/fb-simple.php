<?php
function add_fb_simple_special_to_tabs($tabs){
	array_push($tabs, array( 'fb-simple-setup' => __( 'Facebook CAPI', 'handlutmgrabber' ) ) );
	return $tabs;
}
add_filter('filter_admin_tabs','add_fb_simple_special_to_tabs', 11, 1);

function handl_fb_simple_params(){
	register_setting( 'handl-utm-grabber-fb_simple-group', 'handl_fb_pixel_id' );
	register_setting( 'handl-utm-grabber-fb_simple-group', 'handl_fb_access_token' );
	register_setting( 'handl-utm-grabber-fb_simple-group', 'handl_fb_capi_enabled' );
}
add_action( 'admin_init', 'handl_fb_simple_params' );

function getFBSimpleSpecialContent(){
	global $handl_active, $handl_fields_disabled;
	?>
    <form method='post' action='options.php'>
		<?php settings_fields( 'handl-utm-grabber-fb_simple-group' ); ?>
		<?php do_settings_sections( 'handl-utm-grabber-fb_simple-group' ); ?>
		<?php do_action('maybe_dispay_license_error_notice') ?>
        <h2>Support Coverage/Disclaimer</h2>
        <p class="description"><a href="https://docs.utmgrabber.com/books/102-getting-started-for-handl-utm-grabber-v3/page/facebook-conversion-api-fb-capi" target="_blank">See here</a> the list of plugins we support for the Facebook Conversion API. If you don't see your plugin listed, let us know, and we'd be happy to support it.</p>
        <table class='form-table'>
            <tr>
                <th scope='row'>Enable Facebook CAPI</th>
                <td>
                    <fieldset>
                        <legend class='screen-reader-text'>
                            <span>Enable Facebook CAPI</span>
                        </legend>
                        <label for='handl_fb_capi_enabled'>
                            <input name='handl_fb_capi_enabled' id='handl_fb_capi_enabled' type='checkbox' value='1' <?php checked(get_option('handl_fb_capi_enabled'), '1'); ?> <?php print $handl_fields_disabled;?>/>
                            Enable Facebook Conversion API (CAPI) functionality
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope='row'>FB Pixel ID</th>
                <td>
                    <fieldset>
                        <legend class='screen-reader-text'>
                            <span>FB Pixel ID</span>
                        </legend>
                        <label for='handl_fb_pixel_id'>
                            <input style="width: 500px" name='handl_fb_pixel_id' id='handl_fb_pixel_id' type='text' value='<?php print get_option( 'handl_fb_pixel_id' ) ? get_option( 'handl_fb_pixel_id' ) : '' ?>' <?php print $handl_fields_disabled;?>/>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope='row'>FB Access Token</th>
                <td>
                    <fieldset>
                        <legend class='screen-reader-text'>
                            <span>FB Access Token</span>
                        </legend>
                        <label for='handl_fb_access_token'>
                            <textarea style="width: 500px" name='handl_fb_access_token' id='handl_fb_access_token' rows="4" <?php print $handl_fields_disabled;?>><?php print get_option( 'handl_fb_access_token' ) ? get_option( 'handl_fb_access_token' ) : '' ?></textarea>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
		<?php submit_button(null, 'primary', 'submit', true, $handl_active ? '' : 'disabled'); ?>
    </form>

    <h2>Facebook Conversion API Logs (Last 100)</h2>
    <?php handl_fb_conversion_logs_table(); ?>

	<?php
}
add_filter( 'get_admin_tab_content_fb-simple-setup', 'getFBSimpleSpecialContent', 10 );

function HandLFBSimple(){
	if ( isset($_POST['fb-simple-webhook-secret']) && $_POST['fb-simple-webhook-secret'] == "0fdc169ffec1897a148dc0622b92fbcefaf1ed06"){
		SendWDataToFBConversion($_POST);
	}
}
add_action('init', 'HandLFBSimple');

if (!function_exists('SendWDataToFBConversion')) {
	function SendWDataToFBConversion( $w_data ) {
		if ( get_option( 'handl_fb_pixel_id' ) && get_option( 'handl_fb_access_token' ) ) {
//			$test = "TEST52643";

			update_option('handl_fb_simple_raw_data', $w_data);

			$fb_handl = new HandLFacebookAds();

			$result = [
				'success'=> false
			];

			$pixel_id = $fb_handl->getPixelId();
			$access_token = $fb_handl->getAccessToken();

			if (WP_DEBUG) {
                error_log($pixel_id);
                error_log($access_token);
                error_log(print_r($w_data, 1));
			}

			$payload = 'user[em]=email_addresses&user[ct]=locality&user[country]=country_code&user[fn]=given_name&user[ln]=family_name&user[st]=state&user[zp]=postal_code&user[ph]=phone_numbers&user[fbp]=_fbp&user[fbc]=_fbc&user[client_ip_address]=handl_ip&user[client_user_agent]=user_agent&custom[currency]=currency&custom[value]=value&event[event_name]=event_name&event[event_time]=now&event[event_id]=event_id';

			$orig_args = wp_parse_args($payload);
//			print_r($new_data);
//			print_r($orig_args);

			$data = [
				"user" => [],
				"event" => [],
				"custom" => []
			];

			foreach(['user','event','custom'] as $param){

				if ($orig_args[$param]){
					foreach ($orig_args[$param] as $key=>$value){

						if ( isset($w_data[$value]) || ( $param == 'event' || $param == 'custom' ) ) {

							if ( $key == 'event_time' ) {
								$value = strtotime( $value );
							}

							if ( isset($w_data[$value]) )
								$value = $w_data[ $value ];

							if (in_array($key, ['em','ph'])){
								$value = (array)$value;
							}

							$new_value = $fb_handl->normalize( $key, $value );
							$new_value = $fb_handl->hash($key, $new_value);

							$data[ $param ][ $key ] = $new_value;
						}else{
//							print $key." ".$value."<br>";
						}
					}
				}
			}

			$payload_data = [];
			$payload_data = array_merge($payload_data, $data['event']);
			$payload_data["user_data"] = $data["user"];
			$payload_data["custom_data"] = $data["custom"];

			$payload = [
				"access_token" => $access_token,
				"data" => [ json_encode($payload_data) ],
			];

			if (isset($_POST['test_event_code']) && $_POST['test_event_code'] != ''){
				$payload['test_event_code'] = $_POST['test_event_code'];
			}

			update_option('handl_fb_simple_fb_payload', $payload);

			if (WP_DEBUG){
				error_log(print_r($payload, true));
			}

			try{
				$endpoint_url = $fb_handl->FB_ENDPOINT_URL.'/'.$pixel_id."/events";
				$response = wp_remote_post( $endpoint_url, array(
						'method'      => 'POST',
						'timeout'     => 45,
						'body'        => $payload,
					)
				);

				update_option('handl_fb_simple_fb_result', $response);

				if (WP_DEBUG){
					error_log(print_r($response, true));
				}

				if ( is_wp_error( $response ) ) {
//				dd("test1");
					$error_message = $response->get_error_message();
				} else {
					$body = json_decode($response['body'], true);

					if (isset($body['events_received'])){
						$result['success'] = true;
					}

					if (isset($body['error'])){
						$result['error'] = $body['error']["message"];
						update_option('handl_fb_simple_fb_error', $body['error']["message"]);
					}

				}
			} catch (Exception $e) {
				if (WP_DEBUG){
					error_log(print_r($e, true));
					update_option('handl_fb_simple_fb_error', $e);
				}
			}
		}
	}
}

function handl_log_fb_conversion($event_name, $payload, $response) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'event_name' => $event_name,
        'payload' => $payload,
        'response' => $response,
        'status' => isset($response['success']) && $response['success'] ? 'Success' : 'Failed',
        'error' => isset($response['error']) ? $response['error'] : null,
    );

    $existing_logs = get_option('handl_fb_conversion_logs', array());
    array_unshift($existing_logs, $log_entry); // Add new log entry to the beginning of the array
    
    // Keep only the last 100 log entries
    $existing_logs = array_slice($existing_logs, 0, 100);

    update_option('handl_fb_conversion_logs', $existing_logs);
}

function handl_fb_conversion_logs_table() {
    $logs = get_option('handl_fb_conversion_logs', array());
    ?>
    <table class="widefat handl-fb-logs-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th class="timestamp-col">Timestamp</th>
                <th class="event-name-col">Event Name</th>
                <th class="status-col">Status</th>
                <th class="error-col">Error</th>
                <th class="details-col">Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5">No logs available.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="timestamp-col"><?php echo esc_html($log['timestamp']); ?></td>
                        <td class="event-name-col"><?php echo esc_html($log['event_name']); ?></td>
                        <td class="status-col">
                            <?php if ($log['status'] === 'Success'): ?>
                                <span style="color: green;">✓ Success</span>
                            <?php else: ?>
                                <span style="color: red;">✗ Failed</span>
                            <?php endif; ?>
                        </td>
                        <td class="error-col"><?php echo esc_html($log['error'] ?? 'N/A'); ?></td>
                        <td class="details-col">
                            <button class="button" onclick="toggleDetails(this)">Show Details</button>
                            <div class="log-details" style="display: none;">
                                <h4>Payload:</h4>
                                <pre><?php echo esc_html(json_encode($log['payload'], JSON_PRETTY_PRINT)); ?></pre>
                                <h4>Response:</h4>
                                <pre><?php echo esc_html(json_encode($log['response'], JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <script>
    function toggleDetails(button) {
        var details = button.nextElementSibling;
        if (details.style.display === "none") {
            details.style.display = "block";
            button.textContent = "Hide Details";
        } else {
            details.style.display = "none";
            button.textContent = "Show Details";
        }
    }
    </script>
    <?php
}