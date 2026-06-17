<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Migration_Helpers
{
    public static function block(string $block_name, array $attributes, string $inner_content = ''): string
    {
        return (string) get_comment_delimited_block_content($block_name, $attributes, $inner_content);
    }

    /**
     * @return array<string, string>
     */
    public static function resolve_links(): array
    {
        $overrides = apply_filters('constructly_homepage_link_map', []);

        if (is_array($overrides) && !empty($overrides)) {
            return array_merge(self::default_links(), array_filter($overrides, 'is_string'));
        }

        return self::default_links();
    }

    /**
     * @return array<string, string>
     */
    public static function default_links(): array
    {
        return [
            'foundation' => self::resolve_page_url(
                ['kalkulyatory/fundament', 'kalkulyator-fundamenta', 'fundament', 'foundation'],
                ['фундамент', 'foundation'],
                home_url('/kalkulyatory/fundament/')
            ),
            'screed' => self::resolve_page_url(
                ['kalkulyatory/styazhka', 'kalkulyator-styazhki', 'styazhka', 'screed'],
                ['стяжка', 'screed'],
                home_url('/kalkulyatory/styazhka/')
            ),
            'brick' => self::resolve_page_url(
                ['kalkulyatory/kirpich', 'kalkulyator-kirpicha', 'kirpich', 'brick'],
                ['кирпич', 'brick'],
                home_url('/kalkulyatory/kirpich/')
            ),
            'tile' => self::resolve_page_url(
                ['kalkulyatory/plitka', 'kalkulyator-plitki', 'plitka', 'tile'],
                ['плитка', 'tile'],
                home_url('/kalkulyatory/plitka/')
            ),
            'drywall' => self::resolve_page_url(
                ['kalkulyatory/gipsokarton', 'kalkulyator-gipsokartona', 'kalkulyator-gkl', 'gipsokarton', 'drywall'],
                ['гипсокартон', 'drywall'],
                home_url('/kalkulyatory/gipsokarton/')
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function default_foundation_hub_links(): array
    {
        return [
            'slab' => self::resolve_page_url(
                ['kalkulyatory/fundament/plitnyj', 'kalkulyator-plitnogo-fundamenta'],
                ['плитный'],
                home_url('/kalkulyatory/fundament/plitnyj/')
            ),
            'strip' => self::resolve_page_url(
                ['kalkulyatory/fundament/lentochnyj', 'kalkulyator-lentochnogo-fundamenta'],
                ['ленточн'],
                home_url('/kalkulyatory/fundament/lentochnyj/')
            ),
            'pile' => self::resolve_page_url(
                ['kalkulyatory/fundament/svajnyj', 'kalkulyator-svajnogo-fundamenta'],
                ['свайн'],
                home_url('/kalkulyatory/fundament/svajnyj/')
            ),
            'brick' => self::resolve_page_url(
                ['kalkulyatory/kirpich', 'kalkulyator-kirpicha'],
                ['кирпич'],
                home_url('/kalkulyatory/kirpich/')
            ),
            'screed' => self::resolve_page_url(
                ['kalkulyatory/styazhka', 'kalkulyator-styazhki'],
                ['стяжк'],
                home_url('/kalkulyatory/styazhka/')
            ),
            'tile' => self::resolve_page_url(
                ['kalkulyatory/plitka', 'kalkulyator-plitki'],
                ['плитк'],
                home_url('/kalkulyatory/plitka/')
            ),
        ];
    }

    public static function resolve_page_url(array $paths, array $search_terms, string $fallback): string
    {
        foreach ($paths as $path) {
            $page = get_page_by_path($path);

            if ($page instanceof WP_Post) {
                return get_permalink($page);
            }
        }

        foreach ($search_terms as $term) {
            $pages = get_posts([
                'post_type' => 'page',
                'post_status' => ['publish', 'draft', 'private'],
                's' => $term,
                'posts_per_page' => 1,
            ]);

            if (!empty($pages) && $pages[0] instanceof WP_Post) {
                return get_permalink($pages[0]);
            }
        }

        if (str_starts_with($fallback, '#')) {
            return home_url('/' . $fallback);
        }

        return $fallback;
    }

    /**
     * Basenames for calculator preview files in homepage card order.
     *
     * @return list<string>
     */
    public static function homepage_calculator_preview_basenames(): array
    {
        return [
            'preview-foundation',
            'preview-screed',
            'preview-brick',
            'preview-tile',
            'preview-drywall',
        ];
    }

    /**
     * Uses an existing attachment if _wp_attached_file basename matches, otherwise sideloads from the child theme.
     */
    public static function resolve_homepage_calculator_preview_id(string $basename): int
    {
        $basename = sanitize_file_name($basename);
        if ($basename === '') {
            return 0;
        }

        $dir = trailingslashit(get_stylesheet_directory()) . 'assets/frontend/images/calculator-previews';
        $extensions = ['webp', 'png', 'jpg', 'jpeg'];
        $source_path = '';

        foreach ($extensions as $ext) {
            $candidate = $dir . '/' . $basename . '.' . $ext;
            if (is_readable($candidate)) {
                $source_path = $candidate;
                break;
            }
        }

        if ($source_path === '') {
            return 0;
        }

        $filename = basename($source_path);
        $existing = self::find_attachment_by_upload_basename($filename);

        if ($existing > 0) {
            return $existing;
        }

        return self::sideload_theme_file_as_attachment($source_path, $filename);
    }

    private static function find_attachment_by_upload_basename(string $filename): int
    {
        global $wpdb;

        $filename = basename($filename);
        if ($filename === '') {
            return 0;
        }

        $like = '%/' . $wpdb->esc_like($filename);
        $id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
             AND (meta_value = %s OR meta_value LIKE %s)
             LIMIT 1",
            $filename,
            $like
        ));

        return $id > 0 ? $id : 0;
    }

    private static function sideload_theme_file_as_attachment(string $abs_path, string $filename): int
    {
        if (!is_readable($abs_path)) {
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = wp_tempnam($filename);
        if ($tmp === false || !@copy($abs_path, $tmp)) {
            if (is_string($tmp) && file_exists($tmp)) {
                unlink($tmp);
            }

            return 0;
        }

        $file_array = [
            'name' => $filename,
            'tmp_name' => $tmp,
        ];

        $previous_user = get_current_user_id();
        if ($previous_user === 0 && defined('WP_CLI') && WP_CLI) {
            $admin_ids = get_users([
                'role' => 'administrator',
                'number' => 1,
                'fields' => 'ids',
            ]);
            if (!empty($admin_ids[0])) {
                wp_set_current_user((int) $admin_ids[0]);
            }
        }

        $id = media_handle_sideload($file_array, 0);

        wp_set_current_user($previous_user);

        if (is_string($tmp) && file_exists($tmp)) {
            unlink($tmp);
        }

        if (is_wp_error($id)) {
            return 0;
        }

        return (int) $id;
    }
}
