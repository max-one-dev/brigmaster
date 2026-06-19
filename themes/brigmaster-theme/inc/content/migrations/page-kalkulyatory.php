<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Calculators_Index_Migration
{
    private const MIGRATION_VERSION = 'calculators-index-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_calculators_index_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Calculators index page not found.');
        }

        $content = self::build_calculators_index_page_content();

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

    public static function build_calculators_index_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'calculators-hero-title',
                'image' => 'assets/src/images/illustrations/hero-hub-foundation.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы'],
                ],
                'title' => 'Строительные калькуляторы',
                'lead' => 'Онлайн-калькуляторы для расчёта материалов: фундамент, кирпич, стяжка, плитка и гипсокартон. Быстрая предварительная оценка без регистрации.',
                'columns' => '4',
                'variant' => 'small-title',
                'features' => [
                    ['icon' => 'calculator', 'title' => 'Расчёт по формулам'],
                    ['icon' => 'book', 'title' => 'Прозрачная методика'],
                    ['icon' => 'clock-check', 'title' => 'Результат за минуты'],
                    ['icon' => '2fas-auth', 'title' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/foundation-hub-type-cards', [
                'anchorId' => 'calc-foundations',
                'titleId' => 'calc-foundations-title',
                'sectionTitle' => 'Фундаменты',
                'subtitle' => 'Выберите тип фундамента и выполните расчёт',
                'linkLabel' => 'Подробнее о фундаментах →',
                'linkUrl' => '/kalkulyatory/fundament/',
                'cards' => [
                    [
                        'image' => 'assets/src/images/cards/calc-cover-strip.svg',
                        'title' => 'Ленточный фундамент',
                        'text' => 'Размеры ленты, объём бетона, арматура и опалубка',
                        'href' => '/kalkulyatory/fundament/lentochnyj/',
                        'cta' => 'Рассчитать',
                    ],
                    [
                        'image' => 'assets/src/images/cards/calc-cover-pile.svg',
                        'title' => 'Свайный фундамент',
                        'text' => 'Количество свай, длина, шаг и материалы ростверка',
                        'href' => '/kalkulyatory/fundament/svajnyj/',
                        'cta' => 'Рассчитать',
                    ],
                    [
                        'image' => 'assets/src/images/cards/calc-cover-slab.svg',
                        'title' => 'Плитный фундамент',
                        'text' => 'Толщина плиты, объём бетона, арматура и материалы',
                        'href' => '/kalkulyatory/fundament/plitnyj/',
                        'cta' => 'Рассчитать',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/popular-calculators', [
                'anchor' => 'calc-materials',
                'titleId' => 'calc-materials-title',
                'title' => 'Материалы и отделка',
                'linkLabel' => 'Все калькуляторы',
                'linkUrl' => '#calc-foundations',
                'cards' => [
                    ['icon' => 'target', 'title' => 'Кирпич', 'description' => 'Количество кирпича и раствора для кладки стен и перегородок.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['brick']],
                    ['icon' => 'calculator', 'title' => 'Стяжка пола', 'description' => 'Объём раствора и количество смеси по площади и толщине слоя.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['screed']],
                    ['icon' => 'interface', 'title' => 'Плитка', 'description' => 'Количество плитки, клея и затирки с учётом раскладки.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['tile']],
                    ['icon' => 'document-add', 'title' => 'Гипсокартон', 'description' => 'Листы ГКЛ, профили и крепёж для стен, перегородок и потолков.', 'buttonLabel' => 'Рассчитать', 'buttonUrl' => $links['drywall']],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/trust', [
                'anchor' => 'calc-how',
                'titleId' => 'calc-how-title',
                'themeVariant' => 'bg',
                'title' => 'Как это работает',
                'subtitle' => 'Три шага от параметров до сметы материалов',
                'linkLabel' => 'Методика расчётов',
                'linkUrl' => '/metodologiya/',
                'items' => [
                    [
                        'icon' => 'measurement',
                        'title' => 'Введите параметры',
                        'text' => 'Укажите размеры, режим расчёта и материалы в форме калькулятора.',
                    ],
                    [
                        'icon' => 'calculator',
                        'title' => 'Получите расчёт',
                        'text' => 'Калькулятор посчитает объёмы и количество материалов с учётом запаса.',
                    ],
                    [
                        'icon' => 'document-add',
                        'title' => 'Сохраните смету',
                        'text' => 'Откройте PDF, распечатайте или скопируйте ссылку на результат.',
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Проверьте перед закупкой',
                        'text' => 'Результат ориентировочный — сверьте его с проектом и условиями объекта.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calc-faq',
                'titleId' => 'calc-faq-title',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Калькуляторы бесплатные?', 'answer' => 'Да, все расчёты бесплатны и не требуют регистрации.'],
                    ['question' => 'Насколько точны расчёты?', 'answer' => 'Калькуляторы дают ориентир для предварительной оценки. Точность зависит от исходных данных и условий объекта.'],
                    ['question' => 'Можно ли использовать результат для сметы?', 'answer' => 'Да, как предварительную основу. Для рабочей сметы сверьте результат с проектом и ценами поставщиков.'],
                    ['question' => 'Учитывается ли запас материалов?', 'answer' => 'Да, в большинстве калькуляторов можно задать процент запаса на потери и подрезку.'],
                    ['question' => 'Какой калькулятор выбрать?', 'answer' => 'Выберите калькулятор по типу работ: фундамент, кирпичная кладка, стяжка, плитка или гипсокартон.'],
                    ['question' => 'Можно ли сохранить и распечатать расчёт?', 'answer' => 'Да, результат можно открыть в PDF, распечатать или скопировать ссылку.'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'calc-cta-title',
                'variant' => 'soft',
                'title' => 'Не знаете, с чего начать?',
                'text' => 'Начните с расчёта фундамента — это основа сметы для большинства проектов.',
                'buttonLabel' => 'Перейти к фундаментам',
                'buttonUrl' => '/kalkulyatory/fundament/',
                'image' => '',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
