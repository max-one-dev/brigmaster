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
}
