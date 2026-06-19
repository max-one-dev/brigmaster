<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('BM_THEME_VERSION', '0.2.0');
define('BM_THEME_PATH', __DIR__ . '/..');
define('BM_THEME_URL', get_stylesheet_directory_uri());

// Backward-compatible aliases while legacy block/migration classes are renamed gradually.
define('CONSTRUCTLY_THEME_VERSION', BM_THEME_VERSION);
define('CONSTRUCTLY_THEME_PATH', BM_THEME_PATH);
define('CONSTRUCTLY_THEME_URL', BM_THEME_URL);

/**
 * For block templates: esc_url() strips valid same-page anchors like #calculators.
 */
function constructly_normalize_internal_url(string $url): string
{
    $url = trim($url);

    if ($url === '' || $url === '#' || str_starts_with($url, '#')) {
        return $url;
    }

    $parsed_url = wp_parse_url($url);
    $home_url = wp_parse_url(home_url('/'));

    if (!is_array($parsed_url) || !is_array($home_url)) {
        return $url;
    }

    $home_host = strtolower((string) ($home_url['host'] ?? ''));
    $url_host = strtolower((string) ($parsed_url['host'] ?? $home_host));

    if ($home_host === '' || $url_host !== $home_host) {
        return $url;
    }

    $path = isset($parsed_url['path']) ? untrailingslashit((string) $parsed_url['path']) : '';
    if ($path === '') {
        $path = '/';
    }

    $query = isset($parsed_url['query']) && $parsed_url['query'] !== '' ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) && $parsed_url['fragment'] !== '' ? '#' . $parsed_url['fragment'] : '';

    if (!isset($parsed_url['scheme']) && !isset($parsed_url['host'])) {
        return $path . $query . $fragment;
    }

    $scheme = (string) ($parsed_url['scheme'] ?? ($home_url['scheme'] ?? ''));
    $port = isset($parsed_url['port']) ? ':' . (string) $parsed_url['port'] : '';

    return $scheme . '://' . $home_host . $port . $path . $query . $fragment;
}

function constructly_esc_block_href(string $url): string
{
    $url = trim($url);

    if ($url === '' || $url === '#') {
        return '#';
    }

    if (str_starts_with($url, '#')) {
        return esc_attr($url);
    }

    return esc_url(constructly_normalize_internal_url($url));
}

function constructly_esc_block_image_src(string $src): string
{
    $src = trim($src);

    if ($src === '') {
        return '';
    }

    if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, '/')) {
        return esc_url($src);
    }

    return esc_url(get_theme_file_uri($src));
}

require_once __DIR__ . '/class-constructly-assets.php';
require_once __DIR__ . '/class-constructly-theme-setup.php';
require_once __DIR__ . '/template-tags.php';
require_once __DIR__ . '/bm-estimator-shortcodes.php';
require_once __DIR__ . '/frontend/class-constructly-frontend.php';
require_once __DIR__ . '/editor/class-constructly-blocks.php';
require_once __DIR__ . '/content/migrations/class-constructly-migration-helpers.php';
require_once __DIR__ . '/content/migrations/page-home.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-fundament.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-fundament-lentochnyj.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-fundament-svajnyj.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-fundament-plitnyj.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-kirpich.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-styazhka.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-plitka.php';
require_once __DIR__ . '/content/migrations/page-kalkulyatory-gipsokarton.php';
require_once __DIR__ . '/content/migrations/page-about.php';
require_once __DIR__ . '/content/migrations/page-contacts.php';
require_once __DIR__ . '/content/migrations/page-methodology.php';
require_once __DIR__ . '/content/migrations/page-privacy.php';
require_once __DIR__ . '/content/migrations/page-user-agreement.php';
require_once __DIR__ . '/content/class-constructly-content-migrations.php';
require_once __DIR__ . '/content/class-constructly-articles-seed.php';
require_once __DIR__ . '/content/class-constructly-content-cli.php';

Constructly_Theme_Setup::init();
Constructly_Assets::init();
Constructly_Frontend::init();
Constructly_Blocks::init();
Constructly_Content_Migrations::init();
Constructly_Content_Cli::init();

// Articles / База знаний are hidden pre-launch. Remove this line to re-enable.
add_filter( 'constructly_hide_articles_block', '__return_true' );
