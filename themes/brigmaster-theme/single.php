<?php
declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();
    $post_id = (int) get_the_ID();
    // View is recorded on template_redirect (before output) — see Constructly_Frontend.

    $primary_cat = bm_primary_category($post_id);
    $posts_page_url = ($pid = (int) get_option('page_for_posts')) > 0 ? get_permalink($pid) : home_url('/baza-znaniy/');
    $popular = bm_popular_posts(3, $post_id);
    $feedback_nonce = wp_create_nonce('bm_article_feedback_' . $post_id);
    ?>
    <main id="main" class="site-main site-main--single-post" role="main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('bm-post-single'); ?>>
            <section class="bm-page-hero bm-page-hero--article" aria-labelledby="article-hero-title">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('large', ['class' => 'bm-page-hero__image', 'loading' => 'eager', 'decoding' => 'async', 'alt' => '']); ?>
                <?php endif; ?>
                <div class="bm-container bm-page-hero__container">
                    <nav class="bm-breadcrumbs bm-breadcrumbs--chevron bm-breadcrumbs--link-brand bm-page-hero__breadcrumbs" aria-label="Хлебные крошки">
                        <ol class="bm-breadcrumbs__list">
                            <li class="bm-breadcrumbs__item"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a></li>
                            <li class="bm-breadcrumbs__item"><a href="<?php echo esc_url($posts_page_url); ?>">База знаний</a></li>
                            <?php if ($primary_cat instanceof WP_Term) : ?>
                                <li class="bm-breadcrumbs__item"><a href="<?php echo esc_url(get_category_link($primary_cat)); ?>"><?php echo esc_html($primary_cat->name); ?></a></li>
                            <?php endif; ?>
                            <li class="bm-breadcrumbs__item" aria-current="page"><?php echo esc_html(get_the_title()); ?></li>
                        </ol>
                    </nav>
                    <div class="bm-hero">
                        <?php if ($primary_cat instanceof WP_Term) : ?>
                            <a class="bm-chip bm-chip--category" href="<?php echo esc_url(get_category_link($primary_cat)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
                        <?php endif; ?>
                        <h1 id="article-hero-title" class="bm-hero__title"><?php the_title(); ?></h1>
                        <?php if (has_excerpt()) : ?>
                            <p class="bm-hero__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
                        <?php endif; ?>
                        <div class="bm-meta">
                            <span class="bm-meta__item">
                                <svg class="bm-icon bm-meta__icon" aria-hidden="true"><use href="#bm-icon-clock"></use></svg>
                                <span><?php echo esc_html(bm_reading_time_label($post_id)); ?></span>
                            </span>
                            <span class="bm-meta__item">
                                <svg class="bm-icon bm-meta__icon" aria-hidden="true"><use href="#bm-icon-calendar"></use></svg>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d.m.Y')); ?></time>
                            </span>
                            <span class="bm-meta__item"><?php echo esc_html(bm_post_views_label($post_id)); ?></span>
                            <span class="bm-meta__share">
                                <button type="button" class="bm-button bm-button--secondary" data-bm-share data-share-title="<?php echo esc_attr(get_the_title()); ?>" data-share-url="<?php echo esc_url(get_permalink()); ?>">
                                    <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-share"></use></svg>
                                    <span class="bm-share__label">Поделиться</span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bm-section bm-section--tight">
                <div class="bm-container">
                    <div class="bm-content-layout bm-content-layout--with-right-sidebar bm-content-layout--article">
                        <div class="bm-content-layout__main bm-prose">
                            <?php the_content(); ?>

                            <div class="bm-article-feedback" data-bm-component="article-feedback" data-post-id="<?php echo esc_attr((string) $post_id); ?>" data-nonce="<?php echo esc_attr($feedback_nonce); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                                <div class="bm-article-feedback__content">
                                    <h2 class="bm-article-feedback__title">Статья была полезна?</h2>
                                    <p class="bm-article-feedback__text">Ваше мнение помогает нам делать контент лучше.</p>
                                </div>
                                <div class="bm-article-feedback__actions">
                                    <button type="button" class="bm-button bm-button--secondary bm-article-feedback__btn bm-article-feedback__btn--yes" data-vote="yes">
                                        <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-like"></use></svg>
                                        Да, полезна
                                    </button>
                                    <button type="button" class="bm-button bm-button--secondary bm-article-feedback__btn bm-article-feedback__btn--no" data-vote="no">
                                        <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-like"></use></svg>
                                        Нет, не очень
                                    </button>
                                </div>
                            </div>
                        </div>

                        <aside class="bm-content-layout__sidebar" aria-label="Дополнительно">
                            <nav class="bm-toc bm-toc--collapsible bm-toc--in-sidebar bm-sidebar-card" data-bm-component="toc" aria-label="Содержание статьи">
                                <button type="button" class="bm-toc__toggle" aria-expanded="false" aria-controls="article-toc-panel">
                                    <span>Содержание статьи</span>
                                    <svg class="bm-icon bm-toc__toggle-icon" aria-hidden="true"><use href="#bm-icon-chevron-down"></use></svg>
                                </button>
                                <div id="article-toc-panel" class="bm-toc__panel">
                                    <div class="bm-toc__panel-inner">
                                        <h2 class="bm-toc__title">В этой статье</h2>
                                        <ol class="bm-toc__list"></ol>
                                    </div>
                                </div>
                            </nav>

                            <div class="bm-content-layout__sidebar-body">
                                <div class="bm-sidebar-card bm-sidebar-card--soft bm-sidebar-card--media">
                                    <img class="bm-sidebar-card__image" src="<?php echo esc_url(constructly_esc_block_image_src('assets/src/images/illustrations/calc_bg.jpg')); ?>" alt="" loading="lazy" decoding="async">
                                    <div class="bm-sidebar-card__content">
                                        <h3 class="bm-sidebar-card__title">Рассчитайте материалы для строительства</h3>
                                        <p class="bm-sidebar-card__text">Подберите калькулятор и получите ориентировочный расчёт за несколько минут.</p>
                                        <div class="bm-sidebar-card__action">
                                            <a href="<?php echo esc_url(home_url('/kalkulyatory/')); ?>" class="bm-button bm-button--primary bm-button--full">Перейти к калькуляторам</a>
                                        </div>
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
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </article>
    </main>
    <?php
endwhile;

get_footer();
