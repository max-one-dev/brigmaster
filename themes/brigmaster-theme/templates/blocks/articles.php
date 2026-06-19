<?php
declare(strict_types=1);

if ( apply_filters( 'constructly_hide_articles_block', false ) ) {
    return;
}

$title = (string) ($attributes['title'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url = (string) ($attributes['linkUrl'] ?? '');
$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
?>
<section class="bm-section" aria-labelledby="articles-title">
    <div class="bm-container">
        <header class="bm-section-toolbar">
            <div class="bm-section-toolbar__main">
                <?php if ($title !== '') : ?>
                    <h2 id="articles-title" class="bm-section-toolbar__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
            </div>
            <?php if ($link_label !== '' && $link_url !== '') : ?>
                <a class="bm-section-toolbar__link" href="<?php echo constructly_esc_block_href($link_url); ?>"><?php echo esc_html($link_label); ?></a>
            <?php endif; ?>
        </header>

        <?php if ($items !== []) : ?>
            <div class="bm-card-grid bm-card-grid--cols-4">
                <?php foreach ($items as $item) : ?>
                    <?php
                    if (!is_array($item)) {
                        continue;
                    }

                    $item_title = (string) ($item['title'] ?? '');
                    $item_text = (string) ($item['text'] ?? '');
                    $item_url = (string) ($item['url'] ?? '#');
                    $item_image = (string) ($item['image'] ?? '');
                    $item_image_alt = (string) ($item['imageAlt'] ?? '');
                    $item_tag = (string) ($item['tag'] ?? '');
                    $item_read_time = (string) ($item['readTime'] ?? '');
                    $item_date = (string) ($item['date'] ?? '');
                    ?>
                    <article class="bm-card bm-card-article">
                        <a class="bm-card-article__link" href="<?php echo constructly_esc_block_href($item_url); ?>" aria-label="<?php echo esc_attr($item_title); ?>">
                            <?php if ($item_image !== '') : ?>
                                <div class="bm-card-article__media">
                                    <img src="<?php echo constructly_esc_block_image_src($item_image); ?>" alt="<?php echo esc_attr($item_image_alt); ?>" width="400" height="240" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="bm-card-article__body">
                                <?php if ($item_tag !== '') : ?>
                                    <span class="bm-chip bm-chip--category"><?php echo esc_html($item_tag); ?></span>
                                <?php endif; ?>
                                <?php if ($item_title !== '') : ?>
                                    <h3 class="bm-card-article__title"><?php echo esc_html($item_title); ?></h3>
                                <?php endif; ?>
                                <?php if ($item_text !== '') : ?>
                                    <p class="bm-card-article__excerpt"><?php echo esc_html($item_text); ?></p>
                                <?php endif; ?>
                                <?php if ($item_read_time !== '' || $item_date !== '') : ?>
                                    <div class="bm-card-article__meta">
                                        <?php if ($item_read_time !== '') : ?>
                                            <span class="bm-card-article__meta-item">
                                                <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                                                    <use href="#bm-icon-clock"></use>
                                                </svg>
                                                <span><?php echo esc_html($item_read_time); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($item_date !== '') : ?>
                                            <span class="bm-card-article__meta-item">
                                                <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                                                    <use href="#bm-icon-calendar"></use>
                                                </svg>
                                                <span><?php echo esc_html($item_date); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
