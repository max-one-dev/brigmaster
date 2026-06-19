<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Screed_Migration
{
    private const MIGRATION_VERSION = 'screed-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_screed_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Screed calculator page not found.');
        }

        $content = self::build_screed_page_content();

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

    public static function build_screed_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Стяжка пола'],
                ],
                'title' => 'Калькулятор стяжки пола',
                'lead' => 'Рассчитайте объём раствора и количество сухой смеси или цемента и песка для стяжки пола по площади и толщине слоя. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_screed_estimator',
                'shortcodeTitle' => 'Параметры стяжки',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор рассчитывает материалы для стяжки пола: объём раствора и количество смеси по площади помещения и толщине слоя.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Объём раствора вычисляется как произведение площади пола на толщину стяжки.</li>'
                    . '<li>Для готовой сухой смеси количество мешков определяется по расходу на квадратный метр при толщине 10 мм, пересчитанному на заданную толщину.</li>'
                    . '<li>Для раствора из цемента и песка компоненты рассчитываются по выбранной пропорции; к материалам добавляется технологический запас.</li>'
                    . '</ol>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по раствору и материалам стяжки.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какую толщину стяжки выбрать?', 'answer' => 'Цементно-песчаную стяжку обычно делают толщиной 30–50 мм. Минимальная толщина зависит от основания и типа смеси.'],
                    ['question' => 'Готовая смесь или цемент с песком?', 'answer' => 'Калькулятор поддерживает оба варианта: количество мешков готовой смеси или объёмы цемента и песка по пропорции.'],
                    ['question' => 'Какая пропорция раствора нужна?', 'answer' => 'Для стяжки часто используют пропорцию 1:3 или 1:4 (цемент:песок). Пропорция выбирается в форме.'],
                    ['question' => 'Нужно ли армировать стяжку?', 'answer' => 'Армирование сеткой рекомендуется при больших площадях и толстых слоях; на расход раствора оно влияет незначительно.'],
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
                    ['icon' => 'target', 'title' => 'Кирпич', 'description' => 'Количество кирпича и раствора для кладки стен и перегородок.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['brick']],
                    ['icon' => 'interface', 'title' => 'Плитка', 'description' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['tile']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
