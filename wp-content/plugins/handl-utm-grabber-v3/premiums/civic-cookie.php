<?php

function civic_ok_to_go( $good2go ) {
    $third_party_enabled = get_option('require_third_party_consent', false);
    if ( $third_party_enabled && $good2go['good2go'] === 1 ) {
        if ( isset( $_COOKIE["CookieControl"] ) ) {
            $decoded_cookie = urldecode( $_COOKIE["CookieControl"] );
            $decoded_cookie = stripslashes($decoded_cookie); // Remove backslashes
            $php_json = json_decode( $decoded_cookie );

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error: ' . json_last_error_msg());
                error_log('Decoded cookie: ' . $decoded_cookie);
                $good2go['good2go'] = 0;
                return $good2go;
            }

            $civicConsent = $php_json->optionalCookies;
            if ( $civicConsent->marketing == "accepted" ) {
                $good2go['good2go'] = 1;
            } else {
                $good2go['good2go'] = 0;
            }
        } else {
            // The user has not accepted cookies
            $good2go['good2go'] = 0;
        }
    }
    return $good2go;
}
//add_filter( 'is_ok_to_capture_utms', 'civic_ok_to_go', 10, 1 );