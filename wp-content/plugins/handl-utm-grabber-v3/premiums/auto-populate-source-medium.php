<?php

function register_handl_auto_populate_source_medium() {
    register_setting('handl-utm-grabber-settings-group', 'handl_auto_populate_source_medium');
}
add_action('admin_init', 'register_handl_auto_populate_source_medium');

function add_auto_populate_source_medium_option() {
    global $handl_fields_disabled;
    ?>
    <tr>
        <th scope='row'>Auto-Populate Source/Medium</th>
        <td>
            <fieldset>
                <legend class='screen-reader-text'>
                    <span>Auto-Populate Source/Medium</span>
                </legend>
                <label for='handl_auto_populate_source_medium'>
                    <input name='handl_auto_populate_source_medium' 
                           id='handl_auto_populate_source_medium' 
                           type='checkbox' 
                           value='1' 
                           <?php checked('1', get_option('handl_auto_populate_source_medium')); ?> 
                           <?php echo $handl_fields_disabled; ?> />
                    Automatically set utm_source and utm_medium for organic and referral traffic
                    <p class="description">When enabled, utm_source will be set to traffic_source value and utm_medium will be set to organic_source_str value</p>
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
}
add_filter("insert_rows_to_handl_options", "add_auto_populate_source_medium_option", 10);

function handl_auto_populate_source_medium() {
    if (get_option('handl_auto_populate_source_medium') !== '1') {
        return;
    }

    $traffic_source = isset($_COOKIE['traffic_source']) ? $_COOKIE['traffic_source'] : '';
    $organic_source = isset($_COOKIE['organic_source_str']) ? $_COOKIE['organic_source_str'] : '';

    if (!empty($organic_source) && !isset($_COOKIE['utm_source'])) {
        HandLCreateParameters('utm_source', strtolower($organic_source), getDomainName());
        HandLCreateParameters('first_utm_source', strtolower($organic_source), getDomainName());
    }

    if (!empty($traffic_source) && !isset($_COOKIE['utm_medium'])) {
        HandLCreateParameters('utm_medium', strtolower($traffic_source), getDomainName());
        HandLCreateParameters('first_utm_medium', strtolower($traffic_source), getDomainName());
    }
}
// add_action('after_handl_capture_utms', 'handl_auto_populate_source_medium');


function handl_auto_populate_source_medium_script() {
    if (get_option('handl_auto_populate_source_medium') === '1') {
        ?>
        <script>
        document.addEventListener('handl_cookies_set', function(event) {
            if (!Cookies.get('utm_source')) {
                SetRefLink('utm_source', Cookies.get('organic_source_str').toLowerCase(), true, 0);
                SetRefLink('first_utm_source', Cookies.get('organic_source_str').toLowerCase(), true, 0);
            }
            
            if (!Cookies.get('utm_medium')) {
                SetRefLink('utm_medium', Cookies.get('traffic_source').toLowerCase(), true, 0);
                SetRefLink('first_utm_medium', Cookies.get('traffic_source').toLowerCase(), true, 0);
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'handl_auto_populate_source_medium_script', 100);