<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Methodology_Migration
{
    private const MIGRATION_VERSION = 'methodology-v2';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_methodology_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Methodology page not found.');
        }

        $content = self::build_methodology_page_content();

        // Block attribute JSON escapes < > " & to \uXXXX; wp_update_post() runs
        // wp_unslash() which would strip those backslashes and corrupt the prose
        // HTML. Slash the content so the escaping survives. See PAGES_INTEGRATION_TRACKER.
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

    public static function build_methodology_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'methodology-hero-title',
                'image' => 'assets/src/images/illustrations/hero-methodology.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Методология расчётов'],
                ],
                'title' => 'Методология расчётов',
                'lead' => 'Мы стараемся предоставлять точные и прозрачные расчёты. На этой странице объясняем, как работают калькуляторы и какие данные используются при вычислениях.',
                'columns' => '4',
                'variant' => 'small-title',
                'features' => [
                    ['icon' => 'data-table', 'title' => 'Проверенные формулы и данные'],
                    ['icon' => 'document-add', 'title' => 'Актуальные нормы и стандарты'],
                    ['icon' => 'brain', 'title' => 'Проверенные алгоритмы расчёта'],
                    ['icon' => 'gauge', 'title' => 'Постоянное улучшение точности'],
                ],
            ]),
            Constructly_Migration_Helpers::block(
                'constructly/content-layout',
                [
                    'sidebarTitle' => 'Перейти к калькуляторам',
                    'sidebarText' => 'Проверьте данные на реальных формах и сравните результаты.',
                    'buttonLabel' => 'Все калькуляторы',
                    'buttonUrl' => '/kalkulyatory/',
                ],
                self::prose_blocks()
            ),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'methodology-faq',
                'titleId' => 'methodology-faq-title',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    [
                        'question' => 'Насколько точны расчёты?',
                        'answer' => 'Калькуляторы дают ориентир для предварительной оценки. Точность зависит от исходных данных, условий объекта и выбранной технологии.',
                    ],
                    [
                        'question' => 'Можно ли использовать результат для сметы?',
                        'answer' => 'Да, как предварительную основу. Для рабочей сметы результат нужно сверить с проектом, ценами поставщиков и условиями выполнения работ.',
                    ],
                    [
                        'question' => 'Почему результаты могут отличаться от фактических?',
                        'answer' => 'На практике влияет качество основания, геометрия объекта, отходы, влажность материалов, квалификация исполнителей и другие условия.',
                    ],
                    [
                        'question' => 'Как часто обновляются формулы?',
                        'answer' => 'Мы пересматриваем расчёты при изменении нормативов, справочных коэффициентов или после проверки пользовательских сценариев.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'methodology-cta-title',
                'variant' => 'soft',
                'title' => 'Остались вопросы?',
                'text' => 'Если вы не нашли ответ, свяжитесь с нами — мы поможем разобраться с расчётом.',
                'buttonLabel' => 'Связаться с нами',
                'buttonUrl' => '/kontakty/',
                'image' => 'assets/src/images/illustrations/cta-about-light.jpg',
            ]),
        ];

        return implode("\n\n", $blocks);
    }

    /**
     * The methodology body, seeded as native Gutenberg (core) blocks so it is
     * authored block-by-block in the editor (InnerBlocks of methodology-content).
     * Tables are plain core/table; prose-tables.js wraps them in
     * .bm-prose__table-scroll at runtime. Heading ids feed the auto-generated
     * table of contents (toc.js).
     */
    private static function prose_blocks(): string
    {
        return <<<'HTML'
<!-- wp:heading {"anchor":"methodology-principles"} -->
<h2 class="wp-block-heading" id="methodology-principles">1. Общие принципы</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Расчёты выполняются на основе строительной логики, справочных данных и параметров, которые пользователь указывает в форме. Каждый калькулятор показывает ориентировочный результат и помогает быстро оценить объём материалов.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Мы нормализуем единицы измерения, округляем итог до удобного вида и показываем ограничения, которые важно учитывать перед покупкой материалов.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"methodology-sources"} -->
<h2 class="wp-block-heading" id="methodology-sources">2. Источники данных</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>В расчётах используются актуальные нормативные документы и справочные данные:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>строительные нормы и рекомендации по работам;</li><!-- /wp:list-item --><!-- wp:list-item --><li>типовые размеры материалов и практические коэффициенты расхода;</li><!-- /wp:list-item --><!-- wp:list-item --><li>технические каталоги и паспорта производителей материалов;</li><!-- /wp:list-item --><!-- wp:list-item --><li>проверочные сценарии, основанные на реальных строительных задачах.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"methodology-included"} -->
<h2 class="wp-block-heading" id="methodology-included">3. Что учитывается в расчётах</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>размеры и геометрия объекта;</li><!-- /wp:list-item --><!-- wp:list-item --><li>плотность и расход материалов;</li><!-- /wp:list-item --><!-- wp:list-item --><li>коэффициенты запаса;</li><!-- /wp:list-item --><!-- wp:list-item --><li>технологические допуски;</li><!-- /wp:list-item --><!-- wp:list-item --><li>тип конструкции;</li><!-- /wp:list-item --><!-- wp:list-item --><li>округление до рабочих единиц.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"methodology-excluded"} -->
<h2 class="wp-block-heading" id="methodology-excluded">4. Что не учитывается</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Онлайн-калькуляторы не заменяют инженерный проект и обследование объекта. В базовом расчёте обычно не учитываются:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>индивидуальные особенности грунта и основания;</li><!-- /wp:list-item --><!-- wp:list-item --><li>сложные конструктивные решения и скрытые дефекты объекта;</li><!-- /wp:list-item --><!-- wp:list-item --><li>логистика, потери при хранении и качество выполнения работ;</li><!-- /wp:list-item --><!-- wp:list-item --><li>локальные требования проектировщика, подрядчика или производителя.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"methodology-limits"} -->
<h2 class="wp-block-heading" id="methodology-limits">5. Допущения и ограничения</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Фактическое количество материалов может отличаться в зависимости от условий объекта, качества подготовки основания и выбранной технологии работ. Для предварительной оценки закладывайте запас примерно <strong>±5%</strong>, если калькулятор или инструкция к материалу не требует другого коэффициента.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"methodology-example"} -->
<h2 class="wp-block-heading" id="methodology-example">6. Примеры расчётов</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ниже показан упрощённый пример, как из исходных параметров получается итог.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Пример расчёта объёма бетона</h3>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th scope="col">Параметр</th><th scope="col">Значение</th><th scope="col">Комментарий</th></tr></thead><tbody><tr><td>Длина ленты</td><td>10 м</td><td>Суммарная длина всех участков фундамента</td></tr><tr><td>Ширина ленты</td><td>0.4 м</td><td>Рабочая ширина бетонной части</td></tr><tr><td>Высота ленты</td><td>1.2 м</td><td>Высота от подошвы до верхней отметки</td></tr><tr><td>Объём бетона</td><td>4.8 м³</td><td>10 × 0.4 × 1.2</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading {"anchor":"methodology-accuracy"} -->
<h2 class="wp-block-heading" id="methodology-accuracy">7. Точность результатов</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Итоговые значения округляются так, чтобы ими было удобно пользоваться при закупке и обсуждении работ. Для материалов с упаковкой калькулятор может показывать ориентировочное количество мешков, листов или штук.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>Перед финальной покупкой сверяйте результат с проектной документацией, инструкцией производителя и фактическими условиями объекта.</p></blockquote>
<!-- /wp:quote -->
HTML;
    }
}
