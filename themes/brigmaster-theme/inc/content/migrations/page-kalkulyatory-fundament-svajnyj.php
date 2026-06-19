<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Foundation_Pile_Migration
{
    private const MIGRATION_VERSION = 'foundation-pile-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_pile_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Pile foundation page not found.');
        }

        $content = self::build_foundation_pile_page_content();

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

    public static function build_foundation_pile_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Фундамент', 'url' => '/kalkulyatory/fundament/'],
                    ['label' => 'Свайный фундамент'],
                ],
                'title' => 'Калькулятор свайного фундамента',
                'lead' => 'Рассчитайте количество свай, их длину и шаг, объём бетона и материалы ростверка для свайного фундамента. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_pile_foundation_estimator',
                'shortcodeTitle' => 'Параметры фундамента',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор подбирает количество свай и рассчитывает материалы свайного фундамента с учётом размеров здания, шага свай и параметров ростверка.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Количество свай определяется по периметру и осям здания с учётом выбранного шага: чем меньше шаг, тем больше опор приходится на ленту ростверка.</li>'
                    . '<li>Длина одной сваи складывается из заглубления ниже уровня промерзания грунта и высоты выступающей части под ростверк.</li>'
                    . '<li>Объём бетона на сваю вычисляется по диаметру и длине, затем умножается на количество свай; к ростверку добавляется отдельный объём бетона и арматуры.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по сваям, бетону и ростверку.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какой шаг свай выбрать?', 'answer' => 'Шаг зависит от нагрузки и типа ростверка, обычно его принимают в пределах 1–3 м. Под несущими стенами и углами сваи располагают чаще.'],
                    ['question' => 'На какую глубину погружать сваи?', 'answer' => 'Сваи заглубляют ниже уровня промерзания грунта и опирают на плотные слои. Точную глубину определяет геология участка.'],
                    ['question' => 'Какие сваи учитывает калькулятор?', 'answer' => 'Расчёт ориентирован на буронабивные и забивные сваи круглого сечения; для винтовых свай ориентируйтесь на количество опор.'],
                    ['question' => 'Нужен ли ростверк?', 'answer' => 'Ростверк объединяет сваи в единую конструкцию и распределяет нагрузку от стен. В большинстве случаев он необходим.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса на бетон и арматуру.'],
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
