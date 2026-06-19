<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Content_Migrations
{
    public static function init(): void
    {
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_homepage(int $page_id): array
    {
        return Constructly_Homepage_Migration::migrate_homepage($page_id);
    }

    public static function build_homepage_content(): string
    {
        return Constructly_Homepage_Migration::build_homepage_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_calculators_index_page(int $page_id): array
    {
        return Constructly_Calculators_Index_Migration::migrate_calculators_index_page($page_id);
    }

    public static function build_calculators_index_page_content(): string
    {
        return Constructly_Calculators_Index_Migration::build_calculators_index_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_hub_page(int $page_id): array
    {
        return Constructly_Foundation_Hub_Migration::migrate_foundation_hub_page($page_id);
    }

    public static function build_foundation_hub_page_content(): string
    {
        return Constructly_Foundation_Hub_Migration::build_foundation_hub_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_strip_page(int $page_id): array
    {
        return Constructly_Foundation_Strip_Migration::migrate_foundation_strip_page($page_id);
    }

    public static function build_foundation_strip_page_content(): string
    {
        return Constructly_Foundation_Strip_Migration::build_foundation_strip_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_pile_page(int $page_id): array
    {
        return Constructly_Foundation_Pile_Migration::migrate_foundation_pile_page($page_id);
    }

    public static function build_foundation_pile_page_content(): string
    {
        return Constructly_Foundation_Pile_Migration::build_foundation_pile_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_slab_page(int $page_id): array
    {
        return Constructly_Foundation_Slab_Migration::migrate_foundation_slab_page($page_id);
    }

    public static function build_foundation_slab_page_content(): string
    {
        return Constructly_Foundation_Slab_Migration::build_foundation_slab_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_brick_page(int $page_id): array
    {
        return Constructly_Brick_Migration::migrate_brick_page($page_id);
    }

    public static function build_brick_page_content(): string
    {
        return Constructly_Brick_Migration::build_brick_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_screed_page(int $page_id): array
    {
        return Constructly_Screed_Migration::migrate_screed_page($page_id);
    }

    public static function build_screed_page_content(): string
    {
        return Constructly_Screed_Migration::build_screed_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_tile_page(int $page_id): array
    {
        return Constructly_Tile_Migration::migrate_tile_page($page_id);
    }

    public static function build_tile_page_content(): string
    {
        return Constructly_Tile_Migration::build_tile_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_drywall_page(int $page_id): array
    {
        return Constructly_Drywall_Migration::migrate_drywall_page($page_id);
    }

    public static function build_drywall_page_content(): string
    {
        return Constructly_Drywall_Migration::build_drywall_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_about_page(int $page_id): array
    {
        return Constructly_About_Migration::migrate_about_page($page_id);
    }

    public static function build_about_page_content(): string
    {
        return Constructly_About_Migration::build_about_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_contacts_page(int $page_id): array
    {
        return Constructly_Contacts_Migration::migrate_contacts_page($page_id);
    }

    public static function build_contacts_page_content(): string
    {
        return Constructly_Contacts_Migration::build_contacts_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_methodology_page(int $page_id): array
    {
        return Constructly_Methodology_Migration::migrate_methodology_page($page_id);
    }

    public static function build_methodology_page_content(): string
    {
        return Constructly_Methodology_Migration::build_methodology_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_privacy_page(int $page_id): array
    {
        return Constructly_Privacy_Migration::migrate_privacy_page($page_id);
    }

    public static function build_privacy_page_content(): string
    {
        return Constructly_Privacy_Migration::build_privacy_page_content();
    }

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_user_agreement_page(int $page_id): array
    {
        return Constructly_User_Agreement_Migration::migrate_user_agreement_page($page_id);
    }

    public static function build_user_agreement_page_content(): string
    {
        return Constructly_User_Agreement_Migration::build_user_agreement_page_content();
    }
}
