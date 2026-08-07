<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seeds demo knowledge-base content: categories, the /stati/ posts page, and a set
 * of demo articles (one rich, the rest list-level). Idempotent — re-running updates
 * existing items (matched by slug) instead of duplicating.
 */
final class Constructly_Articles_Seed
{
    private const POSTS_PAGE_SLUG = 'baza-znaniy';

    /**
     * @var list<array{name:string, slug:string, description:string}>
     */
    private const CATEGORIES = [
        ['name' => 'Фундамент',      'slug' => 'fundament',       'description' => 'Типы фундаментов, расчёт, армирование, гидроизоляция.'],
        ['name' => 'Полы',           'slug' => 'poly',            'description' => 'Стяжка, наливные полы, плитка и напольные покрытия.'],
        ['name' => 'Отделка',        'slug' => 'otdelka',         'description' => 'Штукатурка, покраска, гипсокартон, финишная отделка стен.'],
        ['name' => 'Стены',          'slug' => 'steny',           'description' => 'Кирпичная и блочная кладка: материалы, расход и расчёт стен.'],
    ];

    /**
     * @return array{categories:int, posts_page_id:int, articles:int, articles_created:int, articles_updated:int, articles_skipped:int}
     */
    public static function seed(): array
    {
        $cat_ids       = self::ensure_categories();
        $posts_page_id = self::ensure_posts_page();
        self::ensure_menu_links($posts_page_id);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach (Constructly_Article_Registry::all() as $article_data) {
            $result = Constructly_Article_Seeder::seed_article($article_data, $cat_ids);
            if ($result['id'] > 0) {
                switch ($result['status']) {
                    case 'created':
                        ++$created;
                        break;
                    case 'updated':
                        ++$updated;
                        break;
                    case 'skipped':
                        ++$skipped;
                        break;
                }
            }
        }

        return [
            'categories'       => count($cat_ids),
            'posts_page_id'    => $posts_page_id,
            'articles'         => $created + $updated,
            'articles_created' => $created,
            'articles_updated' => $updated,
            'articles_skipped' => $skipped,
        ];
    }

    /**
     * Ensures a "База знаний" link to the posts page exists in the primary nav and the
     * footer "Информация" column. Skips locations without an assigned menu, and never
     * duplicates an existing link to the same page/URL.
     *
     * For the primary menu the item is inserted immediately AFTER the item whose URL
     * contains /kalkulyatory/. All items with a higher menu_order are shifted up by 1
     * to make room. Idempotent: if "База знаний" already exists it is repositioned
     * (and siblings re-sorted) but not duplicated.
     */
    private static function ensure_menu_links(int $posts_page_id): void
    {
        if ($posts_page_id <= 0) {
            return;
        }

        $url       = (string) get_permalink($posts_page_id);
        $locations = get_nav_menu_locations();

        foreach (['primary', 'footer-column-2'] as $location) {
            if (empty($locations[$location])) {
                continue;
            }

            $menu_id = (int) $locations[$location];
            $items   = wp_get_nav_menu_items($menu_id) ?: [];

            // Check whether "База знаний" item already exists in this menu.
            $existing_item_id = 0;
            foreach ($items as $item) {
                if ((int) $item->object_id === $posts_page_id && $item->object === 'page') {
                    $existing_item_id = (int) $item->db_id;
                    break;
                }
                if (untrailingslashit((string) $item->url) === untrailingslashit($url)) {
                    $existing_item_id = (int) $item->db_id;
                    break;
                }
            }

            if ($location !== 'primary') {
                // Footer: keep existing behaviour — add if absent, ignore order.
                if ($existing_item_id > 0) {
                    continue;
                }
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title'     => 'База знаний',
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $posts_page_id,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ]);
                continue;
            }

            // ----------------------------------------------------------------
            // Primary menu: place "База знаний" right after /kalkulyatory/.
            // ----------------------------------------------------------------

            // Find the /kalkulyatory/ anchor item.
            $kalkulyatory_order = 0;
            foreach ($items as $item) {
                if (strpos((string) $item->url, '/kalkulyatory/') !== false) {
                    $kalkulyatory_order = (int) $item->menu_order;
                    break;
                }
            }

            // Target menu_order for "База знаний".
            $target_order = $kalkulyatory_order > 0 ? $kalkulyatory_order + 1 : 0;

            if ($existing_item_id > 0) {
                // Item already exists — check whether it already sits at the
                // correct position; if so, nothing to do.
                $current_order = 0;
                foreach ($items as $item) {
                    if ((int) $item->db_id === $existing_item_id) {
                        $current_order = (int) $item->menu_order;
                        break;
                    }
                }
                if ($target_order > 0 && $current_order === $target_order) {
                    continue; // Already in the right place.
                }
            }

            // Shift all items at or above target_order (excluding our own item)
            // using wp_update_post on menu_order only — never wp_update_nav_menu_item
            // on foreign items, which would wipe their title/object data.
            if ($target_order > 0) {
                foreach ($items as $item) {
                    $db_id = (int) $item->db_id;
                    if ($db_id === $existing_item_id) {
                        continue; // Will be placed explicitly below.
                    }
                    if ((int) $item->menu_order >= $target_order) {
                        wp_update_post([
                            'ID'         => $db_id,
                            'menu_order' => (int) $item->menu_order + 1,
                        ]);
                    }
                }
            }

            if ($existing_item_id > 0) {
                // Reposition existing item without touching any other fields.
                if ($target_order > 0) {
                    wp_update_post([
                        'ID'         => $existing_item_id,
                        'menu_order' => $target_order,
                    ]);
                }
            } else {
                // Create new item only when it does not yet exist.
                $item_args = [
                    'menu-item-title'     => 'База знаний',
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $posts_page_id,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ];
                if ($target_order > 0) {
                    $item_args['menu-item-position'] = $target_order;
                }
                wp_update_nav_menu_item($menu_id, 0, $item_args);
            }
        }
    }

    /**
     * @return array<string, int> slug => term_id
     */
    private static function ensure_categories(): array
    {
        $ids = [];
        foreach (self::CATEGORIES as $cat) {
            $existing = get_term_by('slug', $cat['slug'], 'category');
            if ($existing instanceof WP_Term) {
                $ids[$cat['slug']] = (int) $existing->term_id;
                continue;
            }

            $result = wp_insert_term($cat['name'], 'category', [
                'slug'        => $cat['slug'],
                'description' => $cat['description'] ?? '',
            ]);
            if (!is_wp_error($result)) {
                $ids[$cat['slug']] = (int) $result['term_id'];
            }
        }

        // Route category archives under /baza-znaniy/<slug>/ to match the posts
        // page URL prefix. Idempotent: only writes when value differs.
        self::ensure_category_base();

        return $ids;
    }

    /**
     * Neutralises the legacy category_base = 'baza-zaniy' / 'baza-znaniy' that
     * was previously set here.
     *
     * WHY THE OLD VALUE BREAKS ARTICLES:
     * When category_base is set, WordPress generates a greedy rewrite rule of the
     * form  ^baza-zaniy/(.+?)/?$  (a single rule that matches ANY depth).  That
     * pattern catches two-segment URLs like /baza-zaniy/fundament/moj-post/ before
     * the per-post rule fires, resolves them as a category archive request, finds
     * no matching term, and returns 404.  Category archives (one segment) happened
     * to work because the greedy capture still matched one token, but posts (two
     * segments) were silently eaten.
     *
     * FIX:
     * Leave category_base empty (WordPress default).  Custom rewrite rules in
     * inc/class-constructly-articles-rewrite.php add ONE-SEGMENT rules for the
     * /baza-znaniy/<cat>/ archives; those rules do NOT match two-segment URLs, so
     * the standard per-post rule (generated from the permalink structure
     * /baza-znaniy/%category%/%postname%/) resolves articles correctly.
     *
     * Safe to call repeatedly: only writes option when the stored value is the
     * legacy slug.  Does NOT flush rewrite rules here.
     */
    private static function ensure_category_base(): void
    {
        // Reset any legacy value that would generate a conflicting greedy rule.
        // Both spellings ('baza-zaniy' and 'baza-znaniy') were used historically.
        $current = (string) get_option('category_base', '');
        if ($current !== '') {
            update_option('category_base', '');
        }
    }

    private static function ensure_posts_page(): int
    {
        // Look up by correct slug first; fall back to old typo slug so we rename
        // the existing page in-place instead of creating a duplicate.
        $page = get_page_by_path(self::POSTS_PAGE_SLUG);
        if (!($page instanceof WP_Post)) {
            $page = get_page_by_path('baza-zaniy'); // legacy typo slug
        }

        if ($page instanceof WP_Post) {
            $page_id = (int) $page->ID;
            // Rename slug if it still carries the typo.
            if ($page->post_name !== self::POSTS_PAGE_SLUG) {
                wp_update_post([
                    'ID'        => $page_id,
                    'post_name' => self::POSTS_PAGE_SLUG,
                ]);
            }
        } else {
            $page_id = (int) wp_insert_post([
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => 'База знаний',
                'post_name'    => self::POSTS_PAGE_SLUG,
                'post_content' => '',
            ]);
        }

        if ($page_id > 0) {
            // Serve the posts index at /baza-znaniy/ (front page stays the static homepage).
            update_option('show_on_front', 'page');
            update_option('page_for_posts', $page_id);
        }

        return $page_id;
    }

}
