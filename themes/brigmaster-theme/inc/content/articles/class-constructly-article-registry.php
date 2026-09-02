<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registry of all seeded knowledge-base articles.
 *
 * Each data file in data/ must return an associative array matching this schema:
 *
 * @phpstan-type ArticleData array{
 *   key:        string,        // Unique article key — used as idempotency anchor (_bm_article_key meta).
 *   slug:       string,        // URL slug (post_name).
 *   title:      string,        // H1 / post title.
 *   category:   string,        // Category slug — must be one of the slugs defined in Constructly_Articles_Seed::CATEGORIES.
 *   date:       string,        // Publication date in Y-m-d format.
 *   views_min:  int,           // Minimum seed view count (inclusive).
 *   views_max:  int,           // Maximum seed view count (inclusive).
 *   excerpt:    string,        // 1–2 sentence lead shown in archive cards and post_excerpt.
 *   content:    string,        // Post body as Gutenberg block markup. May contain {{img:file-basename}} placeholders.
 *   images:     array{
 *     hero?:  array{file:string, alt:string},           // → _bm_hero_image_id post meta.
 *     cover?: array{file:string, alt:string},           // → featured image (thumbnail).
 *     body?:  list<array{file:string, alt:string}>,     // Inserted into content via {{img:file-basename}}.
 *   },
 *   meta_title?:       string,  // Optional. Rank Math SEO title → rank_math_title post meta.
 *   meta_description?: string,  // Optional. Rank Math meta description → rank_math_description post meta.
 * }
 *
 * Image files live at:
 *   themes/brigmaster-theme/assets/src/images/posts/<file>.jpg
 *
 * To add a new article:
 *   1. Create data/<key>-<slug-fragment>.php returning the ArticleData array.
 *   2. Append its filename (without .php) to ARTICLE_FILES below.
 *   3. Run: wp constructly seed articles
 */
final class Constructly_Article_Registry
{
    /**
     * Ordered list of data-file basenames (no .php extension).
     * The seed order reflects publication order on the archive page.
     *
     * @var list<string>
     */
    private const ARTICLE_FILES = [
        'f1-beton-lenta',
        'f2-lenta-vs-plita',
        's1-tolshchina-styazhki',
        'o1-rashod-plitki',
        'f3-glubina-zalozheniya',
        's2-polusuhaya-mokraya',
        'o2-rashod-kirpicha',
        's3-rashod-suhoy-smesi',
        's4-tsementno-peschanaya-styazhka',
        's5-armatura-styazhki',
        's6-styazhka-smeta',
    ];

    /**
     * Valid category slugs — kept in sync with Constructly_Articles_Seed::CATEGORIES.
     *
     * @var list<string>
     */
    private const VALID_CATEGORIES = [
        'fundament',
        'poly',
        'otdelka',
        'steny',
    ];

    /**
     * Required top-level keys in every ArticleData array.
     *
     * @var list<string>
     */
    private const REQUIRED_KEYS = [
        'key',
        'slug',
        'title',
        'category',
        'date',
        'views_min',
        'views_max',
        'excerpt',
        'content',
        'images',
    ];

    /**
     * Loads and validates all registered article data files.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $dir     = __DIR__ . '/data/';
        $results = [];

        foreach (self::ARTICLE_FILES as $basename) {
            $path = $dir . $basename . '.php';

            if (!is_readable($path)) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                trigger_error(
                    "Constructly_Article_Registry: data file not readable: {$path}",
                    E_USER_WARNING
                );
                continue;
            }

            $data = require $path;

            if (!is_array($data)) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                trigger_error(
                    "Constructly_Article_Registry: data file must return an array: {$path}",
                    E_USER_WARNING
                );
                continue;
            }

            $error = self::validate($data, $path);
            if ($error !== null) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                trigger_error("Constructly_Article_Registry: {$error}", E_USER_WARNING);
                continue;
            }

            $results[] = $data;
        }

        return $results;
    }

    /**
     * Returns a validation error string, or null when data is valid.
     *
     * @param array<string, mixed> $data
     */
    private static function validate(array $data, string $path): ?string
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                return "Missing required key '{$key}' in {$path}";
            }
        }

        if (!in_array($data['category'], self::VALID_CATEGORIES, true)) {
            $valid = implode(', ', self::VALID_CATEGORIES);
            return "Invalid category '{$data['category']}' in {$path}. Must be one of: {$valid}";
        }

        if (!is_array($data['images'])) {
            return "Key 'images' must be an array in {$path}";
        }

        return null;
    }
}
