<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Vite output: {@see assets/dist/.vite/manifest.json}
 */
final class Constructly_Assets
{
    private const MANIFEST_RELATIVE = '/assets/dist/.vite/manifest.json';

    private const ENTRY_COMMON = 'common.scss';

    private const ENTRY_EDITOR = 'editor.js';

    private const PAGE_ENTRIES = [
        'about' => 'pages/about/index.html',
        'archive' => 'pages/archive/index.html',
        'article' => 'pages/article/index.html',
        'calculator' => 'pages/calculator/index.html',
        'contacts' => 'pages/contacts/index.html',
        'home' => 'pages/home/index.html',
        'hub' => 'pages/hub/index.html',
        'methodology' => 'pages/methodology/index.html',
        'privacy' => 'pages/privacy/index.html',
    ];

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $manifest_cache = null;

    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'disable_frontend_global_styles'], 0);
        add_action('wp_enqueue_scripts', [self::class, 'disable_frontend_core_block_styles'], 100);
        // Funnel core block CSS into the single wp-block-library handle (instead of
        // per-block inline styles) so it can be reliably dequeued on the frontend.
        add_filter('should_load_separate_core_block_assets', '__return_false');
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_assets'], 20);
        add_filter('script_loader_tag', [self::class, 'set_module_script_type'], 10, 2);
    }

    /**
     * Theme bundles are built by Vite as ES modules, so their <script> tags must carry
     * type="module" or the browser refuses to execute them. Scoped to handles flagged
     * in {@see self::enqueue_entry_script()}.
     */
    public static function set_module_script_type(string $tag, string $handle): string
    {
        if (!wp_scripts()->get_data($handle, 'bm_module_script')) {
            return $tag;
        }

        if (str_contains($tag, ' type="module"')) {
            return $tag;
        }

        return (string) preg_replace('/<script(?=\s)/', '<script type="module"', $tag, 1);
    }

    public static function disable_frontend_global_styles(): void
    {
        if (is_admin()) {
            return;
        }

        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
        wp_dequeue_style('global-styles');
        wp_deregister_style('global-styles');
        wp_dequeue_style('core-block-supports');
        wp_deregister_style('core-block-supports');
    }

    /**
     * Core blocks (e.g. core/table inside .bm-prose) are styled by the theme, so the
     * default WordPress block stylesheets are redundant on the frontend. With separate
     * core block assets disabled (see init()), all of that CSS lives in the
     * wp-block-library / wp-block-library-theme handles, which we drop here. The editor
     * keeps these styles (this runs on wp_enqueue_scripts, not in admin).
     */
    public static function disable_frontend_core_block_styles(): void
    {
        if (is_admin()) {
            return;
        }

        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_manifest(): ?array
    {
        if (self::$manifest_cache !== null) {
            return self::$manifest_cache;
        }

        $path = CONSTRUCTLY_THEME_PATH . self::MANIFEST_RELATIVE;
        if (!is_readable($path)) {
            self::$manifest_cache = null;

            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        self::$manifest_cache = is_array($decoded) ? $decoded : null;

        return self::$manifest_cache;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_entry(string $entry_key): ?array
    {
        $manifest = self::get_manifest();
        if ($manifest === null) {
            return null;
        }

        $entry = $manifest[$entry_key] ?? null;

        return is_array($entry) ? $entry : null;
    }

    public static function enqueue_main_bundle(): void
    {
        $entry = self::get_entry(self::ENTRY_COMMON);
        if ($entry === null) {
            return;
        }

        self::enqueue_entry_styles($entry, 'bm-theme', []);
        self::enqueue_entry_script($entry, 'bm-theme', [], true);
        self::enqueue_current_page_bundle();
    }

    private static function enqueue_current_page_bundle(): void
    {
        $page_key = self::resolve_current_page_key();
        if ($page_key === null) {
            return;
        }

        $entry_key = self::PAGE_ENTRIES[$page_key] ?? null;
        if ($entry_key === null) {
            return;
        }

        $entry = self::get_entry($entry_key);
        if ($entry === null) {
            return;
        }

        self::enqueue_entry_styles($entry, 'bm-theme-' . $page_key, ['bm-theme']);
        self::enqueue_entry_script($entry, 'bm-theme-' . $page_key, ['bm-theme'], true);
    }

    private static function resolve_current_page_key(): ?string
    {
        if (is_front_page()) {
            return 'home';
        }

        $page_key = self::resolve_current_page_key_by_path();
        if ($page_key !== null) {
            return $page_key;
        }

        if (is_page_template('page-templates/page-calculator.php')) {
            return 'calculator';
        }

        if (is_page_template('page-templates/page-about.php')) {
            return 'about';
        }

        if (is_page_template('page-templates/page-contacts.php')) {
            return 'contacts';
        }

        if (is_page_template('page-templates/page-hub.php')) {
            return 'hub';
        }

        if (is_page_template('page-templates/page-methodology.php')) {
            return 'methodology';
        }

        if (is_page_template('page-templates/page-privacy.php')) {
            return 'privacy';
        }

        if (is_singular('post')) {
            return 'article';
        }

        if (is_archive() || is_home() || is_search()) {
            return 'archive';
        }

        return null;
    }

    private static function resolve_current_page_key_by_path(): ?string
    {
        $object = get_queried_object();
        if (!$object instanceof WP_Post || $object->post_type !== 'page') {
            return null;
        }

        $path = trim(get_page_uri($object), '/');

        if ($path === '') {
            return null;
        }

        $calculator_paths = [
            'kalkulyatory/fundament/plitnyj',
            'kalkulyatory/fundament/lentochnyj',
            'kalkulyatory/fundament/svajnyj',
            'kalkulyatory/styazhka',
            'kalkulyatory/kirpich',
            'kalkulyatory/plitka',
            'kalkulyatory/gipsokarton',
            'kalkulyator-plitnogo-fundamenta',
            'kalkulyator-lentochnogo-fundamenta',
            'kalkulyator-svajnogo-fundamenta',
            'kalkulyator-styazhki',
            'kalkulyator-kirpicha',
            'kalkulyator-plitki',
            'kalkulyator-gipsokartona',
        ];

        if (in_array($path, $calculator_paths, true)) {
            return 'calculator';
        }

        return match ($path) {
            'kalkulyatory/fundament', 'kalkulyator-fundamenta', 'fundament' => 'hub',
            'o-proekte' => 'about',
            'kontakty' => 'contacts',
            'metodologiya', 'metodologiya-raschetov' => 'methodology',
            'privacy-policy', 'user-agreement' => 'privacy',
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $deps
     */
    private static function enqueue_entry_styles(array $entry, string $handle, array $deps): void
    {
        $css_list = self::resolve_entry_css_list($entry);

        foreach ($css_list as $index => $relative) {
            if ($relative === '') {
                continue;
            }
            $style_handle = $index === 0 ? $handle : $handle . '-' . (string) $index;
            $abs_path = CONSTRUCTLY_THEME_PATH . '/assets/dist/' . $relative;
            wp_enqueue_style(
                $style_handle,
                CONSTRUCTLY_THEME_URL . '/assets/dist/' . $relative,
                $deps,
                is_readable($abs_path) ? (string) filemtime($abs_path) : CONSTRUCTLY_THEME_VERSION
            );
        }
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $deps
     */
    private static function register_entry_styles(array $entry, string $handle, array $deps): void
    {
        $css_list = self::resolve_entry_css_list($entry);

        foreach ($css_list as $index => $relative) {
            if ($relative === '') {
                continue;
            }
            $style_handle = $index === 0 ? $handle : $handle . '-' . (string) $index;
            $abs_path = CONSTRUCTLY_THEME_PATH . '/assets/dist/' . $relative;
            wp_register_style(
                $style_handle,
                CONSTRUCTLY_THEME_URL . '/assets/dist/' . $relative,
                $deps,
                is_readable($abs_path) ? (string) filemtime($abs_path) : CONSTRUCTLY_THEME_VERSION
            );
        }
    }

    /**
     * @param array<string, mixed> $entry
     * @return list<string>
     */
    private static function resolve_entry_css_list(array $entry): array
    {
        $css_list = isset($entry['css']) && is_array($entry['css']) ? $entry['css'] : [];
        $file = isset($entry['file']) && is_string($entry['file']) ? $entry['file'] : '';

        if ($file !== '' && str_ends_with($file, '.css')) {
            array_unshift($css_list, $file);
        }

        return array_values(array_unique(array_filter($css_list, 'is_string')));
    }

    public static function register_editor_preview_styles(): void
    {
        $common_entry = self::get_entry(self::ENTRY_COMMON);
        if ($common_entry !== null) {
            self::register_entry_styles($common_entry, 'bm-theme-editor', ['wp-edit-blocks']);
        }

        $home_entry = self::get_entry(self::PAGE_ENTRIES['home']);
        if ($home_entry !== null) {
            self::register_entry_styles($home_entry, 'bm-theme-editor-home', ['bm-theme-editor']);
        }

        $hub_entry = self::get_entry(self::PAGE_ENTRIES['hub']);
        if ($hub_entry !== null) {
            self::register_entry_styles($hub_entry, 'bm-theme-editor-hub', ['bm-theme-editor']);
        }
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $deps
     */
    private static function enqueue_entry_script(array $entry, string $handle, array $deps, bool $in_footer): void
    {
        $script_relative = isset($entry['file']) && is_string($entry['file']) ? $entry['file'] : '';
        if ($script_relative === '' || !str_ends_with($script_relative, '.js')) {
            return;
        }

        $script_path = CONSTRUCTLY_THEME_PATH . '/assets/dist/' . $script_relative;
        if (is_readable($script_path) && filesize($script_path) > 32) {
            wp_enqueue_script(
                $handle,
                CONSTRUCTLY_THEME_URL . '/assets/dist/' . $script_relative,
                $deps,
                (string) filemtime($script_path),
                $in_footer
            );
            wp_script_add_data($handle, 'bm_module_script', true);
        }
    }

    public static function enqueue_block_editor_assets(): void
    {
        $common_entry = self::get_entry(self::ENTRY_COMMON);
        if ($common_entry !== null) {
            self::enqueue_entry_styles($common_entry, 'bm-theme-editor', ['wp-edit-blocks']);
        }

        $home_entry = self::get_entry(self::PAGE_ENTRIES['home']);
        if ($home_entry !== null) {
            self::enqueue_entry_styles($home_entry, 'bm-theme-editor-home', ['bm-theme-editor']);
        }

        $hub_entry = self::get_entry(self::PAGE_ENTRIES['hub']);
        if ($hub_entry !== null) {
            self::enqueue_entry_styles($hub_entry, 'bm-theme-editor-hub', ['bm-theme-editor']);
        }

        $entry = self::get_entry(self::ENTRY_EDITOR);
        if ($entry === null) {
            return;
        }

        $css_list = isset($entry['css']) && is_array($entry['css']) ? $entry['css'] : [];
        foreach ($css_list as $index => $relative) {
            if (!is_string($relative) || $relative === '') {
                continue;
            }
            $handle = $index === 0 ? 'bm-theme-editor' : 'bm-theme-editor-' . (string) $index;
            $abs_path = CONSTRUCTLY_THEME_PATH . '/assets/dist/' . $relative;
            wp_enqueue_style(
                $handle,
                CONSTRUCTLY_THEME_URL . '/assets/dist/' . $relative,
                ['wp-edit-blocks'],
                is_readable($abs_path) ? (string) filemtime($abs_path) : CONSTRUCTLY_THEME_VERSION
            );
        }
    }

    /**
     * @param list<string> $deps
     */
    public static function enqueue_script_entry(string $entry_key, string $handle, array $deps, bool $in_footer): void
    {
        $entry = self::get_entry($entry_key);
        if ($entry === null) {
            return;
        }

        self::enqueue_entry_script($entry, $handle, $deps, $in_footer);
    }

    /**
     * @return array{'src': non-falsy-string, 'ver': string}|null
     */
    public static function get_registered_script_bundle(string $entry_key): ?array
    {
        $entry = self::get_entry($entry_key);
        if ($entry === null) {
            return null;
        }

        $relative = isset($entry['file']) && is_string($entry['file']) ? $entry['file'] : '';
        if ($relative === '') {
            return null;
        }

        $abs_path = CONSTRUCTLY_THEME_PATH . '/assets/dist/' . $relative;
        if (!is_readable($abs_path)) {
            return null;
        }

        return [
            'src' => CONSTRUCTLY_THEME_URL . '/assets/dist/' . $relative,
            'ver' => (string) filemtime($abs_path),
        ];
    }

    /**
     * @return non-empty-string
     */
    public static function entry_key_bm_custom_select(): string
    {
        return self::ENTRY_COMMON;
    }

}
