<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output favicon and web manifest tags in <head>.
 *
 * Uses get_theme_file_uri() so child-theme overrides work automatically.
 * WP Site Icon outputs its own <link rel="icon"> only when a Site Icon is set
 * in Customizer; our tags take browser precedence as they appear first.
 */
add_action('wp_head', static function (): void {
    $base = trailingslashit(esc_url(get_theme_file_uri('favicon')));
    ?>
<link rel="icon" href="<?php echo $base; ?>favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="<?php echo $base; ?>favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base; ?>favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $base; ?>favicon-16x16.png">
<link rel="apple-touch-icon" href="<?php echo $base; ?>apple-touch-icon.png">
<link rel="manifest" href="<?php echo $base; ?>site.webmanifest">
<meta name="theme-color" content="#5B7C99">
    <?php
}, 1); // priority 1 — before wp_site_icon() at priority 99
