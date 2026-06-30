<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seeds demo knowledge-base content: categories, the /stati/ posts page, and a set
 * of demo articles (one rich, the rest list-level). Idempotent — re-running updates
 * existing items (matched by slug) instead of duplicating.
 */
final class Constructly_Articles_Seed
{
    private const DEMO_FLAG_META = '_bm_demo_post';
    private const POSTS_PAGE_SLUG = 'baza-znaniy';

    /**
     * @var list<array{name:string, slug:string}>
     */
    private const CATEGORIES = [
        ['name' => 'Фундамент', 'slug' => 'fundament'],
        ['name' => 'Стены', 'slug' => 'steny'],
        ['name' => 'Полы', 'slug' => 'poly'],
        ['name' => 'Кровля', 'slug' => 'krovlya'],
        ['name' => 'Отделка', 'slug' => 'otdelka'],
        ['name' => 'Материалы', 'slug' => 'materialy'],
        ['name' => 'Ремонт', 'slug' => 'remont'],
        ['name' => 'Стяжка', 'slug' => 'styazhka'],
        ['name' => 'Плитка', 'slug' => 'plitka'],
    ];

    private const HERO_IMAGES = [
        'assets/src/images/illustrations/hero-article.jpg',
        'assets/src/images/illustrations/hero-archive.jpg',
        'assets/src/images/illustrations/hero-hub-foundation.jpg',
        'assets/src/images/illustrations/cta-about-light.jpg',
    ];

    /**
     * @return array{categories:int, posts_page_id:int, posts:int}
     */
    public static function seed(): array
    {
        $cat_ids = self::ensure_categories();
        $posts_page_id = self::ensure_posts_page();
        self::ensure_menu_links($posts_page_id);
        $posts = self::ensure_posts($cat_ids);

        return [
            'categories' => count($cat_ids),
            'posts_page_id' => $posts_page_id,
            'posts' => $posts,
        ];
    }

    /**
     * Ensures a "База знаний" link to the posts page exists in the primary nav and the
     * footer "Информация" column. Skips locations without an assigned menu, and never
     * duplicates an existing link to the same page/URL.
     */
    private static function ensure_menu_links(int $posts_page_id): void
    {
        if ($posts_page_id <= 0) {
            return;
        }

        $url = (string) get_permalink($posts_page_id);
        $locations = get_nav_menu_locations();

        foreach (['primary', 'footer-column-2'] as $location) {
            if (empty($locations[$location])) {
                continue;
            }

            $menu_id = (int) $locations[$location];
            $items = wp_get_nav_menu_items($menu_id) ?: [];

            $exists = false;
            foreach ($items as $item) {
                if ((int) $item->object_id === $posts_page_id && $item->object === 'page') {
                    $exists = true;
                    break;
                }
                if (untrailingslashit((string) $item->url) === untrailingslashit($url)) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                continue;
            }

            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'База знаний',
                'menu-item-object' => 'page',
                'menu-item-object-id' => $posts_page_id,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            ]);
        }
    }

    /**
     * @return array<string, int> slug => term_id
     */
    private static function ensure_categories(): array
    {
        $ids = [];
        foreach (self::CATEGORIES as $cat) {
            $existing = get_term_by('slug', $cat['slug'], 'category');
            if ($existing instanceof WP_Term) {
                $ids[$cat['slug']] = (int) $existing->term_id;
                continue;
            }

            $result = wp_insert_term($cat['name'], 'category', ['slug' => $cat['slug']]);
            if (!is_wp_error($result)) {
                $ids[$cat['slug']] = (int) $result['term_id'];
            }
        }

        return $ids;
    }

    private static function ensure_posts_page(): int
    {
        $page = get_page_by_path(self::POSTS_PAGE_SLUG);
        if ($page instanceof WP_Post) {
            $page_id = (int) $page->ID;
        } else {
            $page_id = (int) wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'База знаний',
                'post_name' => self::POSTS_PAGE_SLUG,
                'post_content' => '',
            ]);
        }

        if ($page_id > 0) {
            // Serve the posts index at /baza-znaniy/ (front page stays the static homepage).
            update_option('show_on_front', 'page');
            update_option('page_for_posts', $page_id);
        }

        return $page_id;
    }

    /**
     * Resolves an author for the demo posts: the current user, or the first admin.
     */
    private static function author_id(): int
    {
        $current = get_current_user_id();
        if ($current > 0) {
            return $current;
        }

        $admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ids']);

        return !empty($admins) ? (int) $admins[0] : 1;
    }

    /**
     * @param array<string, int> $cat_ids
     */
    private static function ensure_posts(array $cat_ids): int
    {
        $author_id = self::author_id();
        $count = 0;
        foreach (self::posts() as $index => $data) {
            $existing = get_page_by_path($data['slug'], OBJECT, 'post');

            $postarr = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => $data['title'],
                'post_name' => $data['slug'],
                'post_excerpt' => $data['excerpt'],
                'post_content' => $data['content'],
                'post_author' => $author_id,
                'post_date' => $data['date'] . ' 10:00:00',
                'edit_date' => true,
            ];

            if ($existing instanceof WP_Post) {
                $postarr['ID'] = (int) $existing->ID;
                $post_id = (int) wp_update_post(wp_slash($postarr));
            } else {
                $post_id = (int) wp_insert_post(wp_slash($postarr));
            }

            if ($post_id <= 0) {
                continue;
            }
            $count++;

            update_post_meta($post_id, self::DEMO_FLAG_META, '1');
            update_post_meta($post_id, BM_POST_VIEWS_META, (int) $data['views']);

            $cat_slug = $data['category'];
            if (isset($cat_ids[$cat_slug])) {
                wp_set_post_categories($post_id, [$cat_ids[$cat_slug]], false);
            }

            if (!has_post_thumbnail($post_id)) {
                $img = self::HERO_IMAGES[$index % count(self::HERO_IMAGES)];
                $attachment_id = Constructly_Migration_Helpers::sideload_theme_image($img);
                if ($attachment_id > 0) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
        }

        return $count;
    }

    /**
     * @return list<array{slug:string, title:string, category:string, excerpt:string, views:int, date:string, content:string}>
     */
    private static function posts(): array
    {
        $simple = static function (string $lead, array $points): string {
            $items = '';
            foreach ($points as $p) {
                $items .= '<!-- wp:list-item --><li>' . $p . '</li><!-- /wp:list-item -->';
            }

            return "<!-- wp:paragraph -->\n<p>{$lead}</p>\n<!-- /wp:paragraph -->\n\n"
                . "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$items}</ul>\n<!-- /wp:list -->";
        };

        return [
            [
                'slug' => 'kak-vybrat-tip-fundamenta',
                'title' => 'Как выбрать тип фундамента для частного дома',
                'category' => 'fundament',
                'views' => 5432,
                'date' => '2024-04-15',
                'excerpt' => 'Правильный выбор фундамента влияет на долговечность дома, бюджет строительства и безопасность конструкции. Разбираем основные типы и критерии подбора.',
                'content' => self::rich_article_content(),
            ],
            [
                'slug' => 'armirovanie-fundamenta-rukovodstvo',
                'title' => 'Армирование фундамента: полное руководство',
                'category' => 'fundament',
                'views' => 3120,
                'date' => '2024-04-12',
                'excerpt' => 'Схемы армирования, выбор арматуры и правила вязки каркаса для надёжного основания.',
                'content' => $simple('Армирование принимает на себя растягивающие нагрузки и удерживает бетон от трещин.', ['Выбор диаметра арматуры', 'Шаг и нахлёст стержней', 'Защитный слой бетона']),
            ],
            [
                'slug' => 'tolshchina-styazhki-pola',
                'title' => 'Толщина стяжки пола: как не ошибиться',
                'category' => 'styazhka',
                'views' => 2890,
                'date' => '2024-04-10',
                'excerpt' => 'От чего зависит толщина стяжки и как рассчитать оптимальный слой под разные основания.',
                'content' => $simple('Толщина зависит от типа основания, нагрузки и наличия коммуникаций в полу.', ['Минимальная толщина по основанию', 'Стяжка по утеплителю', 'Армирование стяжки']),
            ],
            [
                'slug' => 'ukladka-plitki-na-pol',
                'title' => 'Укладка плитки на пол: пошаговая инструкция',
                'category' => 'plitka',
                'views' => 4210,
                'date' => '2024-03-28',
                'excerpt' => 'Подготовка основания, выбор клея и технология укладки плитки без типичных ошибок.',
                'content' => $simple('Качество укладки зависит от ровности основания и правильного клея.', ['Подготовка и грунтовка основания', 'Выбор клеевой смеси', 'Затирка швов']),
            ],
            [
                'slug' => 'kirpichnaya-kladka-raschet',
                'title' => 'Кирпичная кладка: расчёт материалов на стену',
                'category' => 'steny',
                'views' => 1980,
                'date' => '2024-03-15',
                'excerpt' => 'Как посчитать количество кирпича и раствора на стену с учётом толщины и проёмов.',
                'content' => $simple('Расход зависит от типа кладки, размера кирпича и толщины шва.', ['Площадь стен за вычетом проёмов', 'Тип кладки и толщина', 'Запас на бой и подрезку']),
            ],
            [
                'slug' => 's-chego-nachat-remont-v-novostrojke',
                'title' => 'С чего начать ремонт в новостройке',
                'category' => 'remont',
                'views' => 3670,
                'date' => '2024-02-20',
                'excerpt' => 'Порядок работ, на чём не стоит экономить и как спланировать бюджет ремонта.',
                'content' => $simple('Ремонт начинают с черновых работ и инженерных систем.', ['Замеры и проект', 'Черновые работы', 'Чистовая отделка']),
            ],
            [
                'slug' => 'gidroizolyaciya-fundamenta',
                'title' => 'Гидроизоляция фундамента: способы и материалы',
                'category' => 'fundament',
                'views' => 1450,
                'date' => '2024-02-05',
                'excerpt' => 'Обзор способов гидроизоляции и как защитить основание от влаги и пучения.',
                'content' => $simple('Гидроизоляция защищает бетон и арматуру от разрушения влагой.', ['Обмазочная изоляция', 'Рулонная изоляция', 'Дренаж по периметру']),
            ],
            [
                'slug' => 'vybor-krovelnogo-materiala',
                'title' => 'Выбор кровельного материала: что учесть',
                'category' => 'krovlya',
                'views' => 1120,
                'date' => '2024-01-22',
                'excerpt' => 'Сравнение популярных кровельных материалов по цене, сроку службы и монтажу.',
                'content' => $simple('Материал выбирают по уклону кровли, бюджету и сроку службы.', ['Металлочерепица', 'Мягкая кровля', 'Профнастил']),
            ],
            [
                'slug' => 'shtukaturka-sten-svoimi-rukami',
                'title' => 'Штукатурка стен своими руками: основы',
                'category' => 'otdelka',
                'views' => 2310,
                'date' => '2024-01-10',
                'excerpt' => 'Подготовка стен, выбор смеси и техника нанесения штукатурки для ровных стен.',
                'content' => $simple('Ровность стен определяется подготовкой и установкой маяков.', ['Грунтовка основания', 'Установка маяков', 'Нанесение и затирка']),
            ],
        ];
    }

    /**
     * Full body for the flagship demo article, matching the article design reference.
     * Prose is core blocks; the styled components are registered constructly blocks
     * (criteria / foundation-cards / mistakes / info-block) and the shared faq block —
     * no raw Custom HTML. h2 headings (incl. the faq title) feed the article TOC.
     */
    private static function rich_article_content(): string
    {
        $h2 = static fn (string $id, string $title): string =>
            "<!-- wp:heading {\"anchor\":\"{$id}\"} -->\n<h2 class=\"wp-block-heading\" id=\"{$id}\">{$title}</h2>\n<!-- /wp:heading -->";
        $p = static fn (string $text): string =>
            "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->";
        $table = static fn (string $figure): string =>
            "<!-- wp:table -->\n{$figure}\n<!-- /wp:table -->";

        $blocks = [
            $h2('section-1', 'От чего зависит выбор фундамента'),
            $p('Тип фундамента выбирают с учётом грунта, нагрузки от здания, рельефа участка и бюджета. Ниже — четыре ключевых фактора, которые стоит учесть до проектирования.'),
            Constructly_Migration_Helpers::block('constructly/article-criteria', [
                'columns' => '4',
                'items' => [
                    ['icon' => 'measurement', 'title' => 'Тип грунта', 'text' => 'Несущая способность, уровень грунтовых вод, пучинистость.'],
                    ['icon' => 'briefcase', 'title' => 'Нагрузка от дома', 'text' => 'Материал стен, этажность, площадь и вес перекрытий.'],
                    ['icon' => 'interface', 'title' => 'Рельеф участка', 'text' => 'Уклон, перепады высот, необходимость подпорных стен.'],
                    ['icon' => 'calculator', 'title' => 'Бюджет строительства', 'text' => 'Стоимость материалов, работ и сроки реализации.'],
                ],
            ]),

            $h2('section-2', 'Основные типы фундаментов'),
            $p('Для частного домостроения чаще всего используют ленточный, свайный и плитный фундамент. Кратко — особенности каждого типа.'),
            Constructly_Migration_Helpers::block('constructly/foundation-cards', [
                'cards' => [
                    ['image' => 'assets/src/images/cards/calc-cover-strip.jpg', 'title' => 'Ленточный', 'text' => 'Лента по контуру несущих стен. Оптимален для домов из кирпича и газобетона на устойчивых грунтах.'],
                    ['image' => 'assets/src/images/cards/calc-cover-pile.jpg', 'title' => 'Свайный', 'text' => 'Передаёт нагрузку на плотные слои грунта. Подходит для слабых и пучинистых грунтов, высокого УГВ.'],
                    ['image' => 'assets/src/images/cards/calc-cover-slab.jpg', 'title' => 'Плитный', 'text' => 'Монолитная плита под всем зданием. Надёжен при неравномерной нагрузке и сложных грунтах.'],
                ],
            ]),

            $h2('section-3', 'Сравнение фундаментов'),
            $table('<figure class="wp-block-table"><table><thead><tr><th scope="col">Тип фундамента</th><th scope="col">Несущая способность</th><th scope="col">Устойчивость к пучению</th><th scope="col">Стоимость</th><th scope="col">Сроки строительства</th></tr></thead><tbody><tr><td>Ленточный</td><td>Высокая</td><td>Средняя</td><td>Средняя</td><td>2–4 недели</td></tr><tr><td>Свайный</td><td>Высокая</td><td>Высокая</td><td>Выше средней</td><td>1–3 недели</td></tr><tr><td>Плитный</td><td>Очень высокая</td><td>Высокая</td><td>Высокая</td><td>3–5 недель</td></tr><tr><td>Столбчатый</td><td>Средняя</td><td>Низкая</td><td>Низкая</td><td>1–2 недели</td></tr></tbody></table></figure>'),
            Constructly_Migration_Helpers::block('constructly/info-block', [
                'text' => 'Перед окончательным выбором закажите геологию участка и согласуйте решение с проектировщиком — это снизит риск перерасхода и переделок.',
            ]),

            $h2('section-4', 'Какой фундамент выбрать для разных условий'),
            $table('<figure class="wp-block-table"><table><thead><tr><th scope="col">Условия</th><th scope="col">Рекомендуемый тип</th></tr></thead><tbody><tr><td>Слабые грунты</td><td>Свайный или плитный</td></tr><tr><td>Высокий уровень грунтовых вод</td><td>Свайный</td></tr><tr><td>Лёгкий каркасный дом</td><td>Столбчатый</td></tr><tr><td>Кирпич / газобетон, 1–2 этажа</td><td>Ленточный или плитный</td></tr></tbody></table></figure>'),

            $h2('section-5', 'Частые ошибки при выборе фундамента'),
            Constructly_Migration_Helpers::block('constructly/article-mistakes', [
                'items' => [
                    ['text' => 'Выбор типа фундамента без геологии участка'],
                    ['text' => 'Недооценка пучинистости грунта и глубины промерзания'],
                    ['text' => 'Экономия на глубине заложения и армировании'],
                    ['text' => 'Игнорирование уровня грунтовых вод при проектировании'],
                ],
            ]),

            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'article-faq',
                'titleId' => 'section-6',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Можно ли менять тип фундамента после начала строительства?', 'answer' => 'Практически нет — тип фундамента закладывают на этапе проекта. Смена решения потребует нового расчёта и часто полной переделки основания.'],
                    ['question' => 'Нужна ли геология участка обязательно?', 'answer' => 'Для надёжного выбора — да. Без данных о грунте расчёт остаётся ориентировочным и может не учесть пучение или просадку.'],
                    ['question' => 'Какой фундамент дешевле для небольшого дома?', 'answer' => 'Чаще всего столбчатый — при условии, что грунт позволяет такую схему. На слабых грунтах экономия оборачивается рисками.'],
                    ['question' => 'Чем отличается плитный фундамент от ленточного?', 'answer' => 'Ленточный передаёт нагрузку по контуру стен; плитный — по всей площади основания, что выгодно при неравномерной нагрузке и слабых грунтах.'],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
