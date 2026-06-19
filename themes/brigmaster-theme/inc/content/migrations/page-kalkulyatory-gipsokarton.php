<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Drywall_Migration
{
    private const MIGRATION_VERSION = 'drywall-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_drywall_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Drywall calculator page not found.');
        }

        $content = self::build_drywall_page_content();

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

    public static function build_drywall_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Гипсокартон'],
                ],
                'title' => 'Калькулятор гипсокартона',
                'lead' => 'Рассчитайте количество листов гипсокартона, профилей, крепежа и сопутствующих материалов для стен, перегородок и потолков. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_drywall_estimator',
                'shortcodeTitle' => 'Параметры конструкции',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор рассчитывает материалы каркасной обшивки гипсокартоном: листы ГКЛ, профили, крепёж и сопутствующие материалы по площади конструкции.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Количество листов определяется делением площади обшивки на площадь одного листа с учётом слоёв и запаса на подрезку.</li>'
                    . '<li>Длина профилей рассчитывается по шагу стоек и направляющих для выбранного типа каркаса.</li>'
                    . '<li>Количество саморезов, дюбелей и крепежа принимается по нормативному расходу на квадратный метр.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по листам, профилям и крепежу.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'В один или два слоя обшивать?', 'answer' => 'Двухслойная обшивка повышает прочность и звукоизоляцию, но удваивает расход листов. Количество слоёв выбирается в форме.'],
                    ['question' => 'Какой шаг профилей выбрать?', 'answer' => 'Стоечные профили обычно ставят с шагом 600 мм, при повышенных требованиях — 400 мм. Шаг влияет на длину профиля.'],
                    ['question' => 'Какой гипсокартон выбрать?', 'answer' => 'Для сухих помещений — обычный ГКЛ, для влажных — влагостойкий ГКЛВ. На расчёт количества листов тип не влияет.'],
                    ['question' => 'Учитывается ли крепёж?', 'answer' => 'Да, количество саморезов и дюбелей рассчитывается по нормативному расходу на квадратный метр.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса на подрезку.'],
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
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
