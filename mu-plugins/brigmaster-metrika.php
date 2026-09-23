<?php
/**
 * Plugin Name: Brigmaster Metrika
 * Description: Outputs the official async Yandex.Metrika counter (no jQuery, non-blocking). Replaces the wp-yandex-metrika plugin. CF7 reachGoal event in footer.
 * Version:     1.0.0
 * Author:      Brigmaster
 *
 * Activation: define( 'BRIGMASTER_METRIKA_COUNTER_ID', '<your_counter_id>' ) in wp-config.php.
 * Does nothing if constant is absent or resolved id is empty.
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() ) {
    return;
}

if ( ! defined( 'BRIGMASTER_METRIKA_COUNTER_ID' ) ) {
    if ( defined( 'BRIGMASTER_YANDEX_METRIKA_COUNTER_ID' ) && '' !== BRIGMASTER_YANDEX_METRIKA_COUNTER_ID ) {
        define( 'BRIGMASTER_METRIKA_COUNTER_ID', BRIGMASTER_YANDEX_METRIKA_COUNTER_ID );
    } else {
        return;
    }
}

if ( 0 === (int) BRIGMASTER_METRIKA_COUNTER_ID ) {
    return;
}

// Production guard: suppress snippet + footer events on dev/staging.
if ( function_exists( 'brigmaster_is_production' ) && ! brigmaster_is_production() ) {
    return;
}

/**
 * Output official async Yandex.Metrika snippet in wp_head.
 * Priority 5 — early, before most theme output.
 */
add_action( 'wp_head', 'brigmaster_metrika_head_snippet', 5 );
add_action( 'wp_footer', 'brigmaster_metrika_cf7_unified_event', 20 );

function brigmaster_metrika_head_snippet(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;

    $counter_id_int  = (int) BRIGMASTER_METRIKA_COUNTER_ID;
    $tag_src         = esc_js( 'https://mc.yandex.ru/metrika/tag.js' );
    $noscript_src    = esc_attr( 'https://mc.yandex.ru/watch/' . BRIGMASTER_METRIKA_COUNTER_ID );
    ?>
<!-- Brigmaster Metrika -->
<script>
window.ym = window.ym || function(){(window.ym.a = window.ym.a || []).push(arguments)};
window.ym.l = 1 * new Date();

ym(<?php echo $counter_id_int; ?>, 'init', {
    clickmap:true,
    trackLinks:true,
    accurateTrackBounce:true,
    webvisor:true
});

(function(){
    var _ymSrc = '<?php echo $tag_src; ?>';
    var _ymLoaded = false;
    function _loadYm(){
        if (_ymLoaded) return;
        _ymLoaded = true;
        for (var j = 0; j < document.scripts.length; j++) {
            if (document.scripts[j].src === _ymSrc) return;
        }
        var s = document.createElement('script');
        s.async = true;
        s.src = _ymSrc;
        document.head.appendChild(s);
    }
    if (document.readyState === 'complete') {
        _loadYm();
    } else {
        window.addEventListener('load', _loadYm, {once:true});
    }
}());
</script>
<noscript><div><img src="<?php echo $noscript_src; ?>" style="position:absolute;left:-9999px;" alt="" /></div></noscript>
<!-- /Brigmaster Metrika -->
    <?php
}

/**
 * Unified CF7 generate_lead listener — dual-dispatch (GA4 + Metrika).
 * Single listener site-wide; the ga4.php CF7 hook has been removed.
 * Fires only when CF7 is active and we are on production.
 */
function brigmaster_metrika_cf7_unified_event(): void {
    if ( ! function_exists( 'wpcf7' ) && ! class_exists( 'WPCF7' ) ) {
        return;
    }
    $counter_id_int = (int) BRIGMASTER_METRIKA_COUNTER_ID;
    ?>
<script>
(function(){
    document.body.addEventListener('wpcf7mailsent', function(){
        try {
            if (typeof gtag === 'function') {
                gtag('event', 'generate_lead', {form_type: 'contact'});
            }
        } catch(e) {}
        try {
            if (typeof ym === 'function') {
                ym(<?php echo $counter_id_int; ?>, 'reachGoal', 'generate_lead', {form_type: 'contact'});
            }
        } catch(e) {}
    }, {once: false});
}());
</script>
    <?php
}
