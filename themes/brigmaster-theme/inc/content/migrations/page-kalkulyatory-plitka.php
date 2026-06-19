<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Tile_Migration
{
    private const MIGRATION_VERSION = 'tile-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_tile_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Tile calculator page not found.');
        }

        $content = self::build_tile_page_content();

        // Block attribute JSON escapes < > " & to \uXXXX; wp_update_post() runs
        // wp_unslash() which would strip those backslashes and corrupt the markup.
        // Slash the content so the escaping survives.
        wp_update_post([
            'ID' => $page_id,
            'post_content' => wp_slash($content),
        ]);

        update_post_meta($page_id, '_constructly_content_migration', self::MIGRATION_VERSION);

        return [
            'post_id' => $page_id,
            'content' => $content,
            'migration' => self::MIGRATION_VERSION,
        ];
    }

    public static function build_tile_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Плитка'],
                ],
                'title' => 'Калькулятор плитки',
                'lead' => 'Рассчитайте количество плитки, плиточного клея и затирки для пола или стен с учётом площади, размера плитки и раскладки. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_tile_estimator',
                'shortcodeTitle' => 'Параметры облицовки',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор определяет количество плитки, плиточного клея и затирки по площади поверхности и размеру плитки.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Количество плитки вычисляется делением площади облицовки на площадь одной плитки с добавлением запаса на подрезку и раскладку.</li>'
                    . '<li>Расход клея определяется по площади и рекомендованному слою для выбранного размера плитки.</li>'
                    . '<li>Расход затирки рассчитывается исходя из размеров плитки и ширины шва.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по плитке, клею и затирке.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какой запас плитки закладывать?', 'answer' => 'Обычно закладывают 5–10% на подрезку. При диагональной раскладке или сложной геометрии запас увеличивают.'],
                    ['question' => 'Как раскладка влияет на расход?', 'answer' => 'Прямая раскладка даёт меньше отходов, диагональная и со смещением — больше. Это учитывается через процент запаса.'],
                    ['question' => 'Сколько нужно плиточного клея?', 'answer' => 'Расход клея зависит от размера плитки и толщины слоя; крупная плитка требует более толстого слоя.'],
                    ['question' => 'Как рассчитывается затирка?', 'answer' => 'Расход затирки зависит от размеров плитки и ширины шва: чем мельче плитка и шире шов, тем больше затирки.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса.'],
                    ['question' => 'Нужна ли регистрация для использования?', 'answer' => 'Нет, расчёт можно выполнить без регистрации.'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/popular-calculators', [
                'anchor' => 'related-calculators',
                'titleId' => 'related-calculators-title',
                'themeVariant' => 'muted',
                'title' => 'Быстрый доступ к калькуляторам',
                'linkLabel' => 'Все калькуляторы',
                'linkUrl' => '/kalkulyatory/',
                'cards' => [
                    ['icon' => 'measurement', 'title' => 'Фундамент', 'description' => 'Расчёт объёма бетона, опалубки, арматуры и материалов.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => '/kalkulyatory/fundament/'],
                    ['icon' => 'calculator', 'title' => 'Стяжка пола', 'description' => 'Цементно-песчаная стяжка, наливные полы и другие типы.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['screed']],
                    ['icon' => 'target', 'title' => 'Кирпич', 'description' => 'Количество кирпича и раствора для кладки стен и перегородок.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['brick']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
