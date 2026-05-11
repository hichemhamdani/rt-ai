<?php

// Register settings and add admin menu tab
function handl_phone_tracking_init() {
    register_setting('handl-utm-grabber-phone-tracking-group', 'handl_phone_tracking_enabled');
    
    // Create the database table on plugin activation
    global $wpdb;
    $table_name = $wpdb->prefix . 'handl_phone_tracking';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        phone_number varchar(50) NOT NULL,
        tracking_data longtext NOT NULL,
        clicked_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('admin_init', 'handl_phone_tracking_init');

// Add to admin tabs
function add_phone_tracking_to_tabs($tabs) {
    array_push($tabs, array('phone-tracking' => __('Phone Tracking', 'handlutmgrabber')));
    return $tabs;
}
add_filter('filter_admin_tabs', 'add_phone_tracking_to_tabs', 10, 1);

// Admin page content
function getPhoneTrackingContent() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Sorry, you are not allowed to access this page.'));
    }

    global $handl_active;
    ?>
    <div class="wrap">
        <h2>Phone Number Click Tracking</h2>
        <p class="description">
            Track all phone number clicks (tel: links) on your website along with UTM parameters and other tracking data. 
            This helps you understand which marketing campaigns are driving phone calls.
        </p>

        <form method='post' action='options.php'>
            <?php settings_fields('handl-utm-grabber-phone-tracking-group'); ?>
            <?php do_settings_sections('handl-utm-grabber-phone-tracking-group'); ?>
            <?php do_action('maybe_dispay_license_error_notice') ?>
            
            <table class='form-table'>
                <tr>
                    <th scope='row'>Enable Phone Tracking</th>
                    <td>
                        <fieldset>
                            <legend class='screen-reader-text'>
                                <span>Enable Phone Tracking</span>
                            </legend>
                            <label for='handl_phone_tracking_enabled'>
                                <input name='handl_phone_tracking_enabled' 
                                       id='handl_phone_tracking_enabled' 
                                       type='checkbox' 
                                       value='1' 
                                       <?php checked(get_option('handl_phone_tracking_enabled'), '1'); ?>
                                       <?php echo $handl_active ? '' : 'disabled'; ?>
                                />
                                Enable phone number click tracking
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php submit_button(null, 'primary', 'submit', true, $handl_active ? '' : 'disabled'); ?>
        </form>

        <?php if (get_option('handl_phone_tracking_enabled') === '1'): ?>
            <div class="phone-tracking-report">
                <h2>Phone Tracking Report</h2>
                
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <form method="post" action="">
                            <?php wp_nonce_field('handl_phone_tracking_action', 'handl_phone_tracking_nonce'); ?>
                            <input type="submit" name="export_phone_tracking" class="button" value="Export to CSV">
                            <input type="submit" name="clear_phone_tracking" class="button" value="Clear All Data" 
                                   onclick="return confirm('Are you sure you want to delete all phone tracking data?');">
                        </form>
                    </div>
                    <div class="alignright actions">
                        <form method="get" class="search-form">
                            <input type="hidden" name="page" value="handl-utm-grabber.php">
                            <input type="hidden" name="tab" value="phone-tracking">
                            
                            <input type="text" 
                                   name="phone_search" 
                                   value="<?php echo esc_attr($_GET['phone_search'] ?? ''); ?>" 
                                   placeholder="Search phone number..."
                                   style="margin-right: 5px;">
                            
                            <input type="date" 
                                   name="date_from" 
                                   value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>"
                                   placeholder="From date"
                                   style="margin-right: 5px;">
                            
                            <input type="date" 
                                   name="date_to" 
                                   value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>"
                                   placeholder="To date"
                                   style="margin-right: 5px;">
                            
                            <input type="submit" class="button" value="Search">
                            <?php if(isset($_GET['phone_search']) || isset($_GET['date_from']) || isset($_GET['date_to'])): ?>
                                <a href="<?php echo admin_url('admin.php?page=handl-utm-grabber.php&tab=phone-tracking'); ?>"
                                   class="button">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="tablenav-pages">
                        <?php
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'handl_phone_tracking';
                        $items_per_page = 20;
                        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                        $total_pages = ceil($total_items / $items_per_page);
                        
                        echo paginate_links(array(
                            'base' => add_query_arg(array(
                                'page' => 'handl-utm-grabber.php',
                                'tab' => 'phone-tracking',
                                'paged' => '%#%',
                                'phone_search' => $_GET['phone_search'] ?? '',
                                'date_from' => $_GET['date_from'] ?? '',
                                'date_to' => $_GET['date_to'] ?? ''
                            ), admin_url('admin.php')),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $current_page
                        ));
                        ?>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Phone Number</th>
                            <th>UTM Source</th>
                            <th>UTM Medium</th>
                            <th>UTM Campaign</th>
                            <th>Original Referrer</th>
                            <th>Landing Page</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $offset = ($current_page - 1) * $items_per_page;
                        
                        // Build WHERE clause for search
                        $where_clauses = array();
                        $where_values = array();
                        
                        if (!empty($_GET['phone_search'])) {
                            $where_clauses[] = "phone_number LIKE %s";
                            $where_values[] = '%' . $wpdb->esc_like($_GET['phone_search']) . '%';
                        }
                        
                        if (!empty($_GET['date_from'])) {
                            $where_clauses[] = "clicked_at >= %s";
                            $where_values[] = $_GET['date_from'] . ' 00:00:00';
                        }
                        
                        if (!empty($_GET['date_to'])) {
                            $where_clauses[] = "clicked_at <= %s";
                            $where_values[] = $_GET['date_to'] . ' 23:59:59';
                        }
                        
                        // Construct the query
                        $query = "SELECT * FROM $table_name";
                        if (!empty($where_clauses)) {
                            $query .= " WHERE " . implode(" AND ", $where_clauses);
                        }
                        $query .= " ORDER BY clicked_at DESC";
                        
                        // Get total count for pagination
                        $count_query = "SELECT COUNT(*) FROM $table_name";
                        if (!empty($where_clauses)) {
                            $count_query .= " WHERE " . implode(" AND ", $where_clauses);
                        }
                        
                        // Add LIMIT and OFFSET
                        $query .= " LIMIT %d OFFSET %d";
                        $where_values[] = $items_per_page;
                        $where_values[] = $offset;
                        
                        // Execute queries
                        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
                        $total_pages = ceil($total_items / $items_per_page);
                        
                        $results = $wpdb->get_results($wpdb->prepare($query, $where_values));

                        foreach ($results as $row) {
                            $tracking_data = json_decode(wp_unslash($row->tracking_data), true);
                            $bg_color = get_phone_color($row->phone_number);
                            ?>
                            <tr>
                                <td><?php echo esc_html($row->clicked_at); ?></td>
                                <td>
                                    <span style="background-color: <?php echo esc_attr($bg_color); ?>; 
                                                padding: 6px 12px;
                                                border-radius: 20px;
                                                display: inline-block;
                                                font-weight: 500;
                                                line-height: 1.4;
                                                white-space: nowrap;">
                                        <?php echo esc_html(format_phone_number($row->phone_number)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($tracking_data['utm_source'] ?? ''); ?></td>
                                <td><?php echo esc_html($tracking_data['utm_medium'] ?? ''); ?></td>
                                <td><?php echo esc_html($tracking_data['utm_campaign'] ?? ''); ?></td>
                                <td><?php echo esc_html($tracking_data['handl_original_ref'] ?? ''); ?></td>
                                <td><?php echo esc_html($tracking_data['handl_landing_page'] ?? ''); ?></td>
                                <td>
                                    <button class="button" onclick="showTrackingDetails(<?php echo esc_js(json_encode($tracking_data)); ?>)">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="tracking-details-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Tracking Details</h3>
                    <pre id="tracking-details-content"></pre>
                </div>
            </div>

            <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 1200px;
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .search-form {
                margin: 10px 0;
                display: flex;
                align-items: center;
            }
            .search-form input[type="text"],
            .search-form input[type="date"] {
                height: 28px;
                vertical-align: middle;
            }
            .tablenav {
                height: auto !important;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            .wp-list-table td {
                vertical-align: middle;
                padding: 12px 10px;
            }
            
            /* Update hover effect for the pill */
            .wp-list-table td span {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            
            .wp-list-table td span:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            </style>

            <script>
            function showTrackingDetails(data) {
                const modal = document.getElementById('tracking-details-modal');
                const content = document.getElementById('tracking-details-content');
                content.textContent = JSON.stringify(data, null, 2);
                modal.style.display = "block";
            }

            document.querySelector('.close').onclick = function() {
                document.getElementById('tracking-details-modal').style.display = "none";
            }

            window.onclick = function(event) {
                const modal = document.getElementById('tracking-details-modal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            </script>
        <?php endif; ?>
    </div>
    <?php
}
add_filter('get_admin_tab_content_phone-tracking', 'getPhoneTrackingContent', 10);

// Handle export and clear actions
function handl_phone_tracking_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_POST['handl_phone_tracking_nonce']) || 
        !wp_verify_nonce($_POST['handl_phone_tracking_nonce'], 'handl_phone_tracking_action')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'handl_phone_tracking';

    if (isset($_POST['export_phone_tracking'])) {
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY clicked_at DESC", ARRAY_A);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="phone-tracking-export.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Get UTM fields
        $utm_fields = generateUTMFields();
        
        // Create headers array with base columns
        $headers = array('Timestamp', 'Phone Number');
        
        // Add UTM fields to headers
        foreach ($utm_fields as $field) {
            $headers[] = ucwords(str_replace('_', ' ', $field));
        }
        
        // Write headers to CSV
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($results as $row) {
            $tracking_data = json_decode(wp_unslash($row['tracking_data']), true);
            
            // Start with base data
            $csv_row = array(
                $row['clicked_at'],
                format_phone_number($row['phone_number']),
            );
            
            // Add UTM data
            foreach ($utm_fields as $field) {
                $csv_row[] = isset($tracking_data[$field]) ? $tracking_data[$field] : '';
            }
            
            fputcsv($output, $csv_row);
        }
        
        fclose($output);
        exit();
    }

    if (isset($_POST['clear_phone_tracking'])) {
        $wpdb->query("TRUNCATE TABLE $table_name");
        wp_redirect(add_query_arg('cleared', '1'));
        exit();
    }
}
add_action('admin_init', 'handl_phone_tracking_actions');

// Add JavaScript to track phone clicks
function handl_phone_tracking_script() {
    if (get_option('handl_phone_tracking_enabled') !== '1') {
        return;
    }
    ?>
    <script>
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href^="tel:"]');
        if (!link) return;

        const phoneNumber = link.href.replace('tel:', '');
        const trackingData = getAllHandLUTMParams();

        // Send tracking data to server
        fetch(handl_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'handl_track_phone_click',
                phone_number: phoneNumber,
                tracking_data: JSON.stringify(trackingData),
                nonce: '<?php echo wp_create_nonce("handl_phone_tracking"); ?>'
            })
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'handl_phone_tracking_script');

// Handle AJAX request to save phone click data
function handl_track_phone_click() {
    check_ajax_referer('handl_phone_tracking', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'handl_phone_tracking';

    $wpdb->insert(
        $table_name,
        array(
            'phone_number' => sanitize_text_field(preg_replace('/[^0-9]/', '', $_POST['phone_number'])),
            'tracking_data' => wp_unslash($_POST['tracking_data']),
            'clicked_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s')
    );

    wp_send_json_success();
}
add_action('wp_ajax_handl_track_phone_click', 'handl_track_phone_click');
add_action('wp_ajax_nopriv_handl_track_phone_click', 'handl_track_phone_click');

// Add this function at the top of the file after the initial PHP tag
function get_phone_color($phone_number) {
    // Generate a consistent color based on the phone number
    $hash = crc32($phone_number);
    $hue = $hash % 360; // Get a hue value between 0-359
    return "hsl({$hue}, 70%, 85%)"; // Use HSL with fixed saturation and lightness
}

// Add this function near the other utility functions
function format_phone_number($phone) {
    // Remove everything except digits
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format based on length
    $length = strlen($phone);
    
    if ($length == 10) {
        // Format as: (555) 123-4567
        return sprintf("(%s) %s-%s",
            substr($phone, 0, 3),
            substr($phone, 3, 3),
            substr($phone, 6)
        );
    } else if ($length == 11 && $phone[0] == '1') {
        // Format as: +1 (555) 123-4567
        return sprintf("+1 (%s) %s-%s",
            substr($phone, 1, 3),
            substr($phone, 4, 3),
            substr($phone, 7)
        );
    } else {
        // If it doesn't match standard formats, just group by 3s
        return implode('-', str_split($phone, 3));
    }
} 