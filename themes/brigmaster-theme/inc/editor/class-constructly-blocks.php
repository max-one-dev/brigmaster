<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Blocks
{
    /**
     * @return array<string, array<string, mixed>>
     */
    private static function definitions(): array
    {
        return [
            'hero' => [
                'name' => 'constructly/home-hero',
                'title' => 'Constructly Hero',
                'template' => 'hero',
            ],
            'how-it-works' => [
                'name' => 'constructly/how-it-works',
                'title' => 'Constructly How It Works',
                'template' => 'how-it-works',
            ],
            'popular-calculators' => [
                'name' => 'constructly/popular-calculators',
                'title' => 'Constructly Popular Calculators',
                'template' => 'popular-calculators',
            ],
            'tasks' => [
                'name' => 'constructly/tasks',
                'title' => 'Constructly Tasks',
                'template' => 'tasks',
            ],
            'why-brigmaster' => [
                'name' => 'constructly/why-brigmaster',
                'title' => 'Constructly Why Brigmaster',
                'template' => 'why-brigmaster',
            ],
            'trust' => [
                'name' => 'constructly/trust',
                'title' => 'Constructly Trust',
                'template' => 'trust',
            ],
            'who-its-for' => [
                'name' => 'constructly/who-its-for',
                'title' => 'Constructly Who It Is For',
                'template' => 'who-its-for',
            ],
            'how-calculations-work' => [
                'name' => 'constructly/how-calculations-work',
                'title' => 'Constructly How Calculations Work',
                'template' => 'how-calculations-work',
            ],
            'final-cta' => [
                'name' => 'constructly/final-cta',
                'title' => 'Constructly Final CTA',
                'template' => 'final-cta',
            ],
            'articles' => [
                'name' => 'constructly/articles',
                'title' => 'Constructly Articles',
                'template' => 'articles',
            ],
            'faq' => [
                'name' => 'constructly/faq',
                'title' => 'Constructly FAQ',
                'template' => 'faq',
            ],
            'legacy-home-tasks' => [
                'name' => 'constructly/home-tasks',
                'title' => 'Constructly Tasks',
                'template' => 'tasks',
            ],
            'legacy-home-articles' => [
                'name' => 'constructly/home-articles',
                'title' => 'Constructly Articles',
                'template' => 'articles',
            ],
            'legacy-home-faq' => [
                'name' => 'constructly/home-faq',
                'title' => 'Constructly FAQ',
                'template' => 'faq',
            ],
            'foundation-hub-hero' => [
                'name' => 'constructly/foundation-hub-hero',
                'title' => 'Constructly Foundation Hub Hero',
                'template' => 'foundation-hub-hero',
            ],
            'foundation-hub-type-cards' => [
                'name' => 'constructly/foundation-hub-type-cards',
                'title' => 'Constructly Foundation Hub Types',
                'template' => 'foundation-hub-type-cards',
            ],
            'calculator-hero' => [
                'name' => 'constructly/calculator-hero',
                'title' => 'Constructly Calculator Hero',
                'template' => 'calculator-hero',
            ],
            'calculator-estimator' => [
                'name' => 'constructly/calculator-estimator',
                'title' => 'Constructly Calculator Estimator',
                'template' => 'calculator-estimator',
            ],
        ];
    }

    public static function init(): void
    {
        add_action('init', [self::class, 'register_assets']);
        add_action('init', [self::class, 'register_blocks']);
        add_filter('block_categories_all', [self::class, 'register_category']);
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     * @return array<int, array<string, mixed>>
     */
    public static function register_category(array $categories): array
    {
        $categories[] = [
            'slug' => 'constructly',
            'title' => __('Constructly', 'brigmaster-theme'),
            'icon' => null,
        ];

        return $categories;
    }

    public static function register_assets(): void
    {
        Constructly_Assets::register_editor_preview_styles();

        $editor_bundle = Constructly_Assets::get_registered_script_bundle('editor.js');

        if ($editor_bundle !== null) {
            wp_register_script(
                'bm-editor-blocks',
                $editor_bundle['src'],
                ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render'],
                $editor_bundle['ver'],
                true
            );
            wp_script_add_data('bm-editor-blocks', 'type', 'module');

            return;
        }

        wp_register_script(
            'bm-editor-blocks',
            false,
            ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render'],
            CONSTRUCTLY_THEME_VERSION,
            true
        );
    }

    public static function register_blocks(): void
    {
        $attributes = self::block_attributes();

        foreach (self::definitions() as $definition) {
            register_block_type($definition['name'], [
                'api_version' => 3,
                'title' => __($definition['title'], 'brigmaster-theme'),
                'category' => 'constructly',
                'attributes' => $attributes,
                'editor_script' => 'bm-editor-blocks',
                'style' => 'bm-theme',
                'editor_style' => ['bm-theme-editor', 'bm-theme-editor-home', 'bm-theme-editor-hub'],
                'supports' => [
                    'anchor' => false,
                    'customClassName' => false,
                    'html' => false,
                    'align' => false,
                ],
                'render_callback' => static function (array $attributes, string $content, WP_Block $block) use ($definition): string {
                    return self::render_template($definition['template'], $attributes, $content, $block);
                },
            ]);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function block_attributes(): array
    {
        return [
            'title' => ['type' => 'string'],
            'lead' => ['type' => 'string'],
            'subtitle' => ['type' => 'string'],
            'text' => ['type' => 'string'],
            'anchor' => ['type' => 'string'],
            'anchorId' => ['type' => 'string'],
            'sectionId' => ['type' => 'string'],
            'sectionTitle' => ['type' => 'string'],
            'titleId' => ['type' => 'string'],
            'variant' => ['type' => 'string'],
            'primaryLabel' => ['type' => 'string'],
            'primaryUrl' => ['type' => 'string'],
            'secondaryLabel' => ['type' => 'string'],
            'secondaryUrl' => ['type' => 'string'],
            'linkLabel' => ['type' => 'string'],
            'linkUrl' => ['type' => 'string'],
            'buttonLabel' => ['type' => 'string'],
            'buttonUrl' => ['type' => 'string'],
            'image' => ['type' => 'string'],
            'note' => ['type' => 'string'],
            'quickLinksLabel' => ['type' => 'string'],
            'themeVariant' => ['type' => 'string'],
            'ctaLabel' => ['type' => 'string'],
            'ctaUrl' => ['type' => 'string'],
            'aside' => ['type' => 'string'],
            'shortcode' => ['type' => 'string'],
            'shortcodeTag' => ['type' => 'string'],
            'shortcodeTitle' => ['type' => 'string'],
            'infoTitle' => ['type' => 'string'],
            'infoText' => ['type' => 'string'],
            'infoBody' => ['type' => 'string'],
            'methodTitle' => ['type' => 'string'],
            'noteText' => ['type' => 'string'],
            'noteLinkLabel' => ['type' => 'string'],
            'noteLinkUrl' => ['type' => 'string'],
            'resultTitle' => ['type' => 'string'],
            'resultStatus' => ['type' => 'string'],
            'resultText' => ['type' => 'string'],
            'features' => [
                'type' => 'array',
                'default' => [],
            ],
            'breadcrumbs' => [
                'type' => 'array',
                'default' => [],
            ],
            'cards' => [
                'type' => 'array',
                'default' => [],
            ],
            'items' => [
                'type' => 'array',
                'default' => [],
            ],
            'steps' => [
                'type' => 'array',
                'default' => [],
            ],
            'methodItems' => [
                'type' => 'array',
                'default' => [],
            ],
            'quickLinks' => [
                'type' => 'array',
                'default' => [],
            ],
            'demo' => [
                'type' => 'object',
                'default' => [],
            ],
        ];
    }

    public static function render_template(string $template, array $attributes, string $content, WP_Block $block): string
    {
        $template_path = CONSTRUCTLY_THEME_PATH . '/templates/blocks/' . $template . '.php';

        if (!is_readable($template_path)) {
            return $content;
        }

        ob_start();
        require $template_path;

        return (string) ob_get_clean();
    }
}
