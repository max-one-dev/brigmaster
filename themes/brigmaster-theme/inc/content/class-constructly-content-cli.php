<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Content_Cli
{
    public static function init(): void
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        WP_CLI::add_command('constructly page home migrate', [self::class, 'migrate_home_page']);
        WP_CLI::add_command('constructly page foundation-hub migrate', [self::class, 'migrate_foundation_hub_page']);
        WP_CLI::add_command('constructly page foundation-strip migrate', [self::class, 'migrate_foundation_strip_page']);
        WP_CLI::add_command('constructly page about migrate', [self::class, 'migrate_about_page']);
    }

    private static function resolve_front_page_id(): int
    {
        $page_id = (int) get_option('page_on_front');

        if ($page_id <= 0) {
            WP_CLI::error('Front page is not configured. Pass --page_id=<id> or set Settings > Reading > Homepage.');
        }

        return $page_id;
    }

    /**
     * @param list<string> $paths
     */
    private static function resolve_page_id_by_path(array $paths): int
    {
        foreach ($paths as $path) {
            $page = get_page_by_path($path);
            if ($page instanceof WP_Post && $page->post_type === 'page') {
                return (int) $page->ID;
            }
        }

        WP_CLI::error('Target page was not found. Pass --page_id=<id>.');
    }

    /**
     * Migrates the configured front page with homepage content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to the WordPress front page.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page home migrate
     *     wp constructly page home migrate --dry-run
     *     wp constructly page home migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_home_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id']) ? (int) $assoc_args['page_id'] : self::resolve_front_page_id();

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_homepage_content());
            WP_CLI::success(sprintf('Dry run completed for homepage page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_homepage($page_id);

        WP_CLI::success(
            sprintf(
                'Home page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the foundation hub page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/fundament/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page foundation-hub migrate
     *     wp constructly page foundation-hub migrate --dry-run
     *     wp constructly page foundation-hub migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_foundation_hub_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/fundament', 'kalkulyator-fundamenta', 'fundament']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_foundation_hub_page_content());
            WP_CLI::success(sprintf('Dry run completed for foundation hub page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_foundation_hub_page($page_id);

        WP_CLI::success(
            sprintf(
                'Foundation hub content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the strip foundation calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/fundament/lentochnyj/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page foundation-strip migrate
     *     wp constructly page foundation-strip migrate --dry-run
     *     wp constructly page foundation-strip migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_foundation_strip_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/fundament/lentochnyj', 'kalkulyator-lentochnogo-fundamenta']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_foundation_strip_page_content());
            WP_CLI::success(sprintf('Dry run completed for strip foundation page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_foundation_strip_page($page_id);

        WP_CLI::success(
            sprintf(
                'Strip foundation content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the about page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /o-proekte/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page about migrate
     *     wp constructly page about migrate --dry-run
     *     wp constructly page about migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_about_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['o-proekte', 'about', 'o-nas']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_about_page_content());
            WP_CLI::success(sprintf('Dry run completed for about page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_about_page($page_id);

        WP_CLI::success(
            sprintf(
                'About page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }
}
