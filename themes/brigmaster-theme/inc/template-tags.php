<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Breadcrumbs are owned by Rank Math. If the plugin is unavailable, render nothing.
 */
function bm_breadcrumbs(?WP_Post $post = null): void
{
    if (is_front_page()) {
        return;
    }

    if (function_exists('rank_math_the_breadcrumbs')) {
        rank_math_the_breadcrumbs();

        return;
    }
}

/**
 * Approximate reading time for archive cards (news list), in full minutes (min 1).
 */
function bm_reading_time_minutes(?int $post_id = null): int
{
    $p = get_post($post_id);
    if (!$p instanceof WP_Post) {
        return 1;
    }

    $plain = wp_strip_all_tags((string) $p->post_content);
    if ($plain === '') {
        return 1;
    }

    $tokens = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
    $n = is_array($tokens) ? count($tokens) : 0;

    return max(1, (int) ceil($n / 200));
}

/**
 * Reading time as a display label, e.g. "12 мин".
 */
function bm_reading_time_label(?int $post_id = null): string
{
    return sprintf('%d мин', bm_reading_time_minutes($post_id));
}

const BM_POST_VIEWS_META = '_bm_post_views';

/**
 * Returns the stored view count for a post.
 */
function bm_get_post_views(?int $post_id = null): int
{
    $post_id = $post_id ?? (int) get_the_ID();

    return max(0, (int) get_post_meta($post_id, BM_POST_VIEWS_META, true));
}

/**
 * Increments the view counter for a single post. De-duplicates per visitor with a
 * cookie (so reloads by the same user don't inflate the count) and once per request.
 * This is a lightweight popularity signal, not analytics — bots and cookie-less
 * clients are not specially handled.
 *
 * Call before output is sent (e.g. on template_redirect) so the cookie can be set.
 */
function bm_record_post_view(?int $post_id = null): void
{
    $post_id = $post_id ?? (int) get_the_ID();
    if ($post_id <= 0 || is_admin()) {
        return;
    }

    static $recorded = [];
    if (isset($recorded[$post_id])) {
        return;
    }
    $recorded[$post_id] = true;

    $cookie = isset($_COOKIE['bm_viewed']) ? (string) wp_unslash($_COOKIE['bm_viewed']) : '';
    $seen = array_values(array_filter(array_map('absint', explode(',', $cookie))));
    if (in_array($post_id, $seen, true)) {
        return;
    }

    update_post_meta($post_id, BM_POST_VIEWS_META, bm_get_post_views($post_id) + 1);

    $seen[] = $post_id;
    $seen = array_slice(array_unique($seen), -200); // cap cookie growth
    if (!headers_sent()) {
        setcookie('bm_viewed', implode(',', $seen), [
            'expires' => time() + 30 * DAY_IN_SECONDS,
            'path' => '/',
            'samesite' => 'Lax',
        ]);
        $_COOKIE['bm_viewed'] = implode(',', $seen);
    }
}

/**
 * Russian view-count label, e.g. "5 432 просмотра".
 */
function bm_post_views_label(?int $post_id = null): string
{
    $n = bm_get_post_views($post_id);
    $mod10 = $n % 10;
    $mod100 = $n % 100;

    if ($mod10 === 1 && $mod100 !== 11) {
        $word = 'просмотр';
    } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
        $word = 'просмотра';
    } else {
        $word = 'просмотров';
    }

    return number_format_i18n($n) . ' ' . $word;
}

/**
 * Most-viewed published posts (falls back to most recent when views are equal/absent).
 *
 * @return list<WP_Post>
 */
function bm_popular_posts(int $limit = 5, int $exclude_id = 0): array
{
    $query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => $exclude_id > 0 ? [$exclude_id] : [],
        'meta_key' => BM_POST_VIEWS_META,
        'orderby' => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
        'ignore_sticky_posts' => true,
        // Posts without the meta should still appear (ordered last by date).
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => BM_POST_VIEWS_META,
                'compare' => 'EXISTS',
            ],
            [
                'key' => BM_POST_VIEWS_META,
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);

    return $query->posts;
}

/**
 * Primary category of a post: the first assigned category that is NOT the site
 * default ("Без рубрики" / Uncategorized). Returns null when a post only has the
 * default category, so the default is never surfaced in the UI.
 */
function bm_primary_category(?int $post_id = null): ?WP_Term
{
    $cats = get_the_category($post_id ?? (int) get_the_ID());
    if (empty($cats)) {
        return null;
    }

    $default = (int) get_option('default_category');
    foreach ($cats as $cat) {
        if ($cat instanceof WP_Term && (int) $cat->term_id !== $default) {
            return $cat;
        }
    }

    return null;
}

/**
 * Renders archive pagination as bm-pagination markup, preserving the current
 * query string (search, topic, sort) on each page link.
 */
function bm_archive_pagination(): void
{
    global $wp_query;

    $total = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 1;
    if ($total < 2) {
        return;
    }

    $current = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $mid = 1;

    $link = static function (int $n, string $label, bool $active = false): string {
        $aria = $active ? ' aria-current="page"' : '';
        $class = 'bm-pagination__link' . ($active ? ' is-active' : '');

        return sprintf(
            '<li><a class="%s" href="%s"%s>%s</a></li>',
            esc_attr($class),
            esc_url(get_pagenum_link($n)),
            $aria,
            $label
        );
    };
    $ellipsis = '<li><span class="bm-pagination__link bm-pagination__link--ellipsis">…</span></li>';

    $items = '';
    if ($current > 1) {
        $prev = '<svg class="bm-icon bm-pagination__icon" aria-hidden="true"><use href="#bm-icon-arrow-left"></use></svg>';
        $items .= sprintf(
            '<li><a class="bm-pagination__link" href="%s" aria-label="%s">%s</a></li>',
            esc_url(get_pagenum_link($current - 1)),
            esc_attr__('Предыдущая страница', 'brigmaster-theme'),
            $prev
        );
    }

    for ($i = 1; $i <= $total; $i++) {
        $in_window = $i === 1 || $i === $total || abs($i - $current) <= $mid;
        if ($in_window) {
            $items .= $link($i, (string) $i, $i === $current);
            continue;
        }
        // Collapse runs into a single ellipsis.
        if (($i === 2 && $current - $mid > 2) || ($i === $total - 1 && $current + $mid < $total - 1)) {
            $items .= $ellipsis;
        }
    }

    if ($current < $total) {
        $next = '<svg class="bm-icon bm-pagination__icon" aria-hidden="true"><use href="#bm-icon-arrow-right"></use></svg>';
        $items .= sprintf(
            '<li><a class="bm-pagination__link" href="%s" aria-label="%s">%s</a></li>',
            esc_url(get_pagenum_link($current + 1)),
            esc_attr__('Следующая страница', 'brigmaster-theme'),
            $next
        );
    }

    printf(
        '<nav class="bm-pagination" aria-label="%s"><ul class="bm-pagination__list">%s</ul></nav>',
        esc_attr__('Страницы списка статей', 'brigmaster-theme'),
        $items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url/esc_attr above.
    );
}

/**
 * Russian copy for "found N posts" (list header), matching news archive UX.
 */
function bm_archive_found_posts_label(int $count): string
{
    $n = absint($count);
    $mod10 = $n % 10;
    $mod100 = $n % 100;

    if ($mod10 === 1 && $mod100 !== 11) {
        return sprintf(
            /* translators: %d: post count */
            __('Найдена %d статья', 'brigmaster-theme'),
            $n
        );
    }

    if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
        return sprintf(
            /* translators: %d: post count */
            __('Найдено %d статьи', 'brigmaster-theme'),
            $n
        );
    }

    return sprintf(
        /* translators: %d: post count */
        __('Найдено %d статей', 'brigmaster-theme'),
        $n
    );
}
