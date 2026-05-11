<?php

namespace Handl;

if (!defined('ABSPATH')) {
    exit;
}

class Feature_Flags
{
    const API_URL = 'https://api.utmgrabber.com/http/license-features';
    const TRANSIENT_TTL = HOUR_IN_SECONDS;

    private static $instance = null;
    private $features = null;
    private $plan = null;
    private $loaded = false;

    private function __construct() {}

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return bool Whether the given feature is enabled for this license.
     *              Returns false if the license is inactive.
     *              Fails open (returns true) if the API is unreachable.
     */
    public function is_enabled(string $feature): bool
    {
        global $handl_active;

        if (empty($handl_active)) {
            return false;
        }

        $this->ensure_loaded();

        if ($this->features === null) {
            return true;
        }

        $flag = $this->features[$feature] ?? null;

        if ($flag === null) {
            return false;
        }

        if (!empty($flag['expires_at'])) {
            $expiry = strtotime($flag['expires_at']);
            if ($expiry !== false && $expiry < time()) {
                return false;
            }
        }

        return !empty($flag['enabled']);
    }

    public function get_plan(): ?string
    {
        global $handl_active;

        if (empty($handl_active)) {
            return null;
        }

        $this->ensure_loaded();
        return $this->plan;
    }

    /**
     * Returns the full feature flag object (enabled, expires_at) or null.
     */
    public function get_feature(string $feature): ?array
    {
        global $handl_active;

        if (empty($handl_active)) {
            return null;
        }

        $this->ensure_loaded();

        if ($this->features === null) {
            return null;
        }

        return $this->features[$feature] ?? null;
    }

    private function ensure_loaded(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $license_key = get_option('license_key_handl-utm-grabber-v3');
        if (empty($license_key)) {
            return;
        }

        $transient_key = 'utmgrabber_license_features_' . md5($license_key);
        $cached = get_transient('$transient_key');

        if ($cached !== false && is_array($cached)) {
            $this->plan = $cached['plan'] ?? null;
            $this->features = $cached['features'] ?? null;
            return;
        }

        $response = wp_remote_post(self::API_URL, array(
            'body'    => wp_json_encode(array('license_key' => $license_key)),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 10,
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            // Fail open: cache a sentinel so we don't retry on every request
            set_transient($transient_key, array('fail_open' => true), 5 * MINUTE_IN_SECONDS);
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($body) || !isset($body['features'])) {
            set_transient($transient_key, array('fail_open' => true), 5 * MINUTE_IN_SECONDS);
            return;
        }

        $this->plan = $body['plan'] ?? null;
        $this->features = $body['features'] ?? null;

        set_transient($transient_key, $body, self::TRANSIENT_TTL);
    }
}
