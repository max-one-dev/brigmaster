<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seeds a single knowledge-base article idempotently.
 *
 * Idempotency strategy:
 *   1. Look up post by meta _bm_article_key = data['key'] (primary, stable).
 *   2. Fall back to post_name = data['slug'] if no meta match.
 *   3. Create post when neither matches.
 * On every run: title, slug, excerpt, content, category, and images are
 * upserted. Publish date is set ONCE on creation and never changed on re-run.
 * View count is written ONCE (skipped when the meta already exists).
 */
final class Constructly_Article_Seeder
{
    private const ARTICLE_KEY_META  = '_bm_article_key';
    private const ARTICLE_HASH_META = '_bm_article_hash';
    private const HERO_IMAGE_META   = '_bm_hero_image_id';
    private const IMAGES_DIR        = 'assets/src/images/posts/';
    private const IMAGE_EXTENSIONS  = ['jpg', 'jpeg', 'webp', 'png'];

    /**
     * Upserts one article. Returns an array with the post ID and status, or
     * ['id' => 0, 'status' => 'failed'] on error.
     *
     * Status values:
     *   'created' — new post was inserted.
     *   'updated' — existing post was updated (hash changed).
     *   'skipped' — existing post is up-to-date (hash unchanged).
     *   'failed'  — wp_insert_post / wp_update_post returned WP_Error.
     *
     * @param array<string, mixed>  $data             Validated ArticleData from the registry.
     * @param array<string, int>    $cat_ids_by_slug  slug => term_id map from ensure_categories().
     * @return array{id:int, status:string}
     */
    public static function seed_article(array $data, array $cat_ids_by_slug): array
    {
        $post_id = self::find_post_id((string) $data['key'], (string) $data['slug']);
        $hash    = md5(wp_json_encode($data));

        // ------------------------------------------------------------------
        // Hash check: skip unchanged posts entirely.
        // ------------------------------------------------------------------
        if ($post_id > 0) {
            $saved_hash = (string) get_post_meta($post_id, self::ARTICLE_HASH_META, true);
            if ($saved_hash === $hash) {
                return ['id' => $post_id, 'status' => 'skipped'];
            }
        }

        $is_new = ($post_id === 0);

        // ------------------------------------------------------------------
        // Resolve images before building content (placeholders need IDs/URLs).
        // ------------------------------------------------------------------
        $images        = is_array($data['images']) ? $data['images'] : [];
        $hero_id       = 0;
        $cover_id      = 0;
        $body_image_map = []; // basename => ['id' => int, 'url' => string, 'alt' => string]

        if (isset($images['hero']) && is_array($images['hero'])) {
            $hero_id = self::resolve_image((string) ($images['hero']['file'] ?? ''));
            if ($hero_id > 0 && !empty($images['hero']['alt'])) {
                update_post_meta($hero_id, '_wp_attachment_image_alt', sanitize_text_field((string) $images['hero']['alt']));
            }
        }

        if (isset($images['cover']) && is_array($images['cover'])) {
            $cover_id = self::resolve_image((string) ($images['cover']['file'] ?? ''));
            if ($cover_id > 0 && !empty($images['cover']['alt'])) {
                update_post_meta($cover_id, '_wp_attachment_image_alt', sanitize_text_field((string) $images['cover']['alt']));
            }
        }

        if (isset($images['body']) && is_array($images['body'])) {
            foreach ($images['body'] as $body_img) {
                if (!is_array($body_img) || empty($body_img['file'])) {
                    continue;
                }
                $basename  = (string) $body_img['file'];
                $attach_id = self::resolve_image($basename);
                if ($attach_id > 0) {
                    $alt = sanitize_text_field((string) ($body_img['alt'] ?? ''));
                    update_post_meta($attach_id, '_wp_attachment_image_alt', $alt);
                    $body_image_map[$basename] = [
                        'id'  => $attach_id,
                        'url' => (string) wp_get_attachment_url($attach_id),
                        'alt' => $alt,
                    ];
                }
            }
        }

        // Resolve {{img:basename}} placeholders in content.
        $content = self::resolve_placeholders((string) $data['content'], $body_image_map);

        // ------------------------------------------------------------------
        // Build the post array.
        // ------------------------------------------------------------------
        $date_local = sanitize_text_field((string) $data['date']);
        // Accept Y-m-d; append a fixed time so it sorts predictably.
        if (strlen($date_local) === 10) {
            $date_local .= ' 10:00:00';
        }
        $date_gmt = get_gmt_from_date($date_local);

        $cat_slug = (string) $data['category'];
        $cat_id   = $cat_ids_by_slug[$cat_slug] ?? 0;

        $post_args = [
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_title'   => wp_kses_post((string) $data['title']),
            'post_name'    => sanitize_title((string) $data['slug']),
            'post_excerpt' => wp_kses_post((string) $data['excerpt']),
            'post_content' => wp_slash($content),
        ];

        if ($is_new) {
            $post_args['post_date']     = $date_local;
            $post_args['post_date_gmt'] = $date_gmt;
            $result = wp_insert_post($post_args, true);
        } else {
            $post_args['ID'] = $post_id;

            // Sync publish date if the data-file value differs from what is stored.
            $existing_post = get_post($post_id);
            if ($existing_post instanceof WP_Post) {
                $stored_date = $existing_post->post_date; // 'Y-m-d H:i:s'
                // Normalise to Y-m-d for comparison (ignore stored time component).
                $stored_ymd = substr($stored_date, 0, 10);
                $target_ymd = substr($date_local, 0, 10);
                if ($stored_ymd !== $target_ymd) {
                    $post_args['post_date']     = $date_local;
                    $post_args['post_date_gmt'] = $date_gmt;
                }
            }

            $result = wp_update_post($post_args, true);
        }

        if (is_wp_error($result)) {
            return ['id' => 0, 'status' => 'failed'];
        }

        $post_id = (int) $result;
        $status  = $is_new ? 'created' : 'updated';

        // ------------------------------------------------------------------
        // Category assignment.
        // ------------------------------------------------------------------
        if ($cat_id > 0) {
            wp_set_post_categories($post_id, [$cat_id], false);
        }

        // ------------------------------------------------------------------
        // Meta: article key (idempotency anchor).
        // ------------------------------------------------------------------
        update_post_meta($post_id, self::ARTICLE_KEY_META, sanitize_key((string) $data['key']));

        // ------------------------------------------------------------------
        // Meta: hash — always refresh after a successful create/update.
        // ------------------------------------------------------------------
        update_post_meta($post_id, self::ARTICLE_HASH_META, $hash);

        // ------------------------------------------------------------------
        // Meta: views — written ONCE on creation, never overwritten.
        // ------------------------------------------------------------------
        $existing_views = get_post_meta($post_id, BM_POST_VIEWS_META, true);
        if ($existing_views === '' || $existing_views === false) {
            $views_min = max(0, (int) ($data['views_min'] ?? 0));
            $views_max = max($views_min, (int) ($data['views_max'] ?? $views_min));
            update_post_meta($post_id, BM_POST_VIEWS_META, wp_rand($views_min, $views_max));
        }

        // ------------------------------------------------------------------
        // Images: featured image (cover) and hero meta.
        // ------------------------------------------------------------------
        if ($cover_id > 0) {
            set_post_thumbnail($post_id, $cover_id);
        }

        if ($hero_id > 0) {
            update_post_meta($post_id, self::HERO_IMAGE_META, $hero_id);
        }

        // ------------------------------------------------------------------
        // Rank Math SEO: rank_math_title / rank_math_description.
        // Written only when the data-file supplies a non-empty value.
        // Re-seeding with an updated value in the data file WILL overwrite
        // what is saved in the database (including manual admin edits).
        // To protect a manual admin edit, remove or leave blank the key in
        // the data file — the seeder will then leave the meta untouched.
        // ------------------------------------------------------------------
        $rm_title = isset($data['meta_title']) ? sanitize_text_field((string) $data['meta_title']) : '';
        if ($rm_title !== '') {
            update_post_meta($post_id, 'rank_math_title', $rm_title);
        }

        $rm_desc = isset($data['meta_description']) ? sanitize_text_field((string) $data['meta_description']) : '';
        if ($rm_desc !== '') {
            update_post_meta($post_id, 'rank_math_description', $rm_desc);
        }

        return ['id' => $post_id, 'status' => $status];
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Finds an existing post ID by article key meta, then by slug.
     */
    private static function find_post_id(string $key, string $slug): int
    {
        // Primary: stable key meta lookup.
        $by_key = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'   => self::ARTICLE_KEY_META,
                    'value' => sanitize_key($key),
                ],
            ],
        ]);

        if (!empty($by_key[0])) {
            return (int) $by_key[0];
        }

        // Fallback: match by slug.
        $by_slug = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'name'           => sanitize_title($slug),
        ]);

        if (!empty($by_slug[0])) {
            return (int) $by_slug[0];
        }

        return 0;
    }

    /**
     * Sideloads or reuses an attachment for images/posts/<basename>.jpg.
     * Delegates to Constructly_Migration_Helpers::sideload_theme_image().
     */
    private static function resolve_image(string $basename): int
    {
        $basename = sanitize_file_name($basename);
        if ($basename === '') {
            return 0;
        }

        foreach (self::IMAGE_EXTENSIONS as $ext) {
            $relative = self::IMAGES_DIR . $basename . '.' . $ext;
            $id       = Constructly_Migration_Helpers::sideload_theme_image($relative);
            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }

    /**
     * Replaces {{img:basename}} placeholders with a wp:image Gutenberg block.
     * Placeholders with no matching attachment are left as an empty string
     * so the post does not contain broken markup.
     *
     * @param array<string, array{id:int, url:string, alt:string}> $map
     */
    private static function resolve_placeholders(string $content, array $map): string
    {
        if ($map === [] || strpos($content, '{{img:') === false) {
            return $content;
        }

        return preg_replace_callback(
            '/\{\{img:([^}]+)\}\}/',
            static function (array $matches) use ($map): string {
                $basename = trim($matches[1]);
                if (!isset($map[$basename])) {
                    return '';
                }

                $img = $map[$basename];
                $id  = $img['id'];
                $alt = esc_attr($img['alt']);

                // Prefer the 'large' intermediate size for body images; fall back to full URL.
                $large_url = wp_get_attachment_image_url($id, 'large');
                $url = esc_url($large_url ?: $img['url']);

                // Inject width/height to prevent CLS.
                $src_data   = wp_get_attachment_image_src($id, 'large');
                $size_attrs = '';
                if (is_array($src_data) && isset($src_data[1], $src_data[2])) {
                    $size_attrs = sprintf(' width="%d" height="%d"', (int) $src_data[1], (int) $src_data[2]);
                }

                $inner = sprintf(
                    '<figure class="wp-block-image size-large aligncenter">' .
                    '<img src="%s" alt="%s" class="wp-image-%d aligncenter"%s/>' .
                    '</figure>',
                    $url,
                    $alt,
                    $id,
                    $size_attrs
                );

                return sprintf(
                    '<!-- wp:image {"id":%d,"sizeSlug":"large","linkDestination":"none","align":"center"} -->%s<!-- /wp:image -->',
                    $id,
                    $inner
                );
            },
            $content
        ) ?? $content;
    }
}
