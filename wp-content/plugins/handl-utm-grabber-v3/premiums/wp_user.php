<?php

function handl_insert_custom_user_meta($custom_meta, $user, $update, $userdata){
	$fields = generateUTMFields();
	foreach ( $fields as $field ) {
		if ( isset( $_COOKIE[ $field ] ) && $_COOKIE[ $field ] != '' ) {
//			if (in_array($field, ['email', 'username', 'password']))
			$custom_meta[$field] = esc_attr( $_COOKIE[ $field ] );
		}
	}
	return $custom_meta;
}
add_filter('insert_custom_user_meta', 'handl_insert_custom_user_meta', 10, 4);

function handl_registration_save($user_id){
    $user = get_userdata($user_id);
	$data = (array) $user->data;
	$data['user_registration'] = "true";
	$custom_data_raw = get_user_meta($user_id);
	$custom_data = array();
	foreach ($custom_data_raw as $key => $value) {
		$custom_data[$key] = $value[0];
	}
    $data = array_merge($data, $custom_data);
	unset($data['user_pass']);
    do_action('handl_post_data_to', $data);
}
add_action( 'user_register', 'handl_registration_save', 10, 1 );


function handl_show_user_profile( $user ) {

	if( ! current_user_can('edit_users') ) {
		return;
	}

	?>
	<table class="form-table">
		<?php
		$fields = generateUTMFields();
		$i = 0;
		foreach ($fields as $field) {
			if(  $handlValue = get_user_meta( $user->ID, $field, true ) ) {
				if ($i == 0){
					print "<h3>HandL UTM Grabber Fields</h3>";
				}
				print "<tr>
						<th><label>$field</label></th>
						<td>$handlValue</td>
					</tr>";
				$i++;
			}
		}
		?>
	</table>
	<?php
}
add_action( 'edit_user_profile', 'handl_show_user_profile' );