<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Foundation_Slab_Migration
{
    private const MIGRATION_VERSION = 'foundation-slab-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_slab_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Slab foundation page not found.');
        }

        $content = self::build_foundation_slab_page_content();

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

    public static function build_foundation_slab_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Фундамент', 'url' => '/kalkulyatory/fundament/'],
                    ['label' => 'Плитный фундамент'],
                ],
                'title' => 'Калькулятор плитного фундамента',
                'lead' => 'Рассчитайте объём бетона монолитной плиты, количество арматуры и материалов основания. Получите предварительную смету для плитного фундамента.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_concrete_estimator',
                'shortcodeTitle' => 'Параметры фундамента',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор рассчитывает монолитную фундаментную плиту: объём бетона, армирование и материалы исходя из площади и толщины плиты.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Объём бетона вычисляется как произведение площади плиты на её толщину; к результату добавляется технологический запас.</li>'
                    . '<li>Арматура считается из расчёта двух сеток (верхней и нижней) с выбранным шагом стержней по обоим направлениям.</li>'
                    . '<li>Дополнительно учитываются материалы подготовки основания под плитой в пределах заданных параметров.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по бетону, арматуре и материалам плиты.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какую толщину плиты выбрать?', 'answer' => 'Для малоэтажного строительства толщину обычно принимают в пределах 200–300 мм. Точное значение зависит от нагрузки и грунта.'],
                    ['question' => 'Как армируется плитный фундамент?', 'answer' => 'Чаще всего применяют две арматурные сетки — нижнюю и верхнюю — с шагом стержней 200 мм. Калькулятор считает обе сетки.'],
                    ['question' => 'Какой бетон нужен для плиты?', 'answer' => 'Обычно используют марки B20–B25. Точный выбор зависит от проекта и рекомендаций проектировщика.'],
                    ['question' => 'Нужна ли подготовка под плиту?', 'answer' => 'Под плиту устраивают песчано-щебёночную подушку и гидроизоляцию; их объём зависит от площади и толщины слоёв.'],
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
                    ['icon' => 'interface', 'title' => 'Плитка', 'description' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['tile']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
