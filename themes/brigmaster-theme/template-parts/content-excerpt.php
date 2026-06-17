<?php
declare(strict_types=1);

/**
 * Knowledge-base feed card (bm-card-article), matching the constructly/articles
 * block markup but driven by post data. Used in the /baza-znaniy/ archive grid.
 */
$primary_cat = bm_primary_category(get_the_ID());
$reading = bm_reading_time_label(get_the_ID());
?>
<article <?php post_class('bm-card bm-card-article'); ?>>
    <a class="bm-card-article__link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <div class="bm-card-article__media">
                <?php
                the_post_thumbnail('medium_large', [
                    'width' => 400,
                    'height' => 240,
                    'loading' => 'lazy',
                    'decoding' => 'async',
                ]);
                ?>
            </div>
        <?php else : ?>
            <div class="bm-card-article__media bm-card-article__media--placeholder" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="bm-card-article__body">
            <?php if ($primary_cat instanceof WP_Term) : ?>
                <span class="bm-chip bm-chip--category"><?php echo esc_html($primary_cat->name); ?></span>
            <?php endif; ?>
            <h3 class="bm-card-article__title"><?php echo esc_html(get_the_title()); ?></h3>
            <p class="bm-card-article__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
            <div class="bm-card-article__meta">
                <span class="bm-card-article__meta-item">
                    <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                        <use href="#bm-icon-clock"></use>
                    </svg>
                    <span><?php echo esc_html($reading); ?></span>
                </span>
                <span class="bm-card-article__meta-item">
                    <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                        <use href="#bm-icon-calendar"></use>
                    </svg>
                    <span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
                </span>
            </div>
        </div>
    </a>
</article>
