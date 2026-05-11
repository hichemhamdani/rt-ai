<?php

namespace handl\Premiums;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class DefineYourOwn {
    
    public function __construct() {
        // Add tab to admin
        add_filter('filter_admin_tabs', array($this, 'addToTabs'), 9999, 1);
        add_filter('get_admin_tab_content_dyo_logic', array($this, 'renderReactApp'), 10);
        
        // Frontend logic
        add_action('handl_utm_grabber_enqueue_action', array($this, 'enqueueFrontendLogic'));
        add_action('after_handl_capture_utms', array($this, 'processDyoLogic'), 10, 0);
        add_action('wp_footer', array($this, 'addFooterScripts'));
        
        // AJAX endpoints
        add_action('wp_ajax_handl_get_dyo_logic', array($this, 'ajaxGetDyoLogic'));
        add_action('wp_ajax_handl_update_dyo_logic', array($this, 'ajaxUpdateDyoLogic'));
        add_action('wp_ajax_handl_add_custom_parameter', array($this, 'ajaxAddCustomParameter'));
    }
    
    /**
     * Add DYO tab to admin tabs
     */
    public function addToTabs($tabs) {
        array_push($tabs, array('dyo_logic' => __('Define Your Own', 'handlutmgrabber')));
        return $tabs;
    }
    
    /**
     * Render the React app container
     */
    public function renderReactApp() {
        ?>
        <div id='handl-react-root'>
            <div id="handl-dyo-logic">
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue DYO logic for frontend
     */
    public function enqueueFrontendLogic() {
        wp_localize_script('handl-utm-grabber', 'handl_utm_dyo_logic', $this->getDyoLogic());
    }
    
    /**
     * Get DYO logic with migrations applied
     */
    public function getDyoLogic() {
        $logic = get_option('dyo_logic');
        if (empty($logic)) {
            return array();
        }
        
        // Migrate all items to latest schema
        $migratedLogic = array();
        foreach ($logic as $id => $item) {
            $migratedLogic[$id] = $this->migrateToLatestSchema($item);
        }
        
        return $migratedLogic;
    }
    
    /**
     * Process DYO logic on the server side
     */
    public function processDyoLogic() {
        $dyoLogic = $this->getDyoLogic();
        $domain = getDomainName();
        
        foreach ($dyoLogic as $group) {
            if (!empty($group['conditions'])) {
                $logicType = $group['logic_type'] ?? 'or';
                $conditionsResult = $this->evaluateConditions($group['conditions'], $logicType);
                
                if ($conditionsResult && !empty($group['then_actions'])) {
                    // Execute THEN actions
                    $this->executeActions($group['then_actions'], $domain);
                } elseif (!$conditionsResult && !empty($group['else_actions'])) {
                    // Execute ELSE actions  
                    $this->executeActions($group['else_actions'], $domain);
                }
            }
        }
    }
    
    /**
     * AJAX endpoint to get DYO logic
     */
    public function ajaxGetDyoLogic() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $dyoLogic = $this->getDyoLogic();
        $dyoLogicArray = array_values($dyoLogic);
        
        wp_send_json_success(array(
            'dyo_logic' => $dyoLogicArray,
            'utm_fields' => generateUTMFields()
        ));
    }
    
    /**
     * AJAX endpoint to update DYO logic
     */
    public function ajaxUpdateDyoLogic() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $dyoLogicData = isset($_POST['dyo_logic_data']) ? $_POST['dyo_logic_data'] : '';
        
        if (empty($dyoLogicData)) {
            wp_send_json_error('No data provided.');
            return;
        }
        
        $decodedData = json_decode(stripslashes($dyoLogicData), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON data provided.');
            return;
        }
        
        // Validate data structure (should be array of DyoLogicGroup)
        if (!is_array($decodedData)) {
            wp_send_json_error('Data must be an array.');
            return;
        }
        
        $formattedData = array();
        foreach ($decodedData as $index => $group) {
            if ($this->validateDyoLogicGroup($group)) {
                $formattedData[$index] = $group;
            } else {
                wp_send_json_error('Invalid group structure at index ' . $index);
                return;
            }
        }
        
        // Get current option value to compare (Update option returns false if the option is the same)
        $currentData = get_option('dyo_logic', array());
        
        // Save to WordPress options
        $result = update_option('dyo_logic', $formattedData);
        
        if ($result !== false || $currentData === $formattedData) {
            $message = ($result !== false) ? 'DYO logic updated successfully.' : 'DYO logic is already up to date.';
            wp_send_json_success(array(
                'message' => $message,
                'groups_count' => count($formattedData),
                'updated' => $result !== false
            ));
        } else {
            wp_send_json_error('Failed to save DYO logic data.');
        }
    }
    
    /**
     * AJAX endpoint to add custom parameter
     */
    public function ajaxAddCustomParameter() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access. Admin privileges required.');
            return;
        }
        
        $paramName = isset($_POST['param_name']) ? $_POST['param_name'] : '';
        
        if (empty($paramName)) {
            wp_send_json_error('Parameter name is required.');
            return;
        }
        
        
        // Get current custom parameters
        $customParams = get_option('custom_params', array());
        
        if (in_array($paramName, $customParams)) {
            wp_send_json_success(array(
                'message' => "Parameter '{$paramName}' is already in Custom Fields.",
            ));
            return;
        }
        
        $customParams[] = $paramName;
        
        $result = update_option('custom_params', $customParams);
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => "Parameter '{$paramName}' added to Custom Fields successfully.",
                'param_name' => $paramName,
            ));
        } else {
            wp_send_json_error('Failed to add parameter to Custom Fields.');
        }
    }
    
    /**
     * Add footer scripts for frontend DYO logic
     */
    public function addFooterScripts() {
        ?>
        <script>
        // helper function for processing dynamic values
        function processDynamicValue(template) {
            if (!template) return '';
            
            // Check if template contains cookie references
            const cookieMatches = template.match(/\{\{([^}]+)\}\}/g);
            if (cookieMatches) {
                let processed = template;
                cookieMatches.forEach(function(match) {
                    const cookieName = match.replace(/\{\{|\}\}/g, '');
                    const cookieValue = Cookies.get(cookieName) || '';
                    processed = processed.replace(match, cookieValue);
                });
                return processed;
            }
            
            // Return as-is for static values (backward compatibility)
            return template;
        }

        document.addEventListener('HandL-After-Main-Function', () => {
            if (typeof handl_utm_dyo_logic === 'object') {
                // Convert to array if it's not already
                const logicArray = Array.isArray(handl_utm_dyo_logic) ? handl_utm_dyo_logic : [handl_utm_dyo_logic];
                
                logicArray.forEach(function(group) {
                    if (!group || !group.conditions || !Array.isArray(group.conditions)) return;

                    const logicType = group.logic_type || 'or';
                    const conditionsResult = evaluateConditionsJS(group.conditions, logicType);
                    
                    if (conditionsResult && group.then_actions && Array.isArray(group.then_actions)) {
                        // Execute THEN actions
                        executeActionsJS(group.then_actions);
                    } else if (!conditionsResult && group.else_actions && Array.isArray(group.else_actions)) {
                        // Execute ELSE actions  
                        executeActionsJS(group.else_actions);
                    }
                });
            }
        });

        function executeActionsJS(actions) {
            actions.forEach(function(action) {
                if (action.param && action.value) {
                    const processedValue = processDynamicValue(action.value);
                    SetRefLink(action.param, processedValue, true, 0);
                }
            });
        }

        function evaluateConditionsJS(conditions, logicType) {
            let allConditionsMet = true;
            let anyConditionMet = false;

            // Process each condition in the group
            conditions.forEach(function(condition) {
                if (!condition || !condition.utm_param || !condition.operator) return;

                const utmValue = Cookies.get(condition.utm_param) || '';
                const conditionResult = checkCondition(utmValue, condition.operator, condition.utm_value);

                if (logicType === 'and') {
                    if (!conditionResult) {
                        allConditionsMet = false;
                    }
                } else { // OR logic
                    if (conditionResult) {
                        anyConditionMet = true;
                    }
                }
            });

            return (logicType === 'and' && allConditionsMet) || (logicType === 'or' && anyConditionMet);
        }

        function checkCondition(value, operator, compare) {
            // Handle 'defined' and 'not_defined' operators that don't need a compare value
            if (operator === 'defined') {
                return value !== undefined && value !== '';
            }
            if (operator === 'not_defined') {
                return value === undefined || value === '';
            }

            // Skip other checks if we don't have a compare value
            if (!compare || compare === 'N/A') return false;

            switch (operator) {
                case 'equals':
                    return value === compare;
                case 'not_equals':
                    return value !== compare;
                case 'contains':
                    return value.indexOf(compare) !== -1;
                case 'not_contains':
                    return value.indexOf(compare) === -1;
                case 'starts_with':
                    return value.startsWith(compare);
                case 'ends_with':
                    return value.endsWith(compare);
                case 'regex':
                    try {
                        return new RegExp(compare).test(value);
                    } catch (e) {
                        console.error('Invalid regex pattern:', compare);
                        return false;
                    }
                default:
                    return false;
            }
        }
        </script>
        <?php
    }
    
    // Private helper methods
    
    /**
     * Migrate DYO logic to latest schema version
     */
    private function migrateToLatestSchema($item) {
        // Detect current schema version
        $version = $this->detectSchemaVersion($item);
        
        switch ($version) {
            case 1: // Legacy format (single condition, single action)
                return $this->migrateLegacyToV3($item);
            case 2: // Current format (multiple conditions, single action) 
                return $this->migrateV2ToV3($item);
            case 3: // Latest format (multiple conditions, multiple actions)
                return $item;
            default:
                return $item;
        }
    }
    
    /**
     * Detect schema version of a DYO logic item
     */
    private function detectSchemaVersion($item) {
        if (isset($item['schema_version'])) {
            return $item['schema_version'];
        }
        if (isset($item['then_actions']) || isset($item['else_actions'])) {
            return 3; // Multi-action format
        }
        if (isset($item['conditions'])) {
            return 2; // Current format
        }
        return 1; // Legacy format
    }
    
    /**
     * Migrate from legacy v1 to v3 schema
     */
    private function migrateLegacyToV3($item) {
        return array(
            'schema_version' => 3,
            'logic_type' => $item['logic_type'] ?? 'or',
            'conditions' => array(
                array(
                    'utm_param' => $item['utm_param'] ?? '',
                    'operator' => $item['operator'] ?? '',
                    'utm_value' => $item['utm_value'] ?? ''
                )
            ),
            'then_actions' => array(
                array(
                    'param' => $item['new_param'] ?? '',
                    'value' => $item['new_value'] ?? ''
                )
            ),
            'else_actions' => array()
        );
    }
    
    /**
     * Migrate from v2 to v3 schema
     */
    private function migrateV2ToV3($item) {
        $then_actions = array();
        if (!empty($item['new_param'])) {
            $then_actions[] = array(
                'param' => $item['new_param'],
                'value' => $item['new_value'] ?? ''
            );
        }
        
        $else_actions = array();
        if (!empty($item['else_param'])) {
            $else_actions[] = array(
                'param' => $item['else_param'],
                'value' => $item['else_value'] ?? ''
            );
        }
        
        return array(
            'schema_version' => 3,
            'logic_type' => $item['logic_type'] ?? 'or',
            'conditions' => $item['conditions'] ?? array(),
            'then_actions' => $then_actions,
            'else_actions' => $else_actions
        );
    }
    
    /**
     * Execute actions (set cookies)
     */
    private function executeActions($actions, $domain) {
        if (HandLCookieConsented()) {
            foreach ($actions as $action) {
                if (!empty($action['param']) && !empty($action['value'])) {
                    $processedValue = $this->processDynamicValue($action['value']);
                    HandLCreateParameters($action['param'], $processedValue, $domain);
                }
            }
        }
    }
    
    /**
     * Evaluate conditions based on logic type
     */
    private function evaluateConditions($conditions, $logicType) {
        $allConditionsMet = true;
        $anyConditionMet = false;
        
        foreach ($conditions as $condition) {
            if (!empty($condition['utm_param']) && !empty($condition['operator'])) {
                $utmValue = isset($_COOKIE[$condition['utm_param']]) ? $_COOKIE[$condition['utm_param']] : '';
                $conditionResult = $this->checkCondition($utmValue, $condition['operator'], $condition['utm_value'] ?? '');
                
                if ($logicType === 'and') {
                    if (!$conditionResult) {
                        $allConditionsMet = false;
                        break; // Exit early if any condition fails for AND
                    }
                } else { // OR logic
                    if ($conditionResult) {
                        $anyConditionMet = true;
                        break; // Exit early if any condition is true for OR
                    }
                }
            } else {
                // If any required field is empty in AND logic, the whole group fails
                if ($logicType === 'and') {
                    $allConditionsMet = false;
                    break;
                }
            }
        }
        
        return ($logicType === 'and' && $allConditionsMet) || ($logicType === 'or' && $anyConditionMet);
    }
    
    /**
     * Check individual condition
     */
    private function checkCondition($value, $operator, $compare) {
        switch ($operator) {
            case 'equals':
                return $value === $compare;
            case 'not_equals':
                return $value !== $compare;
            case 'contains':
                return strpos($value, $compare) !== false;
            case 'not_contains':
                return strpos($value, $compare) === false;
            case 'starts_with':
                return strpos($value, $compare) === 0;
            case 'ends_with':
                return substr($value, -strlen($compare)) === $compare;
            case 'regex':
                return preg_match($compare, $value);
            case 'defined':
                return isset($_COOKIE[$value]) && $_COOKIE[$value] !== '';
            case 'not_defined':
                return !isset($_COOKIE[$value]) || $_COOKIE[$value] === '';
            default:
                return false;
        }
    }
    
    /**
     * Process dynamic values in templates
     */
    private function processDynamicValue($template, $fallback = '') {
        if (empty($template)) return $fallback;
        
        // Check if template contains cookie references
        if (preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches)) {
            $processed = $template;
            foreach ($matches[1] as $cookieName) {
                $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : '';
                $processed = str_replace('{{' . $cookieName . '}}', $cookieValue, $processed);
            }
            return $processed;
        }
        
        // Return as-is for static values (backward compatibility)
        return $template;
    }
    
    /**
     * Validate DYO logic group structure
     */
    private function validateDyoLogicGroup($group) {
        if (!is_array($group)) {
            return false;
        }
        
        // Required fields
        $requiredFields = array('logic_type', 'conditions', 'then_actions', 'else_actions');
        foreach ($requiredFields as $field) {
            if (!isset($group[$field])) {
                return false;
            }
        }
        
        // Validate logic_type
        if (!in_array($group['logic_type'], array('or', 'and'))) {
            return false;
        }
        
        // Validate conditions
        if (!is_array($group['conditions'])) {
            return false;
        }
        
        foreach ($group['conditions'] as $condition) {
            if (!is_array($condition) || 
                !isset($condition['utm_param']) || 
                !isset($condition['operator']) || 
                !isset($condition['utm_value'])) {
                return false;
            }
            
            // Validate operator
            $validOperators = array('equals', 'not_equals', 'contains', 'not_contains', 
                                   'starts_with', 'ends_with', 'regex', 'defined', 'not_defined');
            if (!in_array($condition['operator'], $validOperators)) {
                return false;
            }
        }
        
        // Validate actions
        if (!is_array($group['then_actions']) || !is_array($group['else_actions'])) {
            return false;
        }
        
        // Validate action structure
        $allActions = array_merge($group['then_actions'], $group['else_actions']);
        foreach ($allActions as $action) {
            if (!is_array($action) || 
                !isset($action['param']) || 
                !isset($action['value'])) {
                return false;
            }
        }
        
        return true;
    }
}

new DefineYourOwn();
