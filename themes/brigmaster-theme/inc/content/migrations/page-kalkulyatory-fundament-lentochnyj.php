<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Foundation_Strip_Migration
{
    private const MIGRATION_VERSION = 'foundation-strip-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_strip_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Strip foundation page not found.');
        }

        $content = self::build_foundation_strip_page_content();

        // wp_update_post() runs wp_unslash() on its input, which would strip the backslash
        // from JSON-escaped block attributes (e.g. < in the infoBody HTML, turning it
        // into a literal "u003c"). Pre-slash so the original content survives.
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

    public static function build_foundation_strip_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Фундамент', 'url' => '/kalkulyatory/fundament/'],
                    ['label' => 'Ленточный фундамент'],
                ],
                'title' => 'Калькулятор ленточного фундамента',
                'lead' => 'Рассчитайте объём бетона, количество арматуры и опалубки для строительства ленточного фундамента. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_strip_foundation_estimator',
                'shortcodeTitle' => 'Параметры фундамента',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор использует инженерные формулы для расчёта ленточного фундамента с учётом выбранных параметров.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Объём бетона вычисляется как произведение длины ленты, ширины и суммарной высоты. К результату автоматически добавляется технологический запас.</li>'
                    . '<li>Арматурный каркас рассчитывается по минимальному проценту армирования сечения фундамента и выбранному шагу хомутов.</li>'
                    . '<li>Площадь опалубки принимается равной площади боковых поверхностей фундамента с запасом на раскрой материалов.</li>'
                    . '</ol>',
                'noteText' => 'Узнайте больше в Базе Знаний.',
                'noteLinkLabel' => 'Подробное руководство по проектированию ленточных фундаментов',
                'noteLinkUrl' => '/baza-znaniy/lentochnyy-fundament/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по бетону, арматуре и опалубке.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какой запас бетона нужно закладывать?', 'answer' => 'Обычно закладывают небольшой запас на потери, подрезку и особенности объекта.'],
                    ['question' => 'Какие единицы измерения используются?', 'answer' => 'Длины указываются в метрах, арматура в миллиметрах, объёмы в кубических метрах.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если указан процент запаса в дополнительных параметрах.'],
                    ['question' => 'Нужна ли регистрация для использования?', 'answer' => 'Нет, расчёт можно выполнить без регистрации.'],
                    ['question' => 'Можно ли сохранить и распечатать расчёт?', 'answer' => 'Да, после выполнения расчёта доступны кнопки «Сохранить» и «Печать» — результат можно распечатать или сохранить на устройство.'],
                    ['question' => 'Как часто обновляются данные?', 'answer' => 'Нормативные и справочные значения обновляются при изменении методики расчёта.'],
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
                    ['icon' => 'interface', 'title' => 'Плитка', 'description' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['tile']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
