<?php

function register_full_tracking_mode() {
    register_setting('handl-utm-grabber-full-tracking-group', 'handl_full_tracking_mode');
    register_setting('handl-utm-grabber-full-tracking-group', 'handl_full_tracking_tables_created');
}
add_action('admin_init', 'register_full_tracking_mode');

function handle_full_tracking_mode() {
    if (get_option('handl_full_tracking_mode') == '1') {
        $tables_created = get_option('handl_full_tracking_tables_created');
        if (!$tables_created) {
            create_full_tracking_tables();
            update_option('handl_full_tracking_tables_created', true);
        }
        add_action('wp_footer', 'add_full_tracking_script');
    }
}
add_action('init', 'handle_full_tracking_mode');

function create_full_tracking_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sessions_table = $wpdb->prefix . 'handl_sessions';
    $utm_sets_table = $wpdb->prefix . 'handl_utm_sets';
    $page_views_table = $wpdb->prefix . 'handl_page_views';

    $wpdb->query("DROP TABLE IF EXISTS $page_views_table");
    $wpdb->query("DROP TABLE IF EXISTS $utm_sets_table");
    $wpdb->query("DROP TABLE IF EXISTS $sessions_table");

    $sql = "
    CREATE TABLE $sessions_table (
      session_id VARCHAR(255) PRIMARY KEY,
      additional_session_data TEXT
    ) $charset_collate;

    CREATE TABLE $utm_sets_table (
      utm_set_id INT AUTO_INCREMENT PRIMARY KEY,
      utm_source VARCHAR(190),
      utm_medium VARCHAR(190),
      utm_campaign VARCHAR(190),
      utm_content VARCHAR(190),
      utm_term VARCHAR(190),
      UNIQUE KEY unique_utm_combination (
        utm_source(50),
        utm_medium(50),
        utm_campaign(50),
        utm_content(50),
        utm_term(50)
      )
    ) $charset_collate;

    CREATE TABLE $page_views_table (
      page_view_id INT AUTO_INCREMENT PRIMARY KEY,
      session_id VARCHAR(255),
      timestamp DATETIME,
      page_url TEXT,
      referrer TEXT,
      utm_set_id INT
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function add_full_tracking_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var utmParams = getAllHandLUTMParams();
        var sessionId = Cookies.get('handlID') || '';
        
        // Function to remove query parameters and anchors from URL
        function cleanUrl(url) {
            var urlParts = url.split('#');
            return urlParts[0].split('?')[0];
        }
        
        $.ajax({
            url: handl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handl_record_page_view',
                session_id: sessionId,
                utm_params: utmParams,
                page_url: cleanUrl(window.location.href),
                referrer: cleanUrl(document.referrer)
            },
            success: function(response) {
                console.log('Page view recorded:', response);
            }
        });
    });
    </script>
    <?php
}

function handl_record_page_view() {
    global $wpdb;
    $sessions_table = $wpdb->prefix . 'handl_sessions';
    $utm_sets_table = $wpdb->prefix . 'handl_utm_sets';
    $page_views_table = $wpdb->prefix . 'handl_page_views';

    $session_id = $_POST['session_id'];
    $utm_params = $_POST['utm_params'];
    
    // The URLs should already be clean from the client-side, but we'll clean them again just to be sure
    $page_url = preg_replace('/[#?].*/', '', $_POST['page_url']);
    $referrer = preg_replace('/[#?].*/', '', $_POST['referrer']);

    // Insert or update session
    $wpdb->replace(
        $sessions_table,
        array('session_id' => $session_id),
        array('%s')
    );

    // Handle UTM parameters
    $utm_set_id = null;
    if (!empty($utm_params)) {
        $existing_utm_set = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT utm_set_id FROM $utm_sets_table WHERE utm_source = %s AND utm_medium = %s AND utm_campaign = %s AND utm_content = %s AND utm_term = %s",
                $utm_params['utm_source'] ?? '',
                $utm_params['utm_medium'] ?? '',
                $utm_params['utm_campaign'] ?? '',
                $utm_params['utm_content'] ?? '',
                $utm_params['utm_term'] ?? ''
            )
        );

        if ($existing_utm_set) {
            $utm_set_id = $existing_utm_set;
        } else {
            // Filter utm_params to only include standard UTM parameters
            $filtered_utm_params = array_intersect_key($utm_params, array_flip(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']));
            
            // Ensure all standard UTM parameters are present, even if empty
            $filtered_utm_params = array_merge(
                array_fill_keys(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'], ''),
                $filtered_utm_params
            );
            $wpdb->insert($utm_sets_table, $filtered_utm_params);
            $utm_set_id = $wpdb->insert_id;
        }
    }

    // Record the page view
    $wpdb->insert(
        $page_views_table,
        array(
            'session_id' => $session_id,
            'timestamp' => current_time('mysql'),
            'page_url' => esc_url_raw($page_url),
            'referrer' => esc_url_raw($referrer),
            'utm_set_id' => $utm_set_id
        )
    );

    wp_send_json_success('Page view recorded');
}
add_action('wp_ajax_handl_record_page_view', 'handl_record_page_view');
add_action('wp_ajax_nopriv_handl_record_page_view', 'handl_record_page_view');

function handle_full_tracking_mode_disabled() {
    if (get_option('handl_full_tracking_mode') != '1') {
        // delete_option('handl_full_tracking_tables_created');
    }
}
add_action('update_option_handl_full_tracking_mode', 'handle_full_tracking_mode_disabled');

function register_handl_full_tracking_tables_created() {
    register_setting('handl-utm-grabber-full-tracking-group', 'handl_full_tracking_tables_created');
}
add_action('admin_init', 'register_handl_full_tracking_tables_created');

function add_full_tracking_mode_to_tabs($tabs) {
    array_push($tabs, ['full-tracking-report' => __('🚀 Full Tracking Mode', 'handlutmgrabber')]);
    return $tabs;
}
add_filter('filter_admin_tabs', 'add_full_tracking_mode_to_tabs', 9998, 1);

function full_tracking_mode_tab_style() {
    echo '<style>
        .nav-tab-wrapper a[href$="full-tracking-report"] {
            background-color: #e6f3ff;
            border-color: #007cba;
            color: #007cba;
            font-weight: bold;
        }
    </style>';
}
add_action('admin_head', 'full_tracking_mode_tab_style');

function get_full_tracking_report_content() {
    if (!get_option('handl_full_tracking_mode')) {
        get_full_tracking_mode_content();
        return;
    }

    global $wpdb;
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-theme');
    wp_enqueue_style('jquery-ui-dialog');
    wp_enqueue_script('handl-utm-grabber-chartjs');    
    get_full_tracking_mode_content();
    ?>
    <div class="wrap">
        <h2>Full Tracking Mode Report</h2>
        
        <div id="full-tracking-filters">
            <label for="date-range">Date Range:</label>
            <input type="text" id="start-date" class="date-picker" placeholder="Start Date">
            <input type="text" id="end-date" class="date-picker" placeholder="End Date">
            
            <label for="page-url">Page URL:</label>
            <input type="text" id="page-url" placeholder="Enter Page URL">
            
            <label for="session-id">Session ID:</label>
            <input type="text" id="session-id" placeholder="Enter Session ID">
            
            <button id="apply-filters" class="button button-primary">Apply Filters</button>
            <button id="export-csv" class="button button-primary">Export to CSV</button>
        </div>
        
        <div id="session-counts">
            <h3>Session Counts by Page</h3>
            <!-- <canvas id="session-counts-chart"></canvas> -->
            <table id="session-counts-table" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Page URL</th>
                        <th>Unique Sessions</th>
                        <th>Total Visits</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <div id="user-journey">
        <h3>User Journey</h3>
        <table id="user-journey-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Unique Sessions</th>
                    <th>Total Visits</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated via AJAX -->
            </tbody>
        </table>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.date-picker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        
        function loadReportData() {
            var data = {
                action: 'handl_full_tracking_report',
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
                page_url: $('#page-url').val(),
                session_id: $('#session-id').val()
            };
            
            $.post(ajaxurl, data, function(response) {
                // Update Session Counts table and chart
                updateSessionCountsData(response.session_counts);
                // Update User Journey table
                updateUserJourneyData(response.user_journey);
            });
        }

        $('#apply-filters').on('click', loadReportData);
        
        // Initial load
        loadReportData();

        $('#export-csv').on('click', function() {
            var data = {
                action: 'handl_export_full_tracking_csv',
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
                page_url: $('#page-url').val(),
                session_id: $('#session-id').val()
            };
            
            // Create a form and submit it to trigger file download
            var form = $('<form method="POST" action="' + ajaxurl + '">');
            $.each(data, function(key, value) {
                form.append($('<input>').attr('type', 'hidden').attr('name', key).val(value));
            });
            $('body').append(form);
            form.submit();
            form.remove();
        });
    });

    function updateUserJourneyData(data) {
        var tableBody = jQuery('#user-journey-table tbody');
        tableBody.empty();
        data.forEach(function(row) {
            tableBody.append('<tr>' +
                '<td><a href="#" class="session-id-drill-down">' + row.session_id + '</a></td>' +
                '<td>' + row.unique_sessions + '</td>' +
                '<td>' + row.total_visits + '</td>' +
            '</tr>' +
            '<tr class="accordion-content"><td colspan="3"><div class="session-details"></div></td></tr>');
        });

        // Add click event for drill-down
        jQuery('.session-id-drill-down').on('click', function(e) {
            e.preventDefault();
            var sessionId = jQuery(this).text();
            var contentRow = jQuery(this).closest('tr').next('.accordion-content');
            var sessionDetails = contentRow.find('.session-details');

            if (contentRow.is(':visible')) {
                contentRow.hide();
            } else {
                jQuery('.accordion-content').hide();
                contentRow.show();
                if (sessionDetails.is(':empty')) {
                    loadSessionDetails(sessionId, sessionDetails);
                }
            }
        });
    }

    function loadSessionDetails(sessionId, container) {
        container.html('Loading...');
        jQuery.post(ajaxurl, {
            action: 'handl_session_details_report',
            session_id: sessionId
        }, function(response) {
            displaySessionDetails(sessionId, response, container);
        });
    }

    function displaySessionDetails(sessionId, data, container) {
        var content = '<h3>Session Details for ' + sessionId + '</h3>';
        
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'page_url'].forEach(function(param) {
            content += '<h4>' + param.replace('utm_', '').charAt(0).toUpperCase() + param.replace('utm_', '').slice(1) + '</h4>';
            content += '<table class="wp-list-table widefat fixed striped">';
            content += '<thead><tr><th>' + param + '</th><th>Visits</th></tr></thead>';
            content += '<tbody>';
            data[param].forEach(function(row) {
                content += '<tr>';
                content += '<td>' + (row[param] || 'N/A') + '</td>';
                content += '<td>' + row.visits + '</td>';
                content += '</tr>';
            });
            content += '</tbody></table>';
        });

        container.html(content);
    }
    
    function updateSessionCountsData(data) {
        var tableBody = jQuery('#session-counts-table tbody');
        tableBody.empty();
        data.forEach(function(row) {
            tableBody.append('<tr class="accordion-row">' +
                '<td><a href="#" class="page-url-drill-down">' + row.page_url + '</a></td>' +
                '<td>' + row.unique_sessions + '</td>' +
                '<td>' + row.total_visits + '</td>' +
            '</tr>' +
            '<tr class="accordion-content"><td colspan="3"><div class="utm-breakdown"></div></td></tr>');
        });

        // Add click event for drill-down
        jQuery('.page-url-drill-down').on('click', function(e) {
            e.preventDefault();
            var pageUrl = jQuery(this).text();
            var contentRow = jQuery(this).closest('tr').next('.accordion-content');
            var utmBreakdown = contentRow.find('.utm-breakdown');

            if (contentRow.is(':visible')) {
                contentRow.hide();
            } else {
                jQuery('.accordion-content').hide();
                contentRow.show();
                if (utmBreakdown.is(':empty')) {
                    loadUtmBreakdown(pageUrl, utmBreakdown);
                }
            }
        });
    }

    function loadUtmBreakdown(pageUrl, container) {
        container.html('Loading...');
        jQuery.post(ajaxurl, {
            action: 'handl_utm_breakdown_report',
            page_url: pageUrl
        }, function(response) {
            displayUtmBreakdown(pageUrl, response, container);
        });
    }

    function displayUtmBreakdown(pageUrl, data, container) {
        var content = '<h3>UTM Breakdown for ' + pageUrl + '</h3>';
        
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer'].forEach(function(param) {
            content += '<h4>' + param.replace('utm_', '').charAt(0).toUpperCase() + param.replace('utm_', '').slice(1) + '</h4>';
            content += '<table class="wp-list-table widefat fixed striped">';
            content += '<thead><tr><th>' + param + '</th><th>Unique Sessions</th><th>Total Visits</th></tr></thead>';
            content += '<tbody>';
            data[param].forEach(function(row) {
                content += '<tr>';
                content += '<td>' + (row[param] || 'N/A') + '</td>';
                content += '<td>' + row.unique_sessions + '</td>';
                content += '<td>' + row.total_visits + '</td>';
                content += '</tr>';
            });
            content += '</tbody></table>';
        });

        container.html(content);
    }
    </script>
    <?php

    // Add Danger Zone section
    ?>
    <div class="wrap">
        <h2 style="color: #d63638;">Danger Zone</h2>
        <div id="handl-danger-zone" style="background-color: #f8d7da; border: 1px solid #f5c2c7; padding: 15px; margin-top: 20px;">
            <h3>Database Tables Statistics</h3>
            <?php
            global $wpdb;
            $sessions_table = $wpdb->prefix . 'handl_sessions';
            $utm_sets_table = $wpdb->prefix . 'handl_utm_sets';
            $page_views_table = $wpdb->prefix . 'handl_page_views';

            $tables = [$sessions_table, $utm_sets_table, $page_views_table];
            foreach ($tables as $table) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                echo "<p><strong>" . esc_html($table) . ":</strong> " . number_format($count) . " rows</p>";
            }
            ?>
            <p style="color: #842029;"><strong>Warning:</strong> Removing these tables will delete all collected tracking data. This action cannot be undone.</p>
            <button id="remove-tracking-tables" class="button button-danger" style="background-color: #dc3545; color: #fff; border-color: #dc3545;">Remove Tracking Tables</button>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#remove-tracking-tables').on('click', function() {
            if (confirm('Are you sure you want to remove all tracking tables? This action cannot be undone.')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'handl_remove_tracking_tables'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Tracking tables have been removed successfully.');
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }
        });
    });
    </script>
    <?php
}
add_filter('get_admin_tab_content_full-tracking-report', 'get_full_tracking_report_content');

function handl_full_tracking_report() {
    global $wpdb;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $page_url = $_POST['page_url'];
    $session_id = $_POST['session_id'];
    
    // Build the WHERE clause based on filters
    $where = [];
    if ($start_date) $where[] = $wpdb->prepare("timestamp >= %s", $start_date);
    if ($end_date) $where[] = $wpdb->prepare("timestamp <= %s", $end_date);
    if ($page_url) $where[] = $wpdb->prepare("page_url LIKE %s", '%' . $wpdb->esc_like($page_url) . '%');
    if ($session_id) $where[] = $wpdb->prepare("session_id = %s", $session_id);
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Query for UTM Sets driving traffic
    $utm_sets_query = "
        SELECT pv.page_url, us.utm_source, us.utm_medium, us.utm_campaign, us.utm_content, us.utm_term, COUNT(*) as visit_count
        FROM {$wpdb->prefix}handl_page_views pv
        JOIN {$wpdb->prefix}handl_utm_sets us ON pv.utm_set_id = us.utm_set_id
        $where_clause
        GROUP BY pv.page_url, us.utm_set_id
        ORDER BY visit_count DESC
        LIMIT 100
    ";
    $utm_sets_results = $wpdb->get_results($utm_sets_query, ARRAY_A);
    
    // Query for session counts
    $session_counts_query = "
        SELECT page_url, 
               COUNT(DISTINCT session_id) as unique_sessions, 
               COUNT(*) as total_visits
        FROM {$wpdb->prefix}handl_page_views
        $where_clause
        GROUP BY page_url
        ORDER BY total_visits DESC
        LIMIT 100
    ";
    $session_counts_results = $wpdb->get_results($session_counts_query, ARRAY_A);
    
    // Query for user journey
    $user_journey_query = "
        SELECT session_id, 
            COUNT(DISTINCT page_url) as unique_sessions, 
            COUNT(*) as total_visits
        FROM {$wpdb->prefix}handl_page_views
        $where_clause
        GROUP BY session_id
        ORDER BY total_visits DESC
        LIMIT 100
    ";
    $user_journey_results = $wpdb->get_results($user_journey_query, ARRAY_A);

    wp_send_json([
        'utm_sets' => $utm_sets_results,
        'session_counts' => $session_counts_results,
        'user_journey' => $user_journey_results
    ]);
}
add_action('wp_ajax_handl_full_tracking_report', 'handl_full_tracking_report');

function handl_page_referrer_report() {
    global $wpdb;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $page_url = $_POST['page_url'];
    $session_id = $_POST['session_id'];
    
    // Build the WHERE clause based on filters
    $where = [];
    if ($start_date) $where[] = $wpdb->prepare("timestamp >= %s", $start_date);
    if ($end_date) $where[] = $wpdb->prepare("timestamp <= %s", $end_date);
    if ($page_url) $where[] = $wpdb->prepare("page_url LIKE %s", '%' . $wpdb->esc_like($page_url) . '%');
    if ($session_id) $where[] = $wpdb->prepare("session_id = %s", $session_id);
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "
        SELECT page_url, referrer, 
               COUNT(*) as total_count, 
               COUNT(DISTINCT session_id) as unique_count
        FROM {$wpdb->prefix}handl_page_views
        $where_clause
        GROUP BY page_url, referrer
        ORDER BY total_count DESC
        LIMIT 100
    ";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    wp_send_json($results);
}
add_action('wp_ajax_handl_page_referrer_report', 'handl_page_referrer_report');

function handl_utm_micro_tables_report() {
    global $wpdb;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $page_url = $_POST['page_url'];
    $session_id = $_POST['session_id'];
    
    $utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
    $results = [];

    // Build the WHERE clause based on filters
    $where = [];
    if ($start_date) $where[] = $wpdb->prepare("pv.timestamp >= %s", $start_date);
    if ($end_date) $where[] = $wpdb->prepare("pv.timestamp <= %s", $end_date);
    if ($page_url) $where[] = $wpdb->prepare("pv.page_url LIKE %s", '%' . $wpdb->esc_like($page_url) . '%');
    if ($session_id) $where[] = $wpdb->prepare("pv.session_id = %s", $session_id);
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    foreach ($utm_params as $utm_param) {
        $query = "
            SELECT pv.page_url, us.$utm_param,
                   COUNT(DISTINCT pv.session_id) as unique_sessions,
                   COUNT(*) as total_visits
            FROM {$wpdb->prefix}handl_page_views pv
            JOIN {$wpdb->prefix}handl_utm_sets us ON pv.utm_set_id = us.utm_set_id
            $where_clause
            GROUP BY pv.page_url, us.$utm_param
            ORDER BY total_visits DESC
            LIMIT 100
        ";
        $results[$utm_param] = $wpdb->get_results($query, ARRAY_A);
    }
    
    wp_send_json($results);
}
add_action('wp_ajax_handl_utm_micro_tables_report', 'handl_utm_micro_tables_report');

function handl_utm_breakdown_report() {
    global $wpdb;
    $page_url = $_POST['page_url'];
    
    $utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
    $results = [];

    foreach ($utm_params as $utm_param) {
        $query = $wpdb->prepare("
            SELECT us.$utm_param, COUNT(DISTINCT pv.session_id) as unique_sessions, COUNT(*) as total_visits
            FROM {$wpdb->prefix}handl_page_views pv
            JOIN {$wpdb->prefix}handl_utm_sets us ON pv.utm_set_id = us.utm_set_id
            WHERE pv.page_url = %s
            GROUP BY us.$utm_param
            ORDER BY total_visits DESC
            LIMIT 10
        ", $page_url);
        $results[$utm_param] = $wpdb->get_results($query, ARRAY_A);
    }

    // Add referrer breakdown
    $referrer_query = $wpdb->prepare("
        SELECT referrer, COUNT(DISTINCT session_id) as unique_sessions, COUNT(*) as total_visits
        FROM {$wpdb->prefix}handl_page_views
        WHERE page_url = %s
        GROUP BY referrer
        ORDER BY total_visits DESC
        LIMIT 10
    ", $page_url);
    $results['referrer'] = $wpdb->get_results($referrer_query, ARRAY_A);

    wp_send_json($results);
}
add_action('wp_ajax_handl_utm_breakdown_report', 'handl_utm_breakdown_report');

function get_full_tracking_mode_content() {
    global $handl_fields_disabled;
    ?>
    <div class="wrap">
        <h2>Full Tracking Mode Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('handl-utm-grabber-full-tracking-group'); ?>
            <?php do_settings_sections('handl-utm-grabber-full-tracking-group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Enable Full Tracking Mode</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span>Full Tracking Mode</span>
                            </legend>
                            <label for="handl_full_tracking_mode">
                                <input name="handl_full_tracking_mode" id="handl_full_tracking_mode" type="checkbox" value="1" <?php checked('1', get_option('handl_full_tracking_mode')); ?> <?php echo $handl_fields_disabled; ?> />
                                Turn UTM Grabber into a full tracking plugin similar to GA4
                                <p class="description">
                                    <strong>Disclaimer:</strong> This will collect data on every page visit for each session. It will increase the database size.
                                </p>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function handl_session_details_report() {
    global $wpdb;
    $session_id = $_POST['session_id'];
    
    $utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'page_url'];
    $results = [];

    foreach ($utm_params as $param) {
        $query = $wpdb->prepare("
            SELECT $param, COUNT(*) as visits
            FROM {$wpdb->prefix}handl_page_views pv
            LEFT JOIN {$wpdb->prefix}handl_utm_sets us ON pv.utm_set_id = us.utm_set_id
            WHERE pv.session_id = %s
            GROUP BY $param
            ORDER BY visits DESC
            LIMIT 10
        ", $session_id);
        $results[$param] = $wpdb->get_results($query, ARRAY_A);
    }

    wp_send_json($results);
}
add_action('wp_ajax_handl_session_details_report', 'handl_session_details_report');

function handl_export_full_tracking_csv() {
    global $wpdb;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $page_url = $_POST['page_url'];
    $session_id = $_POST['session_id'];
    
    // Build the WHERE clause based on filters
    $where = [];
    if ($start_date) $where[] = $wpdb->prepare("pv.timestamp >= %s", $start_date);
    if ($end_date) $where[] = $wpdb->prepare("pv.timestamp <= %s", $end_date);
    if ($page_url) $where[] = $wpdb->prepare("pv.page_url LIKE %s", '%' . $wpdb->esc_like($page_url) . '%');
    if ($session_id) $where[] = $wpdb->prepare("pv.session_id = %s", $session_id);
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "
        SELECT pv.*, us.*
        FROM {$wpdb->prefix}handl_page_views pv
        LEFT JOIN {$wpdb->prefix}handl_utm_sets us ON pv.utm_set_id = us.utm_set_id
        $where_clause
        ORDER BY pv.timestamp DESC
    ";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    // Output CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=full_tracking_export.csv');
    
    $output = fopen('php://output', 'w');
    
    // Output header row
    if (!empty($results)) {
        fputcsv($output, array_keys($results[0]));
    }
    
    // Output data rows
    foreach ($results as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
add_action('wp_ajax_handl_export_full_tracking_csv', 'handl_export_full_tracking_csv');
add_action('wp_ajax_nopriv_handl_export_full_tracking_csv', 'handl_export_full_tracking_csv');

function handl_remove_tracking_tables() {
    global $wpdb;
    $sessions_table = $wpdb->prefix . 'handl_sessions';
    $utm_sets_table = $wpdb->prefix . 'handl_utm_sets';
    $page_views_table = $wpdb->prefix . 'handl_page_views';

    $tables = [$sessions_table, $utm_sets_table, $page_views_table];
    $success = true;
    $wpdb->show_errors();

    foreach ($tables as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
            $result = $wpdb->query("DROP TABLE IF EXISTS $table");
            if ($result === false) {
                $success = false;
                break;
            }
        }
    }

    if ($success) {
        delete_option('handl_full_tracking_tables_created');
        delete_option('handl_full_tracking_mode');
        wp_send_json_success('Tables removed successfully');
    } else {
        wp_send_json_error('Error removing tables: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_handl_remove_tracking_tables', 'handl_remove_tracking_tables');