<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_About_Migration
{
    private const MIGRATION_VERSION = 'about-v13';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_about_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('About page not found.');
        }

        $content = self::build_about_page_content();

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

    public static function build_about_page_content(): string
    {
        $blocks = [
            // 1. Page hero
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'about-hero-title',
                'image' => 'assets/src/images/illustrations/hero-about.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'О проекте'],
                ],
                'title' => 'О проекте Brigmaster',
                'lead' => 'Молодой независимый сервис строительных калькуляторов. Честный инструмент для частных застройщиков — без регистрации, без рекламных «умножителей».',
                'paragraphs' => [
                    ['text' => 'Brigmaster — инструмент для ориентировочного расчёта расхода строительных материалов. Каждый калькулятор опирается на актуальные государственные нормативы, формулы сверены с действующими нормами и проверяются на расчётном движке.'],
                    ['text' => 'Проект независимый, без внешнего финансирования и корпоративных обязательств. Здесь нет отдела продаж — только честная арифметика и открытая методика.'],
                ],
            ]),

            // 2. Feature-cards «Миссия»
            Constructly_Migration_Helpers::block('constructly/feature-cards', [
                'titleId' => 'about-mission-title',
                'sectionTitle' => 'Миссия',
                'columns' => '3',
                'items' => [
                    [
                        'icon' => 'shield-check',
                        'title' => 'Честность',
                        'text' => 'Результат расчёта — ориентир, а не гарантия. Мы прямо пишем об ограничениях на каждой странице.',
                    ],
                    [
                        'icon' => 'calculator',
                        'title' => 'Нормативная база',
                        'text' => 'Формулы и коэффициенты берём из действующих ГОСТ и СП, а не придумываем.',
                    ],
                    [
                        'icon' => 'target',
                        'title' => 'Доступность',
                        'text' => 'Калькуляторы работают без регистрации. Цель — помочь, а не собрать базу email-адресов.',
                    ],
                ],
            ]),

            // 3. Text-media «Как мы считаем»
            Constructly_Migration_Helpers::block('constructly/text-media', [
                'titleId' => 'about-methodology-title',
                'title' => 'Как мы считаем',
                'mediaPosition' => 'right',
                'image' => 'assets/src/images/illustrations/about-methodology.jpg',
                'imageAlt' => 'Схема методики расчёта по нормативным документам',
                'paragraphs' => [
                    ['text' => 'Каждый калькулятор строится по одной схеме: нормативный документ → формула → ручная проверка → публикация. Если норматив обновляется, калькулятор обновляется вместе с ним.'],
                    ['text' => 'Расчёт выдаёт ориентировочное значение. Реальный расход зависит от квалификации исполнителя, качества материала, условий укладки и других факторов, которые калькулятор учесть не может. Перед закупкой сверяйте результат с проектной документацией или сметой специалиста.'],
                ],
            ]),

            // 4. Norms table
            Constructly_Migration_Helpers::block('core/group', [
                'className' => 'bm-section bm-norms-table-section',
            ], self::norms_table_block()),

            // 5. «Важно знать» notice-card
            self::notice_card_block(),

            // 6. «О проекте» prose
            self::about_prose_block(),

            // 7. Feature-cards «Планы развития»
            Constructly_Migration_Helpers::block('constructly/feature-cards', [
                'titleId' => 'about-plans-title',
                'sectionTitle' => 'Планы развития',
                'columns' => '3',
                'items' => [
                    [
                        'icon' => 'calculator',
                        'title' => 'Новые калькуляторы',
                        'text' => 'Расширяем охват: кровля, фундамент, инженерные сети. Приоритет — по запросам пользователей.',
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Актуализация нормативов',
                        'text' => 'Следим за изменениями ГОСТ и СП. При выходе новой редакции нормативного документа калькулятор обновляется.',
                    ],
                    [
                        'icon' => 'interface',
                        'title' => 'Личный кабинет',
                        'text' => 'Планируем личный кабинет: сохранение и история расчётов, сравнение вариантов сметы, избранные калькуляторы и быстрый доступ к прошлым проектам.',
                    ],
                ],
            ]),

            // 8. Final CTA
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'about-cta-title',
                'variant' => 'soft',
                'title' => 'Нашли ошибку или есть идея?',
                'text' => 'Brigmaster — молодой независимый проект. Список калькуляторов постоянно растёт, и любая обратная связь помогает развитию.',
                'buttonLabel' => 'Написать на email',
                'buttonUrl' => 'mailto:info@brigmaster.ru',
                'image' => 'assets/src/images/illustrations/cta-about-light.jpg',
            ]),
        ];

        return implode("\n\n", $blocks);
    }

    private static function norms_table_block(): string
    {
        return <<<'HTML'
<!-- wp:group {"className":"bm-container"} -->
<div class="wp-block-group bm-container">

<!-- wp:html -->
<div class="bm-section-toolbar">
  <div class="bm-section-toolbar__main">
    <h2 id="about-norms-title" class="bm-section-toolbar__title">Какие нормативы используем</h2>
  </div>
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="bm-norms-table-wrap">
<table class="bm-norms-table">
  <thead>
    <tr>
      <th scope="col">Норматив</th>
      <th scope="col">Где применяется</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>СП 22.13330.2016</td>
      <td>«Основания зданий и сооружений». Норматив для расчёта фундаментов и несущих оснований.</td>
    </tr>
    <tr>
      <td>СП 29.13330</td>
      <td>«Полы». Нормы устройства конструкций полов, применяется в калькуляторе стяжки.</td>
    </tr>
    <tr>
      <td>ГОСТ 530-2012</td>
      <td>Кирпич и камень керамические. Технические условия для расчёта кладочных работ.</td>
    </tr>
    <tr>
      <td>ГОСТ 6787-2020</td>
      <td>Плитки керамические для полов. Норматив расчёта расхода плитки и клея.</td>
    </tr>
    <tr>
      <td>ГОСТ Р 6266-2022</td>
      <td>Листы гипсокартонные. Технические условия для калькулятора гипсокартона.</td>
    </tr>
  </tbody>
</table>
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->
HTML;
    }

    private static function notice_card_block(): string
    {
        return <<<'HTML'
<!-- wp:html -->
<section class="bm-section bm-section--tight">
  <div class="bm-container">
    <div class="bm-notice-card" role="note">
      <div class="bm-notice-card__icon" aria-hidden="true">
        <svg class="bm-icon bm-notice-card__icon-svg">
          <use href="#bm-icon-shield-check"></use>
        </svg>
      </div>
      <div>
        <p class="bm-notice-card__title">Важно знать</p>
        <p class="bm-notice-card__text">Калькуляторы Brigmaster дают <strong>ориентировочный</strong> расход материалов. Результаты не являются проектной документацией. Перед закупкой сверяйте данные с реальным проектом или консультируйтесь со специалистом.</p>
      </div>
    </div>
  </div>
</section>
<!-- /wp:html -->
HTML;
    }

    private static function about_prose_block(): string
    {
        return <<<'HTML'
<!-- wp:html -->
<section class="bm-section bm-section--tight">
  <div class="bm-container">
    <div class="bm-section-toolbar">
      <div class="bm-section-toolbar__main">
        <h2 id="about-project" class="bm-section-toolbar__title">О проекте</h2>
      </div>
    </div>
    <div class="bm-prose">
      <p>Brigmaster — независимый онлайн-сервис строительных калькуляторов. Он создан для тех, кто планирует стройку или ремонт своими силами: частных застройщиков, мастеров небольших бригад и владельцев, которым нужно заранее оценить объём закупки или проверить смету подрядчика.</p>
      <p>В отличие от калькуляторов, которые просто умножают площадь на условный коэффициент, каждый инструмент Brigmaster привязан к конкретному действующему нормативу и считается на собственном расчётном движке. Мы показываем, какие параметры заложены в расчёт, — чтобы итог можно было проследить и перепроверить, а не принимать на веру.</p>
      <p>Сегодня сервис охватывает ключевые этапы частной стройки и отделки: фундаменты — ленточный, плитный и свайный, — стяжку пола, кирпичную кладку, облицовочную плитку и листы гипсокартона. Под каждый материал отдельный калькулятор со своими нормами расхода и понятной методикой.</p>
      <p>Методика расчётов открыта: на странице <a href="/metodologiya/" class="bm-link">«Методология»</a> собраны нормативы и принципы, по которым работают калькуляторы — от исходной формулы до источника. Так у каждого результата есть проверяемое основание, а не «магическое» число.</p>
    </div>
  </div>
</section>
<!-- /wp:html -->
HTML;
    }
}
