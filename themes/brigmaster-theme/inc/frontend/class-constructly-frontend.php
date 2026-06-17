<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Frontend
{
    public static function init(): void
    {
        add_action('init', [self::class, 'register_assets']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_global_assets'], 20);
        add_action('after_setup_theme', [self::class, 'register_theme_supports']);
        add_action('pre_get_posts', [self::class, 'apply_archive_toolbar_query']);
        add_action('template_redirect', [self::class, 'maybe_record_post_view']);
        add_action('wp_ajax_bm_article_feedback', [self::class, 'handle_article_feedback']);
        add_action('wp_ajax_nopriv_bm_article_feedback', [self::class, 'handle_article_feedback']);
        add_filter('manage_post_posts_columns', [self::class, 'add_feedback_column']);
        add_action('manage_post_posts_custom_column', [self::class, 'render_feedback_column'], 10, 2);
    }

    /**
     * Adds an "Отзывы" column to the Posts list table so the helpful/not-helpful
     * vote counts (stored as post meta) are visible in the admin.
     *
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public static function add_feedback_column(array $columns): array
    {
        $columns['bm_feedback'] = __('Отзывы', 'brigmaster-theme');

        return $columns;
    }

    public static function render_feedback_column(string $column, int $post_id): void
    {
        if ($column !== 'bm_feedback') {
            return;
        }

        $yes = (int) get_post_meta($post_id, '_bm_feedback_yes', true);
        $no = (int) get_post_meta($post_id, '_bm_feedback_no', true);

        echo esc_html(sprintf('👍 %d / 👎 %d', $yes, $no));
    }

    /**
     * Records a post view before output is sent, so the de-dupe cookie can be set.
     */
    public static function maybe_record_post_view(): void
    {
        if (is_singular('post')) {
            bm_record_post_view((int) get_queried_object_id());
        }
    }

    /**
     * Records or changes an article-feedback vote (helpful / not helpful) as post meta
     * counters. One vote per visitor (the client enforces this via a cookie, like the
     * view counter); when a visitor switches their choice, the previous vote — sent as
     * `previous` — is decremented so the net count stays one vote per visitor.
     */
    public static function handle_article_feedback(): void
    {
        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $vote = isset($_POST['vote']) ? sanitize_key(wp_unslash((string) $_POST['vote'])) : '';
        $previous = isset($_POST['previous']) ? sanitize_key(wp_unslash((string) $_POST['previous'])) : '';
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash((string) $_POST['nonce'])) : '';

        if ($post_id <= 0 || !in_array($vote, ['yes', 'no'], true) || get_post_type($post_id) !== 'post') {
            wp_send_json_error(['message' => 'invalid'], 400);
        }

        if (!wp_verify_nonce($nonce, 'bm_article_feedback_' . $post_id)) {
            wp_send_json_error(['message' => 'bad_nonce'], 403);
        }

        $meta = static fn (string $v): string => $v === 'yes' ? '_bm_feedback_yes' : '_bm_feedback_no';

        // Re-submitting the same vote is a no-op for the totals.
        if ($previous === $vote) {
            wp_send_json_success(self::feedback_counts($post_id));
        }

        // Switching choice: drop the previous vote first.
        if (in_array($previous, ['yes', 'no'], true)) {
            $prev_key = $meta($previous);
            update_post_meta($post_id, $prev_key, max(0, (int) get_post_meta($post_id, $prev_key, true) - 1));
        }

        $key = $meta($vote);
        update_post_meta($post_id, $key, max(0, (int) get_post_meta($post_id, $key, true)) + 1);

        wp_send_json_success(self::feedback_counts($post_id));
    }

    /**
     * @return array{yes:int, no:int}
     */
    private static function feedback_counts(int $post_id): array
    {
        return [
            'yes' => (int) get_post_meta($post_id, '_bm_feedback_yes', true),
            'no' => (int) get_post_meta($post_id, '_bm_feedback_no', true),
        ];
    }

    /**
     * Applies the knowledge-base toolbar controls (topic filter, sort) to the main
     * posts query. Search is handled natively via the `s` query var. Scoped to the
     * frontend main query on the posts index / blog home / category archive.
     */
    public static function apply_archive_toolbar_query(WP_Query $query): void
    {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if (!($query->is_home() || $query->is_category() || $query->is_search())) {
            return;
        }

        $topic = isset($_GET['topic']) ? sanitize_title(wp_unslash((string) $_GET['topic'])) : '';
        if ($topic !== '' && !$query->is_category()) {
            $query->set('category_name', $topic);
        }

        $sort = isset($_GET['sort']) ? sanitize_key(wp_unslash((string) $_GET['sort'])) : '';
        switch ($sort) {
            case 'oldest':
                $query->set('orderby', 'date');
                $query->set('order', 'ASC');
                break;
            case 'popular':
                $query->set('meta_key', BM_POST_VIEWS_META);
                $query->set('orderby', ['meta_value_num' => 'DESC', 'date' => 'DESC']);
                break;
            case 'newest':
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
        }
    }

    public static function register_theme_supports(): void
    {
        add_theme_support('editor-styles');
        add_theme_support('wp-block-styles');
        add_theme_support('responsive-embeds');
    }

    public static function register_assets(): void
    {
        wp_register_style(
            'bm-base',
            false,
            [],
            CONSTRUCTLY_THEME_VERSION
        );

        wp_register_style(
            'bm-core-estimator',
            false,
            ['bm-base'],
            CONSTRUCTLY_THEME_VERSION
        );

        wp_register_style(
            'bm-core-hub',
            false,
            ['bm-base'],
            CONSTRUCTLY_THEME_VERSION
        );
    }

    public static function enqueue_global_assets(): void
    {
        wp_enqueue_style('bm-base');
        Constructly_Assets::enqueue_main_bundle();

        if (is_singular()) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $content = (string) $post->post_content;

                if (self::post_content_has_estimator_shortcode($content)) {
                    wp_enqueue_style('bm-core-estimator');
                }

                if (self::post_content_needs_foundation_hub_styles($content)) {
                    wp_enqueue_style('bm-core-hub');
                }
            }
        }
    }

    private static function post_content_needs_foundation_hub_styles(string $content): bool
    {
        if (has_shortcode($content, 'brigmaster_foundation_hub')) {
            return true;
        }

        $blocks = [
            'constructly/foundation-hub-hero',
            'constructly/foundation-hub-type-cards',
        ];

        foreach ($blocks as $block_name) {
            if (has_block($block_name, $content)) {
                return true;
            }
        }

        return false;
    }

    private static function post_content_has_estimator_shortcode(string $content): bool
    {
        foreach (bm_estimator_shortcode_tags() as $tag) {
            if (has_shortcode($content, $tag)) {
                return true;
            }
        }

        return false;
    }
}
