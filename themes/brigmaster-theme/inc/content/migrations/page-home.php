<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Homepage_Migration
{
    private const MIGRATION_VERSION = 'homepage-v9';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_homepage(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Homepage page not found.');
        }

        $content = self::build_homepage_content();

        // Block attribute JSON escapes < > " & to \uXXXX; wp_update_post() runs
        // wp_unslash() which would strip those backslashes and corrupt the markup.
        // Slash the content so the escaping survives.
        wp_update_post([
            'ID' => $page_id,
            'post_content' => wp_slash($content),
        ]);

        update_post_meta($page_id, 'rank_math_title', 'Строительные калькуляторы онлайн Brigmaster');
        update_post_meta(
            $page_id,
            'rank_math_description',
            'Онлайн-калькуляторы Brigmaster для фундамента, кирпича, стяжки, гипсокартона и плитки. Предварительная оценка материалов без обещания точной сметы.'
        );
        update_post_meta($page_id, '_constructly_content_migration', self::MIGRATION_VERSION);

        return [
            'post_id' => $page_id,
            'content' => $content,
            'migration' => self::MIGRATION_VERSION,
        ];
    }

    public static function build_homepage_content(): string
    {
        $links = Constructly_Migration_Helpers::resolve_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/home-hero', [
                'title' => 'Онлайн-калькуляторы материалов для строительства и ремонта',
                'lead' => 'Точные ориентировочные расчёты по проверенным формулам. Понятная логика, прозрачные допущения и результаты, которым можно доверять.',
                'primaryLabel' => 'Открыть калькуляторы',
                'primaryUrl' => '#calculators',
                'secondaryLabel' => 'Как это работает?',
                'secondaryUrl' => '#how-it-works',
                'features' => [
                    [
                        'icon' => 'clock-check',
                        'title' => 'Быстро',
                        'text' => 'Результат за <br> несколько минут',
                    ],
                    [
                        'icon' => 'interface',
                        'title' => 'Понятно',
                        'text' => 'Простой интерфейс <br> без лишнего',
                    ],
                    [
                        'icon' => 'briefcase',
                        'title' => 'Практично',
                        'text' => 'Для частных и <br>проф. задач',
                    ],
                ],
                'demo' => [
                    'ariaLabel' => 'Пример расчёта: стяжка пола',
                    'title' => 'Пример расчёта: Стяжка пола',
                    'fields' => [
                        [
                            'label' => 'Площадь помещения',
                            'value' => '45',
                            'unit' => 'м²',
                        ],
                        [
                            'label' => 'Толщина стяжки',
                            'value' => '50',
                            'unit' => 'мм',
                        ],
                        [
                            'label' => 'Тип стяжки',
                            'value' => 'Цементно-песчаная',
                            'select' => true,
                        ],
                    ],
                    'moreLabel' => 'Дополнительные параметры',
                    'resultLabel' => 'Результат',
                    'resultSummary' => 'Объём стяжки',
                    'resultValue' => '≈2.25 м³',
                    'resultItems' => [
                        ['name' => 'Цемент', 'amount' => '≈1297 кг'],
                        ['name' => 'Песок', 'amount' => '≈2.0 м³'],
                        ['name' => 'Вода', 'amount' => '≈648 л'],
                    ],
                ],
                'note' => 'Используются проверенные строительные нормы и справочные данные',
            ]),
            Constructly_Migration_Helpers::block('constructly/popular-calculators', [
                'anchor' => 'calculators',
                'title' => 'Быстрый доступ к калькуляторам',
                'subtitle' => '',
                'linkLabel' => 'Все калькуляторы →',
                'linkUrl' => home_url('/kalkulyatory/'),
                'cards' => [
                    [
                        'title' => 'Фундамент',
                        'description' => 'Расчёт объёма бетона, опалубки, арматуры и материалов.',
                        'buttonLabel' => 'Рассчитать',
                        'buttonUrl' => $links['foundation'],
                        'icon' => 'measurement',
                        'image' => 'assets/src/images/calculators/icon-foundation.jpg',
                    ],
                    [
                        'title' => 'Стяжка пола',
                        'description' => 'Цементно-песчаная стяжка, наливные полы и другие типы.',
                        'buttonLabel' => 'Рассчитать',
                        'buttonUrl' => $links['screed'],
                        'icon' => 'data-table',
                        'image' => 'assets/src/images/calculators/icon-screed.jpg',
                    ],
                    [
                        'title' => 'Кирпич',
                        'description' => 'Количество кирпича и раствора для кладки стен и перегородок.',
                        'buttonLabel' => 'Рассчитать',
                        'buttonUrl' => $links['brick'],
                        'icon' => 'calculator',
                        'image' => 'assets/src/images/calculators/icon-brick.jpg',
                    ],
                    [
                        'title' => 'Плитка',
                        'description' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.',
                        'buttonLabel' => 'Рассчитать',
                        'buttonUrl' => $links['tile'],
                        'icon' => 'interface',
                        'image' => 'assets/src/images/calculators/icon-tile.jpg',
                    ],
                    [
                        'title' => 'Гипсокартон',
                        'description' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.',
                        'buttonLabel' => 'Рассчитать',
                        'buttonUrl' => $links['drywall'],
                        'icon' => 'document-plus',
                        'image' => 'assets/src/images/calculators/icon-drywall.jpg',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/tasks', [
                'title' => 'Решите свою задачу',
                'subtitle' => 'Выберите сценарий — подберём нужные расчёты и подскажем порядок работ.',
                'items' => [
                    [
                        'title' => 'Строю дом',
                        'text' => 'Рассчитайте материалы для фундамента, стен, перекрытий и кровли.',
                        'url' => '#calculators',
                        'label' => 'Подобрать расчёты',
                        'image' => 'assets/src/images/illustrations/task-build-house.jpg',
                    ],
                    [
                        'title' => 'Делаю ремонт в квартире',
                        'text' => 'Расчёт стяжки, штукатурки, плитки, отделки и электромонтажа.',
                        'url' => '#calculators',
                        'label' => 'Подобрать расчёты',
                        'image' => 'assets/src/images/illustrations/task-renovate-apartment.jpg',
                    ],
                    [
                        'title' => 'Ремонт в комнате',
                        'text' => 'Рассчитайте материалы для пола, стен, потолка и отделки.',
                        'url' => '#calculators',
                        'label' => 'Подобрать расчёты',
                        'image' => 'assets/src/images/illustrations/task-renovate-room.jpg',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/how-it-works', [
                'anchor' => 'how-it-works',
                'title' => 'Как это работает',
                'steps' => [
                    [
                        'title' => 'Выбираете калькулятор',
                        'text' => 'Выбираете нужный раздел и тип расчёта.',
                        'icon' => 'calculator',
                    ],
                    [
                        'title' => 'Вводите параметры',
                        'text' => 'Укажите размеры, материалы и другие исходные данные.',
                        'icon' => 'document-add',
                    ],
                    [
                        'title' => 'Получаете результат',
                        'text' => 'Сервис мгновенно выполнит расчёт и покажет результат.',
                        'icon' => 'check-circle',
                    ],
                    [
                        'title' => 'Используйте в работе',
                        'text' => 'Сохраните, распечатайте или экспортируйте расчёт.',
                        'icon' => 'document-plus',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/trust', [
                'title' => 'Почему нам можно доверять',
                'items' => [
                    [
                        'title' => 'Проверенные формулы',
                        'text' => 'Расчёты основаны на строительных нормах и справочных данных.',
                        'icon' => 'book',
                    ],
                    [
                        'title' => 'Прозрачность',
                        'text' => 'Показываем, что учитывается и какие допущения используются.',
                        'icon' => 'shield-check',
                    ],
                    [
                        'title' => 'Практичность',
                        'text' => 'Сервис создан строителями для строителей и частных пользователей.',
                        'icon' => 'target',
                    ],
                    [
                        'title' => 'Постоянное развитие',
                        'text' => 'Мы обновляем данные и добавляем новые расчёты.',
                        'icon' => 'reload',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/articles', [
                'title' => 'Полезные статьи',
                'linkLabel' => 'Все статьи →',
                'linkUrl' => home_url('/baza-znaniy/'),
                'items' => [
                    [
                        'title' => 'Как выбрать тип фундамента для частного дома',
                        'text' => 'Разбираем основные типы фундаментов и критерии выбора под разные условия.',
                        'url' => home_url('/baza-znaniy/'),
                        'image' => 'assets/src/images/illustrations/article-cover-1.svg',
                        'imageAlt' => '',
                        'tag' => 'Фундамент',
                        'readTime' => '12 мин',
                        'date' => '15.04.2024',
                    ],
                    [
                        'title' => 'Толщина стяжки пола: какая должна быть и почему',
                        'text' => 'Рекомендации по выбору толщины для разных видов оснований.',
                        'url' => home_url('/baza-znaniy/'),
                        'image' => 'assets/src/images/illustrations/article-cover-2.svg',
                        'imageAlt' => '',
                        'tag' => 'Стяжка',
                        'readTime' => '8 мин',
                        'date' => '10.04.2024',
                    ],
                    [
                        'title' => 'Как рассчитать плитку на пол и стены без ошибок',
                        'text' => 'Пошаговая инструкция и примеры расчётов с учётом подрезки.',
                        'url' => home_url('/baza-znaniy/'),
                        'image' => 'assets/src/images/illustrations/article-cover-3.svg',
                        'imageAlt' => '',
                        'tag' => 'Плитка',
                        'readTime' => '10 мин',
                        'date' => '05.04.2024',
                    ],
                    [
                        'title' => 'С чего начать ремонт в новостройке',
                        'text' => 'План работ, этапы и полезные советы для новичков.',
                        'url' => home_url('/baza-znaniy/'),
                        'image' => 'assets/src/images/illustrations/article-cover-4.svg',
                        'imageAlt' => '',
                        'tag' => 'Ремонт',
                        'readTime' => '15 мин',
                        'date' => '01.04.2024',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'title' => 'Часто задаваемые вопросы',
                'linkLabel' => 'Все вопросы →',
                'linkUrl' => home_url('/faq/'),
                'items' => [
                    [
                        'question' => 'Насколько точны расчёты?',
                        'answer' => 'Расчёты дают ориентировочную оценку по проверенным формулам. Для закупки и проектных решений результат нужно уточнять под объект.',
                    ],
                    [
                        'question' => 'Учитываются ли потери материалов?',
                        'answer' => 'В калькуляторах заложены типовые коэффициенты запаса там, где это предусмотрено формулой. Итоговый запас зависит от технологии и условий на объекте.',
                    ],
                    [
                        'question' => 'Можно ли сохранить и распечатать расчёт?',
                        'answer' => 'Да, результат можно сохранить или распечатать прямо из браузера после расчёта.',
                    ],
                    [
                        'question' => 'Какие единицы измерения используются?',
                        'answer' => 'Используются метрические единицы: метры, квадратные и кубические метры, килограммы, литры.',
                    ],
                    [
                        'question' => 'Нужна ли регистрация для использования?',
                        'answer' => 'Нет, калькуляторы доступны без регистрации — достаточно открыть нужный раздел и ввести параметры.',
                    ],
                    [
                        'question' => 'Как часто обновляются данные?',
                        'answer' => 'Мы регулярно обновляем справочные данные и проверяем расчётную логику по мере развития сервиса.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'title' => 'Начните расчёт прямо сейчас',
                'text' => 'Выберите нужный калькулятор и получите результат за несколько минут.',
                'buttonLabel' => 'Открыть калькуляторы',
                'buttonUrl' => '#calculators',
                'image' => 'assets/src/images/illustrations/cta-house-dark.jpg',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
