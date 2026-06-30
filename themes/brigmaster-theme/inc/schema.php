<?php
declare(strict_types=1);

/**
 * BreadcrumbList JSON-LD schema.
 *
 * Captures breadcrumb data from constructly/calculator-hero or
 * constructly/page-hero block attributes during render_block (guaranteed
 * to have the real stored attrs), then outputs JSON-LD in wp_footer.
 * Google accepts JSON-LD anywhere in the document.
 */

/** @var array<int,array{label:string,url?:string}> */
$GLOBALS['_bm_breadcrumb_ld_crumbs'] = [];

add_filter( 'render_block', 'constructly_capture_hero_breadcrumbs', 10, 2 );

function constructly_capture_hero_breadcrumbs( string $content, array $block ): string {
    $hero_blocks = [ 'constructly/calculator-hero', 'constructly/page-hero' ];

    if ( ! in_array( $block['blockName'] ?? '', $hero_blocks, true ) ) {
        return $content;
    }

    $crumbs = $block['attrs']['breadcrumbs'] ?? [];
    if ( is_array( $crumbs ) && ! empty( $crumbs ) ) {
        $GLOBALS['_bm_breadcrumb_ld_crumbs'] = $crumbs;
    }

    return $content;
}

add_action( 'wp_footer', 'constructly_output_breadcrumb_schema' );
add_action( 'wp_footer', 'constructly_output_webpage_schema' );

function constructly_output_breadcrumb_schema(): void {
    if ( ! is_singular() || is_front_page() ) {
        return;
    }

    $crumbs = $GLOBALS['_bm_breadcrumb_ld_crumbs'] ?? [];

    $items = [];

    if ( ! empty( $crumbs ) ) {
        // Primary: hero block breadcrumbs (exact labels users see visually).
        foreach ( $crumbs as $crumb ) {
            if ( ! is_array( $crumb ) ) {
                continue;
            }
            $name = wp_strip_all_tags( (string) ( $crumb['label'] ?? '' ) );
            $url  = trim( (string) ( $crumb['url'] ?? '' ) );
            if ( $name === '' ) {
                continue;
            }
            $entry = [
                '@type'    => 'ListItem',
                'position' => count( $items ) + 1,
                'name'     => $name,
            ];
            if ( $url !== '' && $url !== '#' ) {
                $entry['item'] = esc_url( home_url( $url ) );
            }
            $items[] = $entry;
        }
    } else {
        // Fallback: WordPress page parent hierarchy.
        $post = get_post();
        if ( ! $post instanceof WP_Post ) {
            return;
        }

        $trail   = [];
        $current = $post;
        while ( $current instanceof WP_Post ) {
            $trail[] = $current;
            $current = $current->post_parent ? get_post( $current->post_parent ) : null;
        }
        $trail = array_reverse( $trail );

        $items[] = [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Главная',
            'item'     => home_url( '/' ),
        ];
        foreach ( $trail as $index => $page ) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $index + 2,
                'name'     => wp_strip_all_tags( get_the_title( $page ) ),
                'item'     => (string) get_permalink( $page ),
            ];
        }
    }

    if ( empty( $items ) ) {
        return;
    }

    echo '<script type="application/ld+json">' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        . wp_json_encode(
            [
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $items,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )
        . "</script>\n";
}

function constructly_output_webpage_schema(): void {
    if ( ! is_singular() ) {
        return;
    }

    $post = get_post();
    if ( ! $post instanceof WP_Post ) {
        return;
    }

    $slug = $post->post_name;
    $allowed_slugs = [ 'o-proekte', 'kontakty' ];

    if ( ! in_array( $slug, $allowed_slugs, true ) ) {
        return;
    }

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'WebPage',
        'name'        => wp_strip_all_tags( get_the_title( $post ) ),
        'url'         => esc_url( (string) get_permalink( $post ) ),
        'description' => wp_strip_all_tags( (string) get_the_excerpt( $post ) ),
        'inLanguage'  => 'ru-RU',
        'isPartOf'    => [
            '@type' => 'WebSite',
            'url'   => home_url( '/' ),
        ],
    ];

    echo '<script type="application/ld+json">' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        . "</script>\n";
}
