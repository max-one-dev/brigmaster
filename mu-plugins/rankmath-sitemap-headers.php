<?php
/**
 * Plugin Name: Rank Math – sitemap without X-Robots-Tag
 * Description: Removes X-Robots-Tag from Rank Math XML sitemap HTTP responses (not XSL).
 *
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'rank_math/sitemap/http_headers',
	function ( $headers, $is_xsl ) {
		if ( ! empty( $is_xsl ) ) {
			return $headers;
		}
		unset( $headers['X-Robots-Tag'] );
		return $headers;
	},
	99,
	2
);
