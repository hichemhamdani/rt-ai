<?php

function register_session_cookies_option() {
    register_setting('handl-utm-grabber-settings-group', 'enable_session_cookies');
}
add_action('admin_init', 'register_session_cookies_option');

function add_session_cookies_option() {
    global $handl_fields_disabled;
    ?>
    <tr>
        <th scope='row'>Enable Session Cookies</th>
        <td>
            <fieldset>
                <legend class='screen-reader-text'>
                    <span>Enable Session Cookies</span>
                </legend>
                <label for='enable_session_cookies'>
                    <input name='enable_session_cookies' id='enable_session_cookies' type='checkbox' value='1' <?php checked('1', get_option('enable_session_cookies')); ?> <?php echo $handl_fields_disabled; ?> />
                    Use session cookies instead of persistent cookies
                    <p class="description">When enabled, cookies will expire when the browser session ends instead of after a set number of days.</p>
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
}
add_filter("insert_rows_to_handl_options", "add_session_cookies_option", 10); 