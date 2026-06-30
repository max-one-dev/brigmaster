<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Foundation_Hub_Migration
{
    private const MIGRATION_VERSION = 'foundation-hub-v3';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_foundation_hub_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Foundation hub page not found.');
        }

        $content = self::build_foundation_hub_page_content();

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

    public static function build_foundation_hub_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/foundation-hub-hero', [
                'image' => 'assets/src/images/illustrations/hero-fundament.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Фундамент'],
                ],
                'title' => 'Калькулятор фундамента',
                'lead' => 'Подберите подходящий тип фундамента, рассчитайте количество материалов и получите ориентировочные параметры для вашего проекта.',
                'features' => [
                    ['icon' => 'calculator', 'title' => 'Точные расчёты', 'text' => 'на основе форм и формул'],
                    ['icon' => 'book', 'title' => 'Прозрачная методика', 'text' => 'понятная логика расчётов'],
                    ['icon' => 'clock-check', 'title' => 'Быстрый результат', 'text' => 'за несколько минут'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/tasks', [
                'anchor' => 'foundation-tasks',
                'titleId' => 'foundation-tasks-title',
                'variant' => 'compact',
                'title' => 'Выберите свою задачу',
                'items' => [
                    [
                        'icon' => 'briefcase',
                        'title' => 'Строю дом с нуля',
                        'text' => 'Нужен надёжный фундамент для нового дома',
                        'label' => 'Подобрать и рассчитать',
                        'url' => '#hub-calculators',
                    ],
                    [
                        'icon' => 'reload',
                        'title' => 'Реконструкция дома',
                        'text' => 'Хочу усилить или заменить существующий фундамент',
                        'label' => 'Подобрать и рассчитать',
                        'url' => '#hub-calculators',
                    ],
                    [
                        'icon' => 'measurement',
                        'title' => 'Сложный грунт',
                        'text' => 'Участок с высоким УГВ, пучинистые или слабые грунты',
                        'label' => 'Подобрать и рассчитать',
                        'url' => '#hub-calculators',
                    ],
                    [
                        'icon' => 'calculator',
                        'title' => 'Сравнить варианты',
                        'text' => 'Сравню разные типы фундаментов по параметрам и материалам',
                        'label' => 'Подобрать и рассчитать',
                        'url' => '#hub-calculators',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/foundation-hub-type-cards', [
                'anchorId' => 'hub-calculators',
                'titleId' => 'hub-calculators-title',
                'sectionTitle' => 'Калькуляторы фундаментов',
                'subtitle' => 'Выберите тип фундамента и выполните расчёт',
                'linkLabel' => 'Все калькуляторы →',
                'linkUrl' => '/kalkulyatory/',
                'cards' => [
                    [
                        'image' => 'assets/src/images/cards/calc-cover-strip.jpg',
                        'title' => 'Ленточный фундамент',
                        'text' => 'Расчёт размеров ленты, объёма бетона и количества материалов',
                        'href' => '/kalkulyatory/fundament/lentochnyj/',
                        'cta' => 'Рассчитать',
                    ],
                    [
                        'image' => 'assets/src/images/cards/calc-cover-pile.jpg',
                        'title' => 'Свайный фундамент',
                        'text' => 'Количество свай, длина, шаг и объём материалов',
                        'href' => '/kalkulyatory/fundament/svajnyj/',
                        'cta' => 'Рассчитать',
                    ],
                    [
                        'image' => 'assets/src/images/cards/calc-cover-slab.jpg',
                        'title' => 'Плитный фундамент',
                        'text' => 'Толщина плиты, объём бетона, арматура и материалы',
                        'href' => '/kalkulyatory/fundament/plitnyj/',
                        'cta' => 'Рассчитать',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/trust', [
                'anchor' => 'hub-choose-guide',
                'titleId' => 'hub-choose-guide-title',
                'themeVariant' => 'bg',
                'title' => 'Как выбрать фундамент?',
                'subtitle' => 'Краткие рекомендации по выбору типа фундамента',
                'items' => [
                    [
                        'icon' => 'measurement',
                        'title' => 'Тип грунта',
                        'text' => 'На слабых и пучинистых грунтах лучше применять свайные или плитные фундаменты.',
                    ],
                    [
                        'icon' => 'info-circle',
                        'title' => 'Уровень грунтовых вод',
                        'text' => 'Высокий УГВ требует утепления, дренажа и использования специальных решений.',
                    ],
                    [
                        'icon' => 'briefcase',
                        'title' => 'Бюджет и сроки',
                        'text' => 'Ленточный — оптимален по цене, плитный — дороже, но быстрее, свайный — компромиссный вариант.',
                    ],
                    [
                        'icon' => 'target',
                        'title' => 'Нагрузка от здания',
                        'text' => 'Чем тяжелее конструкция, тем массивнее и надёжнее должен быть фундамент.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'hub-faq',
                'titleId' => 'hub-faq-title',
                'title' => 'Часто задаваемые вопросы',
                'linkLabel' => 'Все вопросы →',
                'linkUrl' => '/faq/',
                'items' => [
                    [
                        'question' => 'Какой фундамент лучше для частного дома?',
                        'answer' => 'Зависит от грунта, этажности и бюджета. Для лёгких домов на устойчивых грунтах часто выбирают ленточный, на слабых — плитный или свайный.',
                    ],
                    [
                        'question' => 'На какую глубину закладывать фундамент?',
                        'answer' => 'Глубина определяется уровнем промерзания и несущей способностью грунта. Калькулятор даёт ориентир по выбранному типу и параметрам участка.',
                    ],
                    [
                        'question' => 'Нужно ли армировать фундамент?',
                        'answer' => 'Для большинства частных домов армирование обязательно. Схема и диаметр арматуры зависят от типа фундамента и нагрузки.',
                    ],
                    [
                        'question' => 'Какой бетон выбрать для фундамента?',
                        'answer' => 'Обычно используют марки B20–B25 для малоэтажного строительства. Точный выбор зависит от проекта и рекомендаций проектировщика.',
                    ],
                    [
                        'question' => 'Что влияет на стоимость фундамента?',
                        'answer' => 'Тип фундамента, объём бетона и арматуры, глубина заложения, подготовка основания и доставка материалов.',
                    ],
                    [
                        'question' => 'Как часто обновляются данные?',
                        'answer' => 'Мы регулярно обновляем справочные данные и проверяем расчётную логику по мере развития сервиса.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'hub-cta-title',
                'variant' => 'soft',
                'title' => 'Не знаете, с чего начать?',
                'text' => 'Ответьте на несколько вопросов — подберём оптимальный тип фундамента и откроем нужный калькулятор.',
                'buttonLabel' => 'Подобрать фундамент',
                'buttonUrl' => '#hub-calculators',
                'image' => '',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
