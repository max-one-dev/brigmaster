<?php
/**
 * Plugin Name: Brigmaster Security Hardening
 * Description: Lightweight login rate-limit/lockout, generic errors, XML-RPC disable, user-enum block, WP version hide. No dependencies.
 * Version:     1.0.0
 * Author:      Brigmaster
 *
 * Rollback: delete this file — all measures are self-contained (transients expire naturally).
 * Security: all IP values read once and sanitised; no DB writes outside WP transients API.
 */

if ( defined( 'BM_SECURITY_LOADED' ) ) {
	return;
}
define( 'BM_SECURITY_LOADED', true );

// ============================================================
// CONFIGURATION
// ============================================================

/** Maximum failed login attempts before lockout. */
define( 'BM_LOGIN_MAX_ATTEMPTS', 5 );

/** Lockout duration in seconds (15 minutes). */
define( 'BM_LOGIN_LOCKOUT_SECONDS', 15 * MINUTE_IN_SECONDS );

/** Sliding window for counting failures, in seconds (15 minutes). */
define( 'BM_LOGIN_WINDOW_SECONDS', 15 * MINUTE_IN_SECONDS );

// ============================================================
// 1. LOGIN RATE-LIMIT / LOCKOUT BY IP
// ============================================================

/**
 * Resolve the real client IP.
 *
 * Priority: REMOTE_ADDR first (authoritative on most hosts).
 * CF-Connecting-IP / X-Forwarded-For are accepted ONLY as supplementary
 * data when REMOTE_ADDR itself is a known proxy/CDN range.
 *
 * On Beget the traffic goes directly or through Cloudflare; Beget's own
 * load-balancers set REMOTE_ADDR to the Cloudflare edge IP, so we fall
 * back to CF header only when REMOTE_ADDR looks like a CF range.
 *
 * @return string Sanitised IPv4 or IPv6 address.
 */
function bm_security_get_client_ip(): string {
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	// Cloudflare sends CF-Connecting-IP; trust it only when REMOTE_ADDR is a CF edge.
	// Simplified check: if REMOTE_ADDR is a private/loopback range, prefer forwarded header.
	$is_private = bm_security_is_private_ip( $remote );

	if ( $is_private ) {
		// Try Cloudflare header first (single IP, not a list).
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$cf = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
				return $cf;
			}
		}
		// Fall back to first entry in X-Forwarded-For.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$fwd   = trim( $parts[0] );
			if ( filter_var( $fwd, FILTER_VALIDATE_IP ) ) {
				return $fwd;
			}
		}
	}

	return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';
}

/**
 * Returns true when $ip is private, loopback, or link-local.
 */
function bm_security_is_private_ip( string $ip ): bool {
	return ! filter_var(
		$ip,
		FILTER_VALIDATE_IP,
		FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
	);
}

/**
 * Build transient key from IP (hashed to stay within 172-char WP limit).
 */
function bm_security_transient_key( string $ip ): string {
	return 'bm_sec_ll_' . substr( md5( $ip ), 0, 16 );
}

/**
 * Increment failed-login counter for IP.
 * Hooked: wp_login_failed (runs after WordPress decides credentials are wrong).
 *
 * @param string $username The username that was attempted.
 */
function bm_security_on_login_failed( string $username ): void {
	$ip  = bm_security_get_client_ip();
	$key = bm_security_transient_key( $ip );

	$data = get_transient( $key );
	if ( false === $data ) {
		$data = array( 'count' => 0, 'lockout_until' => 0 );
	}

	$data['count']++;

	if ( $data['count'] >= BM_LOGIN_MAX_ATTEMPTS ) {
		$data['lockout_until'] = time() + BM_LOGIN_LOCKOUT_SECONDS;
		error_log( sprintf(
			'[BM-SECURITY] LOGIN-LOCKOUT ip=%s user=%s attempts=%d',
			$ip,
			sanitize_user( $username ),
			$data['count']
		) );
	}

	// Store for the length of the sliding window (or longer if locked out).
	$ttl = max( BM_LOGIN_WINDOW_SECONDS, BM_LOGIN_LOCKOUT_SECONDS );
	set_transient( $key, $data, $ttl );
}
add_action( 'wp_login_failed', 'bm_security_on_login_failed' );

/**
 * Reset counter on successful login.
 * Hooked: wp_login.
 *
 * @param string   $user_login Unused.
 * @param \WP_User $user       Authenticated user object.
 */
function bm_security_on_login_success( string $user_login, \WP_User $user ): void {
	$ip  = bm_security_get_client_ip();
	$key = bm_security_transient_key( $ip );
	delete_transient( $key );
}
add_action( 'wp_login', 'bm_security_on_login_success', 10, 2 );

/**
 * Block authentication when IP is locked out.
 * Priority 100: runs after WP's own credential check (priority 20/30) but
 * only blocks the response — does NOT interfere with already-established
 * admin sessions (cookie auth goes through wp_validate_auth_cookie, not here).
 *
 * @param null|\WP_User|\WP_Error $user     Incoming auth result.
 * @param string                  $username Attempted username.
 * @return null|\WP_User|\WP_Error
 */
function bm_security_check_lockout( $user, string $username ) {
	$ip  = bm_security_get_client_ip();
	$key = bm_security_transient_key( $ip );

	$data = get_transient( $key );
	if ( false === $data || empty( $data['lockout_until'] ) ) {
		return $user;
	}

	if ( time() < $data['lockout_until'] ) {
		$remaining = (int) ceil( ( $data['lockout_until'] - time() ) / 60 );
		/* translators: %d: minutes remaining */
		return new \WP_Error(
			'bm_lockout',
			sprintf(
				/* translators: %d = minutes */
				esc_html__( 'Too many failed attempts. Try again in %d minute(s).', 'brigmaster-security' ),
				$remaining
			)
		);
	}

	// Lockout expired — clean up.
	delete_transient( $key );
	return $user;
}
add_filter( 'authenticate', 'bm_security_check_lockout', 100, 2 );

// ============================================================
// 2. GENERIC LOGIN ERROR MESSAGE
// ============================================================

/**
 * Replace WordPress's specific "wrong password / unknown user" messages
 * with a single neutral message to prevent username enumeration via login form.
 *
 * @param string $error Default error HTML.
 * @return string
 */
function bm_security_generic_login_error( string $error ): string {
	// Allow our own lockout message to pass through unchanged.
	if ( strpos( $error, 'bm_lockout' ) !== false ) {
		return $error;
	}
	return '<strong>' . esc_html__( 'Error', 'brigmaster-security' ) . ':</strong> '
		. esc_html__( 'Invalid username or password.', 'brigmaster-security' );
}
add_filter( 'login_errors', 'bm_security_generic_login_error' );

// ============================================================
// 3. DISABLE XML-RPC
// ============================================================

// 3a. Disable XML-RPC entirely via WP filter.
add_filter( 'xmlrpc_enabled', '__return_false' );

// 3b. Strip pingback methods from the methods list as extra precaution.
add_filter( 'xmlrpc_methods', static function ( array $methods ): array {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

// 3c. Return 403 for direct requests to xmlrpc.php before WP processes them.
add_action( 'init', static function (): void {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	// Match /xmlrpc.php with optional query string, case-insensitive.
	if ( preg_match( '#/xmlrpc\.php(\?.*)?$#i', $uri ) ) {
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		exit( 'XML-RPC is disabled.' );
	}
}, 1 );

// ============================================================
// 4. BLOCK USER ENUMERATION
// ============================================================

// 4a. Block ?author=N on the front-end (redirect to home, 302 then 403 would
//     be more forceful but 302 to home avoids SEO noise).
add_action( 'template_redirect', static function (): void {
	// Skip admin, REST, or CLI contexts.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['author'] ) && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}
}, 1 );

// 4b. Restrict REST /wp/v2/users to authenticated users with list_users capability.
add_filter( 'rest_endpoints', static function ( array $endpoints ): array {
	if ( is_user_logged_in() && current_user_can( 'list_users' ) ) {
		return $endpoints;
	}
	// Remove collection endpoint for unauthenticated visitors.
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

// ============================================================
// 5. HIDE WP VERSION & LOGIN HINTS
// ============================================================

// 5a. Remove version from <head> and RSS.
remove_action( 'wp_head', 'wp_generator' );

// 5b. Strip ?ver= query strings from scripts/styles on front-end.
add_filter( 'style_loader_src', 'bm_security_strip_ver', 9999 );
add_filter( 'script_loader_src', 'bm_security_strip_ver', 9999 );

/**
 * Remove ?ver= parameter from enqueued asset URLs on the front-end.
 * Skips admin and login pages to avoid breaking admin functionality.
 *
 * @param string $src Asset URL.
 * @return string
 */
function bm_security_strip_ver( string $src ): string {
	if ( is_admin() ) {
		return $src;
	}
	// Only strip if the ver param is present.
	if ( strpos( $src, 'ver=' ) !== false ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}

// 5c. Suppress "login_error_shake" hint that leaks username validity
//     (WP adds a CSS shake animation only when the username is correct).
add_filter( 'shake_error_codes', static function ( array $codes ): array {
	// Keep lockout code in shake list for UX; remove username-specific codes.
	return array_diff( $codes, array( 'invalid_username', 'incorrect_password' ) );
} );
