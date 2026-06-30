<?php
declare(strict_types=1);

$anchor = trim((string) ($attributes['anchor'] ?? 'calculators'));
$title_id = trim((string) ($attributes['titleId'] ?? 'calculators-title'));
$theme_variant = (string) ($attributes['themeVariant'] ?? '');
$title = (string) ($attributes['title'] ?? '');
$subtitle = (string) ($attributes['subtitle'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url = (string) ($attributes['linkUrl'] ?? '');
$cards = is_array($attributes['cards'] ?? null) ? $attributes['cards'] : [];
$section_classes = 'bm-section';
if ($theme_variant === 'muted') {
    $section_classes .= ' bm-section--muted';
}
if ($title_id === '') {
    $title_id = 'calculators-title';
}
?>
<section id="<?php echo esc_attr(sanitize_title($anchor) ?: 'calculators'); ?>" class="<?php echo esc_attr($section_classes); ?>" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <header class="bm-section-toolbar">
            <div class="bm-section-toolbar__main">
                <?php if ($title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-section-toolbar__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($subtitle !== '') : ?>
                    <p class="bm-section-toolbar__lead"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($link_label !== '' && $link_url !== '') : ?>
                <a class="bm-section-toolbar__link" href="<?php echo constructly_esc_block_href($link_url); ?>"><?php echo esc_html($link_label); ?></a>
            <?php endif; ?>
        </header>

        <?php if ($cards !== []) : ?>
            <?php
            $columns = (int) ($attributes['columns'] ?? 5);
            $columns_class = $columns > 0 ? ' bm-card-grid--cols-' . $columns : ' bm-card-grid--cols-5';
            ?>
            <div class="bm-card-grid<?php echo esc_attr($columns_class); ?>">
                <?php foreach ($cards as $card) : ?>
                    <?php
                    if (!is_array($card)) {
                        continue;
                    }

                    $card_title = (string) ($card['title'] ?? '');
                    $description = (string) ($card['description'] ?? '');
                    $button_label = (string) ($card['buttonLabel'] ?? '');
                    $button_url = (string) ($card['buttonUrl'] ?? '#calculators');
                    $icon = (string) ($card['icon'] ?? 'calculator');
                    $icon_image = (string) ($card['image'] ?? '');
                    ?>
                    <article class="bm-card bm-card-calculator">
                        <div class="bm-card-calculator__icon">
                            <?php if ($icon_image !== '') : ?>
                                <img src="<?php echo constructly_esc_block_image_src($icon_image); ?>" alt="" width="100" height="100" loading="lazy" decoding="async">
                            <?php else : ?>
                                <svg class="bm-icon bm-icon--lg" aria-hidden="true">
                                    <use href="#bm-icon-<?php echo esc_attr($icon); ?>"></use>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <?php if ($card_title !== '') : ?>
                            <h3 class="bm-card-calculator__title"><?php echo esc_html($card_title); ?></h3>
                        <?php endif; ?>
                        <?php if ($description !== '') : ?>
                            <p class="bm-card-calculator__text"><?php echo esc_html($description); ?></p>
                        <?php endif; ?>
                        <?php if ($button_label !== '') : ?>
                            <a href="<?php echo constructly_esc_block_href($button_url); ?>" class="bm-button bm-button--secondary"><?php echo esc_html($button_label); ?></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
