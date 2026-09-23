<?php
/**
 * Plugin Name: Brigmaster Analytics Env
 * Description: Single source of truth for production environment detection used by all Brigmaster analytics mu-plugins. Must load before brigmaster-ga4.php and brigmaster-metrika.php (alphabetical order guaranteed).
 * Version:     1.0.0
 * Author:      Brigmaster
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'brigmaster_is_production' ) ) {
    /**
     * Returns true only on the production environment.
     * Override via filter 'brigmaster_is_production'.
     */
    function brigmaster_is_production(): bool {
        $is_production = ( wp_get_environment_type() === 'production' );
        return (bool) apply_filters( 'brigmaster_is_production', $is_production );
    }
}
