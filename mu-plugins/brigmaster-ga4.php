<?php
/**
 * Plugin Name: Brigmaster GA4
 * Description: Outputs Google Analytics 4 (gtag.js) snippet via BRIGMASTER_GA4_MEASUREMENT_ID wp-config constant. No admin output. CF7 generate_lead event in footer.
 * Version:     1.0.0
 * Author:      Brigmaster
 *
 * Activation: define( 'BRIGMASTER_GA4_MEASUREMENT_ID', 'G-XXXXXXX' ) in wp-config.php.
 * Does nothing if constant is absent or empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Guard: skip admin and bail early if constant absent.
if ( is_admin() ) {
    return;
}

if ( ! defined( 'BRIGMASTER_GA4_MEASUREMENT_ID' ) || '' === BRIGMASTER_GA4_MEASUREMENT_ID ) {
    return;
}

// Production guard: suppress GA4 snippet on dev/staging.
if ( function_exists( 'brigmaster_is_production' ) && ! brigmaster_is_production() ) {
    return;
}

/**
 * Output GA4 gtag.js snippet once in wp_head.
 * Priority 1 — early, but after theme's own head output.
 */
add_action( 'wp_head', 'brigmaster_ga4_head_snippet', 1 );

function brigmaster_ga4_head_snippet(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;

    $measurement_id = esc_js( BRIGMASTER_GA4_MEASUREMENT_ID );
    $gtag_src       = esc_js( 'https://www.googletagmanager.com/gtag/js?id=' . BRIGMASTER_GA4_MEASUREMENT_ID );
    ?>
<!-- Brigmaster GA4 -->
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo $measurement_id; ?>');

(function(){
    var _ga4Loaded = false;
    function _loadGa4(){
        if (_ga4Loaded) return;
        _ga4Loaded = true;
        var s = document.createElement('script');
        s.async = true;
        s.src = '<?php echo $gtag_src; ?>';
        document.head.appendChild(s);
    }
    if (document.readyState === 'complete') {
        _loadGa4();
    } else {
        window.addEventListener('load', _loadGa4, {once:true});
    }
}());
</script>
<!-- /Brigmaster GA4 -->
    <?php
}

// CF7 generate_lead listener removed — unified dual-dispatch (GA4 + Metrika)
// is now handled exclusively in brigmaster-metrika.php::brigmaster_metrika_cf7_unified_event().
