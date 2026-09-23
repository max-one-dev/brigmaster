<?php
/**
 * Plugin Name: Brigmaster Performance
 * Description: Conditionally dequeues Contact Form 7 assets off non-contact pages, disables jquery-migrate on the front end, and preloads Inter fonts for faster LCP. No caching, safe with/without CF7 & Metrika.
 * Version:     1.0.0
 * Author:      Brigmaster
 *
 * Rollback: delete this file — all measures are self-contained, no DB changes.
 * Safety: every feature is guarded for admin, checks asset existence before mutating.
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'BM_PERFORMANCE_LOADED' ) ) {
	return;
}
define( 'BM_PERFORMANCE_LOADED', true );

// ============================================================
// 1. Contact Form 7 — assets only on the contacts page
// ============================================================

/**
 * Returns the original load value on the contacts page, false elsewhere.
 *
 * Registered for both wpcf7_load_js and wpcf7_load_css.
 * Harmless when CF7 is not installed (filters simply never fire).
 *
 * @param mixed $load Original value passed by CF7.
 * @return mixed
 */
function brigmaster_perf_cf7_load( $load ) {
	if ( is_page( array( 'kontakty', 'contacts', 'contact' ) ) ) {
		return $load;
	}

	return false;
}

if ( ! is_admin() ) {
	add_filter( 'wpcf7_load_js',  'brigmaster_perf_cf7_load' );
	add_filter( 'wpcf7_load_css', 'brigmaster_perf_cf7_load' );
}

// ============================================================
// 2. Remove jquery-migrate on the front end
// ============================================================

/**
 * Strips 'jquery-migrate' from the dependency list of the 'jquery' handle.
 *
 * @param WP_Scripts $scripts Global scripts registry.
 * @return void
 */
function brigmaster_perf_remove_jquery_migrate( WP_Scripts $scripts ): void {
	if ( ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}

	$deps = $scripts->registered['jquery']->deps;

	if ( ! is_array( $deps ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_values(
		array_filter( $deps, static function ( string $dep ): bool {
			return 'jquery-migrate' !== $dep;
		} )
	);
}

if ( ! is_admin() ) {
	add_action( 'wp_default_scripts', 'brigmaster_perf_remove_jquery_migrate' );
}

// ============================================================
// 3. Preload Inter font files (Regular + SemiBold) for LCP
// ============================================================

/**
 * Outputs <link rel="preload"> tags for Inter font files in wp_head.
 *
 * Priority 1 so the hints appear before any enqueued stylesheets.
 *
 * @return void
 */
function brigmaster_perf_preload_fonts(): void {
	$base = get_template_directory_uri() . '/assets/dist/fonts/';

	$fonts = array(
		'Inter-Regular.woff2',
		'Inter-SemiBold.woff2',
	);

	foreach ( $fonts as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base . $file )
		);
	}
}

if ( ! is_admin() ) {
	add_action( 'wp_head', 'brigmaster_perf_preload_fonts', 1 );
}

// ============================================================
// 4. Preconnect hints for analytics origins
// ============================================================

/**
 * Outputs <link rel="preconnect"> tags for third-party analytics origins.
 *
 * Priority 1 so hints appear early in wp_head.
 *
 * @return void
 */
function brigmaster_perf_preconnect_origins(): void {
	$origins = array(
		'https://mc.yandex.ru',
		'https://www.googletagmanager.com',
	);

	foreach ( $origins as $origin ) {
		printf(
			'<link rel="preconnect" href="%s" crossorigin>' . "\n",
			esc_url( $origin )
		);
	}
}

if ( ! is_admin() ) {
	add_action( 'wp_head', 'brigmaster_perf_preconnect_origins', 1 );
}
