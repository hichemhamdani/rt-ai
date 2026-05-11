<?php


function register_cookie_duration(){
    register_setting( 'handl-utm-grabber-settings-group', 'cookie_duration' );
}
add_action( 'admin_init', 'register_cookie_duration' );

function add_cookie_duration(){
    global $handl_fields_disabled;
    ?>
<tr id="cookie-duration-row">
    <th scope='row'>Cookie Duration</th>
    <td>
        <fieldset>
            <legend class='screen-reader-text'>
                <span>Cookie Duration</span>
            </legend>
            <label for='cookie_duration'>
                <input name='cookie_duration' id='cookie_duration' type='number' 
                    value='<?php print get_option( 'cookie_duration' ) ? get_option( 'cookie_duration' ) : 30 ?>' 
                    <?php print $handl_fields_disabled;?>
                    <?php print get_option('enable_session_cookies') ? 'disabled' : ''; ?>
                /> days
                <p class="description" id="cookie-duration-description">
                    <?php if(get_option('enable_session_cookies')): ?>
                        Cookie duration is disabled because Session Cookies is enabled. Cookies will expire when the browser session ends.
                    <?php else: ?>
                        Specify how many days the cookies should persist.
                    <?php endif; ?>
                </p>
            </label>
        </fieldset>
    </td>
</tr>
<?php
}
add_filter("insert_rows_to_handl_options", "add_cookie_duration", 10);