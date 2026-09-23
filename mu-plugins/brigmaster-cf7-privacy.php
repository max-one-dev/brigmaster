<?php
/**
 * Plugin Name: Brigmaster CF7 Privacy
 * Description: Adds ym-disable-keys CSS class to PII input fields in Contact Form 7 for Yandex Webvisor keystroke masking.
 * Version:     1.0.0
 * Author:      Brigmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * Append ym-disable-keys to CF7 PII field elements.
 *
 * Targets: <input type="text|email|tel|url|search"> and <textarea>.
 * Skips: submit, hidden, checkbox, radio, number, file, date, and any
 * element that already carries the class (no double-add).
 *
 * @param string $content Rendered CF7 form HTML.
 * @return string Modified HTML.
 */
add_filter( 'wpcf7_form_elements', 'brigmaster_cf7_add_ym_disable_keys' );

function brigmaster_cf7_add_ym_disable_keys( $content ) {
	$pii_class = 'ym-disable-keys';

	// Match <input> tags whose type is one of the PII types.
	$content = preg_replace_callback(
		'/<input\b([^>]*)\btype=["\'](?:text|email|tel|url|search)["\']([^>]*)>/i',
		static function ( $matches ) use ( $pii_class ) {
			return brigmaster_inject_class( $matches[0], $pii_class );
		},
		$content
	);

	// Match all <textarea> tags (no type attribute).
	$content = preg_replace_callback(
		'/<textarea\b([^>]*)>/i',
		static function ( $matches ) use ( $pii_class ) {
			return brigmaster_inject_class( $matches[0], $pii_class );
		},
		$content
	);

	return $content;
}

/**
 * Inject a CSS class into an HTML open tag string.
 * Appends to an existing class attribute or adds a new one.
 * Never double-adds if class is already present.
 *
 * @param string $tag       Full opening HTML tag, e.g. <input class="foo" ...>.
 * @param string $new_class CSS class name to inject.
 * @return string Tag with class injected.
 */
function brigmaster_inject_class( $tag, $new_class ) {
	// Already has the class — return untouched.
	if ( preg_match( '/\bclass=["\'][^"\']*\b' . preg_quote( $new_class, '/' ) . '\b[^"\']*["\']/', $tag ) ) {
		return $tag;
	}

	// Has a class attribute — append to it.
	if ( preg_match( '/\bclass=(["\'])([^"\']*)\1/', $tag ) ) {
		return preg_replace(
			'/\bclass=(["\'])([^"\']*)\1/',
			'class=$1$2 ' . $new_class . '$1',
			$tag,
			1
		);
	}

	// No class attribute — insert one before the closing > or />.
	return preg_replace( '/(\s*\/?>)$/', ' class="' . $new_class . '"$1', $tag, 1 );
}
