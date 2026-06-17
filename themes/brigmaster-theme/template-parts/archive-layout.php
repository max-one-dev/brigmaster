<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Knowledge-base archive layout (/baza-znaniy/): static hero + two-column layout with the
 * post grid, a functional GET toolbar (search / topic / sort) and a sidebar
 * (topics, "suggest a topic" CTA, popular posts). Shared by home.php and archive.php.
 */
global $wp_query;

$posts_page_url = ($pid = (int) get_option('page_for_posts')) > 0 ? get_permalink($pid) : home_url('/baza-znaniy/');
$current_topic = isset($_GET['topic']) ? sanitize_title(wp_unslash((string) $_GET['topic'])) : '';
$current_sort = isset($_GET['sort']) ? sanitize_key(wp_unslash((string) $_GET['sort'])) : 'newest';
$current_search = get_search_query();
$found = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;

$hero_features = [
    ['icon' => 'shield-check', 'title' => 'Проверенная информация', 'text' => 'от экспертов отрасли'],
    ['icon' => 'briefcase', 'title' => 'Практические советы', 'text' => 'и рекомендации'],
    ['icon' => 'interface', 'title' => 'Пошаговые инструкции', 'text' => 'и примеры'],
    ['icon' => 'reload', 'title' => 'Актуальные данные', 'text' => 'и нормативы'],
];

$topics = get_categories(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC']);
$popular = bm_popular_posts(5);

$sort_options = [
    'newest' => 'Сортировка: сначала новые',
    'oldest' => 'Сначала старые',
    'popular' => 'По популярности',
];
?>
<main id="main" class="site-main site-main--archive" role="main">
    <section class="bm-page-hero" aria-labelledby="archive-hero-title">
        <img class="bm-page-hero__image" src="<?php echo esc_url(constructly_esc_block_image_src('assets/src/images/illustrations/hero-archive.jpg')); ?>" alt="" loading="eager" decoding="async">
        <div class="bm-container bm-page-hero__container">
            <nav class="bm-breadcrumbs bm-breadcrumbs--chevron bm-breadcrumbs--link-brand bm-page-hero__breadcrumbs" aria-label="Хлебные крошки">
                <ol class="bm-breadcrumbs__list">
                    <li class="bm-breadcrumbs__item"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a></li>
                    <li class="bm-breadcrumbs__item" aria-current="page">База знаний</li>
                </ol>
            </nav>
            <div class="bm-hero">
                <h1 id="archive-hero-title" class="bm-hero__title">База знаний</h1>
                <p class="bm-hero__lead">Полезные статьи, инструкции и рекомендации по строительству и ремонту — от выбора материалов до практических расчётов.</p>
                <ul class="bm-hero__features bm-hero__features--cols-4">
                    <?php foreach ($hero_features as $f) : ?>
                        <li class="bm-hero__feature">
                            <svg class="bm-icon bm-hero__feature-icon" aria-hidden="true"><use href="#bm-icon-<?php echo esc_attr($f['icon']); ?>"></use></svg>
                            <span class="bm-hero__feature-copy">
                                <span class="bm-hero__feature-title"><?php echo esc_html($f['title']); ?></span>
                                <span class="bm-hero__feature-text"><?php echo esc_html($f['text']); ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="bm-section" aria-labelledby="archive-list-title">
        <div class="bm-container">
            <h2 id="archive-list-title" class="bm-visually-hidden">Список статей</h2>
            <div class="bm-content-layout bm-content-layout--with-right-sidebar">
                <div class="bm-content-layout__main">
                    <form class="bm-archive-toolbar" method="get" action="<?php echo esc_url($posts_page_url); ?>" role="search">
                        <div class="bm-archive-toolbar__filters">
                            <label class="bm-search">
                                <span class="bm-visually-hidden">Поиск по статьям</span>
                                <svg class="bm-icon bm-search__icon" aria-hidden="true"><use href="#bm-icon-search"></use></svg>
                                <input class="bm-search__input" type="search" name="s" value="<?php echo esc_attr($current_search); ?>" placeholder="Поиск по статьям" autocomplete="off">
                            </label>
                            <div data-bm-component="select">
                                <label class="bm-visually-hidden" for="archive-topic">Тема</label>
                                <select id="archive-topic" name="topic" onchange="this.form.submit()">
                                    <option value=""<?php selected($current_topic, ''); ?>>Все темы</option>
                                    <?php foreach ($topics as $topic) : ?>
                                        <option value="<?php echo esc_attr($topic->slug); ?>"<?php selected($current_topic, $topic->slug); ?>><?php echo esc_html($topic->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="bm-archive-toolbar__sort" data-bm-component="select">
                            <label class="bm-visually-hidden" for="archive-sort">Сортировка</label>
                            <select id="archive-sort" name="sort" onchange="this.form.submit()">
                                <?php foreach ($sort_options as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>"<?php selected($current_sort, $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="bm-visually-hidden">Показать</button>
                    </form>

                    <?php if (have_posts()) : ?>
                        <p class="bm-archive-found"><?php echo esc_html(bm_archive_found_posts_label($found)); ?></p>
                        <div class="bm-card-grid bm-card-grid--cols-3">
                            <?php
                            while (have_posts()) :
                                the_post();
                                get_template_part('template-parts/content', 'excerpt');
                            endwhile;
                            ?>
                        </div>
                        <?php bm_archive_pagination(); ?>
                    <?php else : ?>
                        <?php get_template_part('template-parts/content', 'none'); ?>
                    <?php endif; ?>
                </div>

                <aside class="bm-content-layout__sidebar" aria-label="Дополнительные материалы">
                    <?php if (!empty($topics)) : ?>
                        <div class="bm-sidebar-card">
                            <h3 class="bm-sidebar-card__title">Темы</h3>
                            <ul class="bm-tag-list">
                                <?php foreach ($topics as $topic) : ?>
                                    <li class="bm-tag-list__item">
                                        <a class="bm-tag-list__link" href="<?php echo esc_url(add_query_arg('topic', $topic->slug, $posts_page_url)); ?>">
                                            <span><?php echo esc_html($topic->name); ?></span>
                                            <span class="bm-tag-list__count"><?php echo esc_html((string) $topic->count); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="bm-sidebar-card bm-sidebar-card--soft bm-sidebar-card--center">
                        <h3 class="bm-sidebar-card__title">Не нашли нужную тему?</h3>
                        <p class="bm-sidebar-card__text">Предложите тему для новой статьи — мы рассмотрим вашу идею.</p>
                        <div class="bm-sidebar-card__action">
                            <a href="<?php echo esc_url(home_url('/kontakty/')); ?>" class="bm-button bm-button--secondary bm-button--full">Предложить тему</a>
                        </div>
                    </div>

                    <?php if (!empty($popular)) : ?>
                        <div class="bm-sidebar-card">
                            <h3 class="bm-sidebar-card__title">Популярные статьи</h3>
                            <?php foreach ($popular as $pop) : ?>
                                <?php $pop_cat = bm_primary_category((int) $pop->ID); ?>
                                <article class="bm-card-popular-article">
                                    <a class="bm-card-popular-article__link" href="<?php echo esc_url(get_permalink($pop)); ?>">
                                        <div class="bm-card-popular-article__media">
                                            <?php if (has_post_thumbnail($pop)) : ?>
                                                <?php echo get_the_post_thumbnail($pop, [64, 64], ['loading' => 'lazy', 'decoding' => 'async']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 class="bm-card-popular-article__title"><?php echo esc_html(get_the_title($pop)); ?></h4>
                                            <p class="bm-card-popular-article__meta">
                                                <?php if ($pop_cat instanceof WP_Term) : ?>
                                                    <span class="bm-card-popular-article__tag"><?php echo esc_html($pop_cat->name); ?></span>
                                                <?php endif; ?>
                                                <span><?php echo esc_html(bm_reading_time_label((int) $pop->ID)); ?></span>
                                            </p>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </section>
</main>
