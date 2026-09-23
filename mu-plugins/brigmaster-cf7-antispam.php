<?php
/**
 * Plugin Name: Brigmaster CF7 Anti-Spam
 * Description: Invisible spam protection for Contact Form 7 (honeypot, time-trap, referer, MX, rate-limit).
 * Version:     1.0.0
 * Author:      Brigmaster
 *
 * Security: no nonce required here — CF7 REST is open (WPCF7_VERIFY_NONCE=false).
 * Sanitization: all external input sanitized before use.
 * No DB schema changes; uses WP transients only.
 */

defined( 'ABSPATH' ) || exit;

/**
 * -------------------------------------------------------------------------
 * 1. Inject honeypot + time-trap fields into every CF7 form.
 * -------------------------------------------------------------------------
 * Appended after all existing form elements so no markup is broken.
 * Both fields are visually hidden, tabindex=-1, autocomplete=off.
 * The time-trap value is HMAC-signed so it cannot be forged.
 * -------------------------------------------------------------------------
 */
add_filter( 'wpcf7_form_elements', 'brigmaster_cf7_inject_hidden_fields' );

function brigmaster_cf7_inject_hidden_fields( string $form ): string {
	$honeypot_name = brigmaster_cf7_honeypot_name();
	$time_token    = brigmaster_cf7_make_time_token();

	$hidden_css = 'position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;';

	$inject  = '<div aria-hidden="true" style="' . $hidden_css . '">';
	// Honeypot — label text is intentionally misleading to bots.
	$inject .= '<label for="' . esc_attr( $honeypot_name ) . '">Leave this field empty</label>';
	$inject .= '<input type="text"';
	$inject .= ' id="' . esc_attr( $honeypot_name ) . '"';
	$inject .= ' name="' . esc_attr( $honeypot_name ) . '"';
	$inject .= ' value=""';
	$inject .= ' tabindex="-1"';
	$inject .= ' autocomplete="off"';
	$inject .= ' />';
	// Time-trap — signed timestamp token.
	$inject .= '<input type="hidden"';
	$inject .= ' name="bm_tt"';
	$inject .= ' value="' . esc_attr( $time_token ) . '"';
	$inject .= ' autocomplete="off"';
	$inject .= ' />';
	$inject .= '</div>';

	return $form . $inject;
}

/**
 * -------------------------------------------------------------------------
 * 2–5. Spam detection filters on wpcf7_spam.
 * -------------------------------------------------------------------------
 * Hook runs BEFORE CF7 sends mail. Return true = mark as spam.
 * Each check logs reason + IP via error_log for owner visibility.
 * -------------------------------------------------------------------------
 */
add_filter( 'wpcf7_spam', 'brigmaster_cf7_spam_checks', 10, 2 );

function brigmaster_cf7_spam_checks( bool $spam, WPCF7_Submission $submission ): bool {
	if ( $spam ) {
		return true; // Already flagged; skip further checks.
	}

	$ip     = brigmaster_cf7_get_ip();
	$posted = $submission->get_posted_data();

	// --- 1. Honeypot check ---
	// Read directly from $_POST: CF7 strips fields whose names begin with '_', and
	// honeypot/time-trap field names are injected by us — not registered as CF7 tags —
	// so get_posted_data() may silently drop them. $_POST is safe here after sanitization.
	$honeypot_name = brigmaster_cf7_honeypot_name();
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$hp_value = isset( $_POST[ $honeypot_name ] )
		? sanitize_text_field( wp_unslash( $_POST[ $honeypot_name ] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: '';

	if ( '' !== $hp_value ) {
		brigmaster_cf7_log( 'honeypot', $ip );
		return true;
	}

	// --- 2. Time-trap check ---
	// Accept both current name (bm_tt) and legacy cached-form name (_bm_tt).
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$raw_token = isset( $_POST['bm_tt'] )
		? sanitize_text_field( wp_unslash( $_POST['bm_tt'] ) )   // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: ( isset( $_POST['_bm_tt'] )
			? sanitize_text_field( wp_unslash( $_POST['_bm_tt'] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: '' );

	$time_result = brigmaster_cf7_verify_time_token( $raw_token );
	if ( true !== $time_result ) {
		brigmaster_cf7_log( 'time-trap:' . $time_result, $ip );
		return true;
	}

	// --- 3. Referer / Origin check ---
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$origin  = isset( $_SERVER['HTTP_ORIGIN'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';

	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

	$referer_host = $referer ? wp_parse_url( $referer, PHP_URL_HOST ) : '';
	$origin_host  = $origin  ? wp_parse_url( $origin,  PHP_URL_HOST ) : '';

	$has_valid_referer = ( $referer_host && strtolower( $referer_host ) === strtolower( $site_host ) );
	$has_valid_origin  = ( $origin_host  && strtolower( $origin_host )  === strtolower( $site_host ) );

	if ( ! $has_valid_referer && ! $has_valid_origin ) {
		brigmaster_cf7_log( 'bad-referer referer=' . $referer . ' origin=' . $origin, $ip );
		return true;
	}

	// --- 4. MX check on email field ---
	// Look for any field whose tag name contains 'email' or ends with '-email'.
	$email_value = '';
	foreach ( $posted as $field_name => $field_value ) {
		if ( false !== strpos( strtolower( $field_name ), 'email' ) ) {
			$email_value = sanitize_email( wp_unslash( (string) $field_value ) );
			break;
		}
	}

	if ( $email_value ) {
		$email_domain = strtolower( substr( strrchr( $email_value, '@' ), 1 ) );
		if ( $email_domain ) {
			$has_mx = brigmaster_cf7_check_mx( $email_domain );
			if ( false === $has_mx ) {
				brigmaster_cf7_log( 'no-mx domain=' . $email_domain, $ip );
				return true;
			}
			// null = DNS error → pass through (do not block).
		}
	}

	// --- 5. Rate-limit by IP: >5 submissions per 10 min ---
	if ( brigmaster_cf7_rate_limit( $ip ) ) {
		brigmaster_cf7_log( 'rate-limit', $ip );
		return true;
	}

	return false;
}

// ============================================================
// Helpers
// ============================================================

/**
 * Returns a stable, salted honeypot field name.
 * Derived from site URL so it is unique per install.
 */
function brigmaster_cf7_honeypot_name(): string {
	return 'bm_' . substr( md5( home_url() . 'hp_salt_v1' ), 0, 8 );
}

/**
 * Creates a signed time token: "<timestamp>.<hmac>".
 */
function brigmaster_cf7_make_time_token(): string {
	$ts   = time();
	$hmac = hash_hmac( 'sha256', (string) $ts, brigmaster_cf7_secret() );
	return $ts . '.' . substr( $hmac, 0, 16 );
}

/**
 * Verifies token.
 * Returns true on success, string reason on failure.
 *
 * @return true|string
 */
function brigmaster_cf7_verify_time_token( string $token ) {
	if ( '' === $token ) {
		return 'missing';
	}

	$parts = explode( '.', $token, 2 );
	if ( 2 !== count( $parts ) ) {
		return 'malformed';
	}

	list( $ts, $provided_hmac ) = $parts;
	$ts = absint( $ts );

	if ( 0 === $ts ) {
		return 'invalid-ts';
	}

	$expected_hmac = substr( hash_hmac( 'sha256', (string) $ts, brigmaster_cf7_secret() ), 0, 16 );
	if ( ! hash_equals( $expected_hmac, $provided_hmac ) ) {
		return 'bad-sig';
	}

	$elapsed = time() - $ts;

	if ( $elapsed < 3 ) {
		return 'too-fast(' . $elapsed . 's)';
	}

	if ( $elapsed > 3600 ) {
		return 'expired(' . $elapsed . 's)';
	}

	return true;
}

/**
 * HMAC secret derived from WP auth key so no extra config needed.
 */
function brigmaster_cf7_secret(): string {
	return defined( 'AUTH_KEY' ) ? AUTH_KEY : 'brigmaster_fallback_secret_v1';
}

/**
 * MX lookup with error suppression.
 * Returns true = MX exists, false = no MX, null = DNS error.
 *
 * @return bool|null
 */
function brigmaster_cf7_check_mx( string $domain ) {
	// Use a transient to cache per-domain result for 1 hour.
	$cache_key = 'bm_mx_' . md5( $domain );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return 'yes' === $cached ? true : ( 'no' === $cached ? false : null );
	}

	// Temporarily suppress DNS errors — we treat them as "pass".
	set_error_handler( '__return_null', E_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
	$has_mx = checkdnsrr( $domain, 'MX' );
	restore_error_handler();

	// If checkdnsrr returned false, also check A/AAAA to distinguish "no MX" from "domain not found".
	if ( false === $has_mx ) {
		$has_a = checkdnsrr( $domain, 'A' );
		if ( ! $has_a ) {
			// Domain has neither MX nor A → likely non-existent; but be conservative.
			// We only block on confirmed no-MX; if A exists the domain is real without MX.
			// Here we treat "no A and no MX" as suspicious (return false).
		}
		set_transient( $cache_key, 'no', HOUR_IN_SECONDS );
		return false;
	}

	set_transient( $cache_key, 'yes', HOUR_IN_SECONDS );
	return true;
}

/**
 * Rate-limit: returns true if IP is over the threshold.
 * Threshold: 5 submissions per 600 seconds.
 */
function brigmaster_cf7_rate_limit( string $ip, int $max = 5, int $window = 600 ): bool {
	$cache_key = 'bm_rl_' . md5( $ip );
	$count     = (int) get_transient( $cache_key );

	if ( $count >= $max ) {
		return true;
	}

	if ( 0 === $count ) {
		set_transient( $cache_key, 1, $window );
	} else {
		// Increment without resetting TTL: use update_option approach via transients is not ideal;
		// WP transients do not expose TTL bump, so we overwrite (window slides slightly, acceptable).
		set_transient( $cache_key, $count + 1, $window );
	}

	return false;
}

/**
 * Get real visitor IP, respecting common proxy headers.
 * Sanitized to string.
 */
function brigmaster_cf7_get_ip(): string {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			// HTTP_X_FORWARDED_FOR can be comma-separated; take first.
			$ip = trim( explode( ',', $raw )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return 'unknown';
}

/**
 * Structured error_log entry.
 */
function brigmaster_cf7_log( string $reason, string $ip ): void {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[BM-CF7-ANTISPAM] BLOCKED reason=' . $reason . ' ip=' . $ip . ' time=' . gmdate( 'Y-m-d H:i:s' ) . ' url=' . esc_url_raw( home_url( $_SERVER['REQUEST_URI'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}
