<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom rewrite rules and URL filters for the "База знаний" section.
 *
 * ARCHITECTURE NOTE — why custom rules instead of category_base:
 *
 * Setting category_base = 'baza-znaniy' makes WordPress emit one greedy rule:
 *   ^baza-znaniy/(.+?)/?$  → index.php?category_name=$matches[1]
 * The (.+?) capture matches ANY depth, so a two-segment path like
 *   /baza-znaniy/fundament/moj-post/
 * is intercepted and resolved as a category_name request.  WordPress finds no
 * category named "fundament/moj-post", returns 404.
 *
 * The fix: leave category_base empty and register ONE-SEGMENT rules manually.
 *   ^baza-znaniy/([^/]+)/?$
 * The character class [^/]+ matches exactly one path token (no slash).  A
 * two-segment URL (/baza-znaniy/cat/post/) contains a slash in the second
 * position, so it does NOT match this rule.  WordPress continues down the rule
 * list and hits the standard per-post rule generated from the permalink
 * structure /baza-znaniy/%category%/%postname%/, which resolves the article.
 *
 * ARTICLES SLUG FLOW (confirmed):
 *   /baza-znaniy/fundament/moj-post/
 *   → does NOT match ^baza-znaniy/([^/]+)/?$  (contains interior slash)
 *   → matches WordPress post rule: baza-znaniy/(.+)/([^/]+)/?
 *     → index.php?category_name=$matches[1]&name=$matches[2]  → 200 OK
 *
 * CATEGORY ARCHIVE FLOW:
 *   /baza-znaniy/fundament/
 *   → matches ^baza-znaniy/([^/]+)/?$
 *   → index.php?category_name=fundament  → 200 OK
 */
final class Constructly_Articles_Rewrite
{
    /** Slugs of the knowledge-base categories whose links we rewrite. */
    private const KB_CATEGORY_SLUGS = ['fundament', 'poly', 'otdelka', 'steny'];

    /** URL prefix for the knowledge-base section (no leading/trailing slash). */
    private const KB_PREFIX = 'baza-znaniy';

    public static function init(): void
    {
        add_action('init', [self::class, 'register_rules']);
        add_filter('category_link', [self::class, 'filter_category_link'], 10, 2);
    }

    /**
     * Registers one-segment category archive rules under /baza-znaniy/.
     * These rules fire at priority 'top' so they run before the default
     * category rules, but their pattern [^/]+ prevents them from matching
     * two-segment article URLs.
     */
    public static function register_rules(): void
    {
        $prefix = self::KB_PREFIX;

        // Plain archive: /baza-znaniy/<cat>/
        add_rewrite_rule(
            '^' . $prefix . '/([^/]+)/?$',
            'index.php?category_name=$matches[1]',
            'top'
        );

        // Paginated archive: /baza-znaniy/<cat>/page/<n>/
        add_rewrite_rule(
            '^' . $prefix . '/([^/]+)/page/([0-9]+)/?$',
            'index.php?category_name=$matches[1]&paged=$matches[2]',
            'top'
        );
    }

    /**
     * Rewrites get_category_link() output for knowledge-base categories so that
     * breadcrumbs, menus, and canonical tags point to /baza-znaniy/<slug>/
     * instead of the default WordPress category URL.
     *
     * Only modifies links for the three known KB category slugs; all other
     * categories keep their unmodified URL.
     *
     * @param string $link    The default category permalink.
     * @param int    $term_id The category term ID.
     * @return string
     */
    public static function filter_category_link(string $link, int $term_id): string
    {
        $term = get_term($term_id, 'category');
        if (!($term instanceof WP_Term)) {
            return $link;
        }

        if (!in_array($term->slug, self::KB_CATEGORY_SLUGS, true)) {
            return $link;
        }

        return home_url('/' . self::KB_PREFIX . '/' . $term->slug . '/');
    }
}

Constructly_Articles_Rewrite::init();
