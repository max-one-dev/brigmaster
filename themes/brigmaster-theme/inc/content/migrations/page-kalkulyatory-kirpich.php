<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Brick_Migration
{
    private const MIGRATION_VERSION = 'brick-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_brick_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Brick calculator page not found.');
        }

        $content = self::build_brick_page_content();

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

    public static function build_brick_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Кирпич'],
                ],
                'title' => 'Калькулятор кирпича',
                'lead' => 'Рассчитайте количество кирпича и кладочного раствора для стен и перегородок с учётом типа кладки и проёмов. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_brick_estimator',
                'shortcodeTitle' => 'Параметры кладки',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор определяет количество кирпича и объём кладочного раствора по площади стен, типу кладки и размеру кирпича.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Площадь кладки вычисляется по длине и высоте стен за вычетом площади оконных и дверных проёмов.</li>'
                    . '<li>Количество кирпича определяется по расходу на квадратный метр для выбранной толщины кладки и формата кирпича, с учётом растворных швов.</li>'
                    . '<li>Объём раствора рассчитывается по нормативному расходу для выбранного типа кладки; к материалам добавляется технологический запас.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по кирпичу и раствору.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Учитывается ли растворный шов?', 'answer' => 'Да, расход кирпича на квадратный метр уже включает толщину кладочных швов, поэтому результат ближе к фактическому.'],
                    ['question' => 'Чем отличается кладка в полкирпича и в кирпич?', 'answer' => 'Это разная толщина стены: чем толще кладка, тем больше кирпича на квадратный метр. Тип кладки выбирается в форме.'],
                    ['question' => 'Какой формат кирпича выбрать?', 'answer' => 'Поддерживаются одинарный, полуторный и двойной форматы — у каждого свой расход на квадратный метр.'],
                    ['question' => 'Вычитаются ли проёмы?', 'answer' => 'Да, площадь окон и дверей вычитается из общей площади кладки.'],
                    ['question' => 'Учитываются ли потери и бой?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса на бой и подрезку.'],
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
                    ['icon' => 'interface', 'title' => 'Плитка', 'description' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['tile']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
