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
        WP_CLI::add_command('constructly page calculators migrate', [self::class, 'migrate_calculators_index_page']);
        WP_CLI::add_command('constructly page foundation-hub migrate', [self::class, 'migrate_foundation_hub_page']);
        WP_CLI::add_command('constructly page foundation-strip migrate', [self::class, 'migrate_foundation_strip_page']);
        WP_CLI::add_command('constructly page foundation-pile migrate', [self::class, 'migrate_foundation_pile_page']);
        WP_CLI::add_command('constructly page foundation-slab migrate', [self::class, 'migrate_foundation_slab_page']);
        WP_CLI::add_command('constructly page brick migrate', [self::class, 'migrate_brick_page']);
        WP_CLI::add_command('constructly page screed migrate', [self::class, 'migrate_screed_page']);
        WP_CLI::add_command('constructly page tile migrate', [self::class, 'migrate_tile_page']);
        WP_CLI::add_command('constructly page drywall migrate', [self::class, 'migrate_drywall_page']);
        WP_CLI::add_command('constructly page about migrate', [self::class, 'migrate_about_page']);
        WP_CLI::add_command('constructly page contacts migrate', [self::class, 'migrate_contacts_page']);
        WP_CLI::add_command('constructly page methodology migrate', [self::class, 'migrate_methodology_page']);
        WP_CLI::add_command('constructly page privacy migrate', [self::class, 'migrate_privacy_page']);
        WP_CLI::add_command('constructly page user-agreement migrate', [self::class, 'migrate_user_agreement_page']);
        WP_CLI::add_command('constructly seed articles', [self::class, 'seed_articles']);
    }

    /**
     * Seeds demo knowledge-base content: categories, the /stati/ posts page and
     * demo articles. Idempotent.
     *
     * ## EXAMPLES
     *
     *     wp constructly seed articles
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function seed_articles(array $args, array $assoc_args): void
    {
        $result = Constructly_Articles_Seed::seed();

        WP_CLI::success(
            sprintf(
                'Seeded %d categories, posts page ID %d, %d demo posts.',
                $result['categories'],
                $result['posts_page_id'],
                $result['posts']
            )
        );
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
     * Migrates the all-calculators index page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page calculators migrate
     *     wp constructly page calculators migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_calculators_index_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory', 'kalkulyatory-index', 'calculators']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_calculators_index_page_content());
            WP_CLI::success(sprintf('Dry run completed for calculators index page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_calculators_index_page($page_id);

        WP_CLI::success(
            sprintf(
                'Calculators index content migrated for page ID %d using %s.',
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
     * Migrates the pile foundation calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/fundament/svajnyj/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page foundation-pile migrate
     *     wp constructly page foundation-pile migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_foundation_pile_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/fundament/svajnyj', 'kalkulyator-svajnogo-fundamenta']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_foundation_pile_page_content());
            WP_CLI::success(sprintf('Dry run completed for pile foundation page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_foundation_pile_page($page_id);

        WP_CLI::success(
            sprintf(
                'Pile foundation content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the slab foundation calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/fundament/plitnyj/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page foundation-slab migrate
     *     wp constructly page foundation-slab migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_foundation_slab_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/fundament/plitnyj', 'kalkulyator-plitnogo-fundamenta']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_foundation_slab_page_content());
            WP_CLI::success(sprintf('Dry run completed for slab foundation page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_foundation_slab_page($page_id);

        WP_CLI::success(
            sprintf(
                'Slab foundation content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the brick calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/kirpich/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page brick migrate
     *     wp constructly page brick migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_brick_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/kirpich', 'kalkulyator-kirpicha']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_brick_page_content());
            WP_CLI::success(sprintf('Dry run completed for brick page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_brick_page($page_id);

        WP_CLI::success(
            sprintf(
                'Brick content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the screed calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/styazhka/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page screed migrate
     *     wp constructly page screed migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_screed_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/styazhka', 'kalkulyator-styazhki']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_screed_page_content());
            WP_CLI::success(sprintf('Dry run completed for screed page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_screed_page($page_id);

        WP_CLI::success(
            sprintf(
                'Screed content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the tile calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/plitka/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page tile migrate
     *     wp constructly page tile migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_tile_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/plitka', 'kalkulyator-plitki']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_tile_page_content());
            WP_CLI::success(sprintf('Dry run completed for tile page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_tile_page($page_id);

        WP_CLI::success(
            sprintf(
                'Tile content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the drywall calculator page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kalkulyatory/gipsokarton/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page drywall migrate
     *     wp constructly page drywall migrate --dry-run
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_drywall_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kalkulyatory/gipsokarton', 'kalkulyator-gipsokartona', 'kalkulyator-gkl']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_drywall_page_content());
            WP_CLI::success(sprintf('Dry run completed for drywall page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_drywall_page($page_id);

        WP_CLI::success(
            sprintf(
                'Drywall content migrated for page ID %d using %s.',
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

    /**
     * Migrates the contacts page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /kontakty/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page contacts migrate
     *     wp constructly page contacts migrate --dry-run
     *     wp constructly page contacts migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_contacts_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['kontakty', 'contacts', 'kontakt']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_contacts_page_content());
            WP_CLI::success(sprintf('Dry run completed for contacts page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_contacts_page($page_id);

        WP_CLI::success(
            sprintf(
                'Contacts page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the methodology page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /metodologiya/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page methodology migrate
     *     wp constructly page methodology migrate --dry-run
     *     wp constructly page methodology migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_methodology_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['metodologiya', 'metodologiya-raschetov', 'methodology']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_methodology_page_content());
            WP_CLI::success(sprintf('Dry run completed for methodology page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_methodology_page($page_id);

        WP_CLI::success(
            sprintf(
                'Methodology page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the privacy policy page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /privacy-policy/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page privacy migrate
     *     wp constructly page privacy migrate --dry-run
     *     wp constructly page privacy migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_privacy_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['privacy-policy', 'politika-konfidencialnosti', 'privacy']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_privacy_page_content());
            WP_CLI::success(sprintf('Dry run completed for privacy page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_privacy_page($page_id);

        WP_CLI::success(
            sprintf(
                'Privacy page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }

    /**
     * Migrates the user agreement page content.
     *
     * ## OPTIONS
     *
     * [--page_id=<id>]
     * : Optional page ID override. Defaults to /polzovatelskoe-soglashenie/.
     *
     * [--dry-run]
     * : Print generated content without writing to the database.
     *
     * ## EXAMPLES
     *
     *     wp constructly page user-agreement migrate
     *     wp constructly page user-agreement migrate --dry-run
     *     wp constructly page user-agreement migrate --page_id=123
     *
     * @param array<int, string> $args
     * @param array<string, string|bool> $assoc_args
     */
    public static function migrate_user_agreement_page(array $args, array $assoc_args): void
    {
        $page_id = isset($assoc_args['page_id'])
            ? (int) $assoc_args['page_id']
            : self::resolve_page_id_by_path(['polzovatelskoe-soglashenie', 'user-agreement', 'polzovatelskoe-soglasenie']);

        if (!empty($assoc_args['dry-run'])) {
            WP_CLI::line(Constructly_Content_Migrations::build_user_agreement_page_content());
            WP_CLI::success(sprintf('Dry run completed for user agreement page ID %d.', $page_id));

            return;
        }

        $result = Constructly_Content_Migrations::migrate_user_agreement_page($page_id);

        WP_CLI::success(
            sprintf(
                'User agreement page content migrated for page ID %d using %s.',
                $result['post_id'],
                $result['migration']
            )
        );
    }
}
