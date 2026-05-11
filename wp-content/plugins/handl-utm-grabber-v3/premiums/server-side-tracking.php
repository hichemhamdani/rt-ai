<?php

function add_disable_server_side_tracking_option() {
    global $handl_fields_disabled;
    ?>
    <tr>
        <th scope='row'>Disable Server Side Tracking</th>
        <td>
            <fieldset>
                <legend class='screen-reader-text'>
                    <span>Disable Server Side Tracking</span>
                </legend>
                <label for='disable_server_side_tracking'>
                    <input name='disable_server_side_tracking' id='disable_server_side_tracking' type='checkbox' value='1' <?php checked('1', get_option('disable_server_side_tracking')); ?> <?php echo $handl_fields_disabled; ?> />
                    Disable server side tracking
                    <p class="description">If you leverage server side caching and you think your tracking is impacted adversely, we recommend you try disabling server side tracking to fully rely on client side tracking.</p>
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
}
add_filter("insert_rows_to_handl_options", "add_disable_server_side_tracking_option", 10);

function register_disable_server_side_tracking_option() {
    register_setting('handl-utm-grabber-settings-group', 'disable_server_side_tracking');
}
add_action('admin_init', 'register_disable_server_side_tracking_option');
