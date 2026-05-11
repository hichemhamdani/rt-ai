<?php

// Contact Form 7
if (!function_exists('hug_wpcf7_before_send_mail')) {
    function hug_wpcf7_before_send_mail($form) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $submission = WPCF7_Submission::get_instance();
        $data = $submission->get_posted_data();
        $data['form_id'] = $form->id();

        if ($webhook_set) {
            $data = populateUTMFields($data);
            do_action('handl_post_data_to', $data);
        }

        if ($fb_capi_enabled) {
            $user_data = array(
                'em' => isset($data['your-email']) ? [$data['your-email']] : [],
                'ph' => isset($data['your-phone']) ? [$data['your-phone']] : [],
                'fn' => '',
                'ln' => ''
            );

            if (isset($data['your-name'])) {
                $name_parts = explode(' ', $data['your-name'], 2);
                $user_data['fn'] = $name_parts[0];
                $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
            }

            $user_data = populate_additional_fields($data, $user_data);
            handl_send_fb_capi_lead_event($form->id(), $form->title(), $user_data);
        }
    }
}
add_action('wpcf7_mail_sent', 'hug_wpcf7_before_send_mail');

// Ninja Forms
if (!function_exists('hug_ninja_forms_after_submission')) {
    function hug_ninja_forms_after_submission($form_data) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $data = array();
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);

        foreach ($form_data['fields_by_key'] as $field) {
            if (isset($field['key'])) {
                $data[$field['key']] = $field['value'];
            }

            if ($fb_capi_enabled) {
                switch ($field['type']) {
                    case 'textbox':
						if ($field['key'] === 'name') {
							$name_parts = explode(' ', $field['value'], 2);
							$user_data['fn'] = $name_parts[0];
							$user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
						}
                        break;
                    case 'email':
                        $user_data['em'] = [$field['value']];
                        break;
                    case 'phone':
                        $user_data['ph'] = [$field['value']];
                        break;
                }
            }
        }

        if ($webhook_set) {
            $data = populateUTMFields($data);
	        $data['ninja_form_id'] = $form_data['form_id'];
            do_action('handl_post_data_to', $data);
        }

        if ($fb_capi_enabled) {
            $user_data = populate_additional_fields($data, $user_data);
            handl_send_fb_capi_lead_event($form_data['form_id'], $form_data['settings']['title'], $user_data);
        }
    }
}
add_action('ninja_forms_after_submission', 'hug_ninja_forms_after_submission');

// Gravity Forms
if (!function_exists('hug_gform_after_submission')) {
    function hug_gform_after_submission($entry, $form) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $data = array();
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);

        foreach ($form['fields'] as $field) {
            $inputs = $field->get_entry_inputs();
            if (is_array($inputs)) {
                foreach ($inputs as $input) {
                    $value = rgar($entry, (string) $input['id']);
                    $label = isset($input['adminLabel']) && $input['adminLabel'] != '' ? $input['adminLabel'] : 'input_' . $input['id'];
                    $data[$label] = $value;
                }
            } else {
                $value = rgar($entry, (string) $field->id);
                $label = isset($field->adminLabel) && $field->adminLabel != '' ? $field->adminLabel : 'input_' . $field->id;
                $data[$label] = $value;
            }

            if ($fb_capi_enabled) {
                switch ($field->type) {
                    case 'name':
                        if ($field->inputs) {
                            foreach ($field->inputs as $input) {
                                if (strpos($input['label'], 'First') !== false) {
                                    $user_data['fn'] = rgar($entry, $input['id']);
                                } elseif (strpos($input['label'], 'Last') !== false) {
                                    $user_data['ln'] = rgar($entry, $input['id']);
                                }
                            }
                        } else {
                            $full_name = rgar($entry, $field->id);
                            $name_parts = explode(' ', $full_name, 2);
                            $user_data['fn'] = $name_parts[0];
                            $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                        }
                        break;
                    case 'email':
                        $user_data['em'] = [rgar($entry, $field->id)];
                        break;
                    case 'phone':
                        $user_data['ph'] = [rgar($entry, $field->id)];
                        break;
                }
            }
        }

        if ($webhook_set) {
            $data["form_id"] = $form['id'];
            $data["form"] = 'gravity_form';
            $data = populateUTMFields($data);
            do_action('handl_post_data_to', $data);
        }

        if ($fb_capi_enabled) {
            $user_data = populate_additional_fields($data, $user_data);
            handl_send_fb_capi_lead_event($form['id'], $form['title'], $user_data);
        }
    }
}
add_action('gform_after_submission', 'hug_gform_after_submission', 10, 2);

// Formidable Forms
if (!function_exists('hug_frm_process_entry')) {
    function hug_frm_process_entry($params, $errors, $form, $other) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $fields = FrmFieldsHelper::get_form_fields($form->id, $errors);
        $data = array();
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);

        foreach ($fields as $field) {
            $value = $_POST['item_meta'][$field->id];
            $data[$field->field_key] = $value;

            if ($fb_capi_enabled) {
                switch ($field->type) {
                    case 'text':
                        if (stripos($field->description, 'first') !== false) {
                            $user_data['fn'] = $value;
                        } elseif (stripos($field->description, 'last') !== false) {
                            $user_data['ln'] = $value;
                        }
                        break;
                    case 'email':
                        $user_data['em'] = [$value];
                        break;
                    case 'phone':
                        $user_data['ph'] = [$value];
                        break;
                }
            }
        }

        if ($webhook_set) {
            $data = populateUTMFields($data);
            do_action('handl_post_data_to', $data);
        }

        if ($fb_capi_enabled) {
            $user_data = populate_additional_fields($data, $user_data);
            handl_send_fb_capi_lead_event($form->id, $form->name, $user_data);
        }
    }
}
add_action('frm_process_entry', 'hug_frm_process_entry', 10, 4);

// Thrive Architect Forms
if (!function_exists('handl_tcb_api_form_submit')) {
    function handl_tcb_api_form_submit($post) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $data = $post;

        if ($webhook_set) {
            $data = populateUTMFields($data);
            do_action('handl_post_data_to', $data);
        }

        if ($fb_capi_enabled) {
            $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);

            foreach ($data as $field_name => $field_value) {
                if (stripos($field_name, 'name') !== false) {
                    $name_parts = explode(' ', $field_value, 2);
                    $user_data['fn'] = $name_parts[0];
                    $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                } elseif (stripos($field_name, 'email') !== false) {
                    $user_data['em'] = [$field_value];
                } elseif (stripos($field_name, 'phone') !== false) {
                    $user_data['ph'] = [$field_value];
                }
            }

            $user_data = populate_additional_fields($data, $user_data);
            $form_id = isset($data['form_id']) ? $data['form_id'] : 'unknown';
            $form_name = isset($data['form_name']) ? $data['form_name'] : 'Thrive Architect Form';
            handl_send_fb_capi_lead_event($form_id, $form_name, $user_data);
        }
    }
}
add_action('tcb_api_form_submit', 'handl_tcb_api_form_submit', 10, 1);

// Fluent Forms
function handl_fluentform_submission_inserted($insertId, $formData, $form) {
    $webhook_set = apply_filters('handl_webhook_url_set', false);
    $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

    if (!$webhook_set && !$fb_capi_enabled) {
        return;
    }

    $data = $formData;
    $data['form_id'] = $insertId;

    if ($webhook_set) {
        $data = populateUTMFields($data);
        do_action('handl_post_data_to', $data);
    }

    if ($fb_capi_enabled) {
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);

        foreach ($formData as $field_name => $field_value) {
            if (strpos($field_name, 'name') !== false) {
                // Handle array (separate first/last name fields) or string (full name)
                if (is_array($field_value)) {
                    $user_data['fn'] = isset($field_value['first_name']) ? $field_value['first_name'] : '';
                    $user_data['ln'] = isset($field_value['last_name']) ? $field_value['last_name'] : '';
                } else {
                    $name_parts = explode(' ', $field_value, 2);
                    $user_data['fn'] = $name_parts[0];
                    $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                }
            } elseif (strpos($field_name, 'email') !== false) {
                $user_data['em'] = [$field_value];
            } elseif (strpos($field_name, 'phone') !== false) {
                $user_data['ph'] = [$field_value];
            }
        }

        $user_data = populate_additional_fields($data, $user_data);
        handl_send_fb_capi_lead_event($insertId, $form->title, $user_data);
    }
}
add_action('fluentform_submission_inserted', 'handl_fluentform_submission_inserted', 10, 3);

// Elementor Forms
if (!function_exists('handl_elementor_form_submission')) {
    function handl_elementor_form_submission($record, $ajax_handler) {
        $webhook_set = apply_filters('handl_webhook_url_set', false);
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';

        if (!$webhook_set && !$fb_capi_enabled) {
            return;
        }

        $form_name = $record->get_form_settings('form_name');
        $form_id = $record->get_form_settings('id');
        $raw_fields = $record->get('fields');
        
        $data = array();
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);
        
        foreach ($raw_fields as $id => $field) {
            $data[$id] = $field['value'];
            
            if ($fb_capi_enabled) {
                switch ($field['type']) {
                    case 'text':
                        if (stripos($id, 'name') !== false) {
                            $name_parts = explode(' ', $field['value'], 2);
                            $user_data['fn'] = $name_parts[0];
                            $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                        }
                        break;
                    case 'email':
                        $user_data['em'] = [$field['value']];
                        break;
                    case 'tel':
                        $user_data['ph'] = [$field['value']];
                        break;
                }
            }
        }
        
        if ($webhook_set) {
            $data = populateUTMFields($data);
            do_action('handl_post_data_to', $data);
        }
        
        if ($fb_capi_enabled) {
            $user_data = populate_additional_fields($data, $user_data);
            handl_send_fb_capi_lead_event($form_id, $form_name, $user_data);
        }
    }
}
add_action('elementor_pro/forms/new_record', 'handl_elementor_form_submission', 10, 2);

if (!function_exists('handl_wpforms_fb_capi')) {
    function handl_wpforms_fb_capi($fields, $entry, $form_data, $entry_id) {
        $fb_capi_enabled = get_option('handl_fb_capi_enabled') === '1';
        
        if (!$fb_capi_enabled) {
            return;
        }
        
        $data = array();
        $user_data = array('fn' => '', 'ln' => '', 'em' => [], 'ph' => []);
        
        foreach ($fields as $field) {
            $data[$field['name']] = $field['value'];
            
            switch ($field['type']) {
                case 'name':
                    // WPForms name field can be simple or multi-part
                    if (isset($field['first']) && isset($field['last'])) {
                        // Multi-part name field
                        $user_data['fn'] = $field['first'];
                        $user_data['ln'] = $field['last'];
                    } else {
                        $name_parts = explode(' ', $field['value'], 2);
                        $user_data['fn'] = $name_parts[0];
                        $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                    }
                    break;
                case 'email':
                    $user_data['em'] = [$field['value']];
                    break;
                case 'phone':
                    $user_data['ph'] = [$field['value']];
                    break;
                case 'text':
                case 'textarea':
                    // Check field labels for name fields that might be text type
                    $field_label = strtolower($field['name']);
                    if (strpos($field_label, 'first') !== false && strpos($field_label, 'name') !== false) {
                        $user_data['fn'] = $field['value'];
                    } elseif (strpos($field_label, 'last') !== false && strpos($field_label, 'name') !== false) {
                        $user_data['ln'] = $field['value'];
                    } elseif (strpos($field_label, 'name') !== false && empty($user_data['fn'])) {
                        // Generic name field
                        $name_parts = explode(' ', $field['value'], 2);
                        $user_data['fn'] = $name_parts[0];
                        $user_data['ln'] = isset($name_parts[1]) ? $name_parts[1] : '';
                    }
                    break;
            }
        }
        
        $user_data = populate_additional_fields($data, $user_data);
        
        // Get form name and ID
        $form_id = isset($form_data['id']) ? $form_data['id'] : 'unknown';
        $form_name = isset($form_data['settings']['form_title']) ? $form_data['settings']['form_title'] : 'WPForms Form';
        
        handl_send_fb_capi_lead_event($form_id, $form_name, $user_data);
    }
}
add_action('wpforms_process_complete', 'handl_wpforms_fb_capi', 10, 4);

if (!function_exists('populateUTMFields')) {
    function populateUTMFields( $post ) {
        foreach ( generateUTMFields() as $field ) {
            if ( isset( $_COOKIE[ $field ] ) && $_COOKIE[ $field ] != '' ) {
                $post[ 'handl_' . $field ] = $_COOKIE[ $field ];
            }
        }
        return $post;
    }
}

function handl_send_fb_capi_lead_event($form_id, $form_name, $user_data) {
    if (get_option('handl_fb_capi_enabled') === '1') {
        $fb_handl = new HandLFacebookAds();
        
        $lead_data = array(
            'event_name' => 'Lead',
            'user_data' => $user_data,
            'custom_data' => array(
                'form_id' => $form_id,
                'form_name' => $form_name,
            ),
        );

        $result = $fb_handl->sendFBConversion($lead_data);

        if (WP_DEBUG && !$result['success']) {
            error_log('HandL UTM Grabber: Failed to send Lead event to Facebook CAPI. Error: ' . $result['error']);
        }

        return $result;
    }
    return null;
}

function populate_additional_fields($data, $user_data) {
    $additional_fields = array(
        'fbc' => 'fbc',
        'fbp' => 'fbp',
        'client_ip_address' => 'handl_ip',
        'client_user_agent' => 'user_agent'
    );

    foreach ($additional_fields as $fb_field => $form_field) {
        if (isset($data[$form_field])) {
            $user_data[$fb_field] = $data[$form_field];
        }
    }

    return $user_data;
}
