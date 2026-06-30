<?php
declare(strict_types=1);

$title = (string) ($attributes['title'] ?? '');
$subtitle = (string) ($attributes['subtitle'] ?? '');
$anchor = trim((string) ($attributes['anchor'] ?? ''));
$title_id = trim((string) ($attributes['titleId'] ?? 'tasks-title'));
$variant = (string) ($attributes['variant'] ?? '');
$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$columns = (int) ($attributes['columns'] ?? count($items));

if ($title_id === '') {
    $title_id = 'tasks-title';
}

$section_classes = 'bm-section';
if ($variant !== 'compact') {
    $section_classes .= ' bm-section--bg';
}
?>
<section<?php echo $anchor !== '' ? ' id="' . esc_attr($anchor) . '"' : ''; ?> class="<?php echo esc_attr($section_classes); ?>" aria-labelledby="<?php echo esc_attr($title_id); ?>">
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
        </header>

        <?php if ($items !== []) : ?>
            <div class="bm-card-grid bm-card-grid--cols-<?php echo esc_attr((string) $columns); ?>">
                <?php foreach ($items as $item) : ?>
                    <?php
                    if (!is_array($item)) {
                        continue;
                    }

                    $item_title = (string) ($item['title'] ?? '');
                    $item_text = (string) ($item['text'] ?? '');
                    $item_url = (string) ($item['url'] ?? '#calculators');
                    $item_label = (string) ($item['label'] ?? 'Подобрать расчёты');
                    $item_image = (string) ($item['image'] ?? '');
                    $item_icon = (string) ($item['icon'] ?? 'check-circle');
                    ?>
                    <article class="bm-card bm-card-task<?php echo $variant === 'compact' ? ' bm-card-task--compact' : ''; ?>">
                        <?php if ($variant === 'compact') : ?>
                            <div class="bm-card-task__head">
                                <span class="bm-card-task__icon" aria-hidden="true">
                                    <svg class="bm-icon bm-card-task__icon-svg">
                                        <use href="#bm-icon-<?php echo esc_attr($item_icon); ?>"></use>
                                    </svg>
                                </span>
                                <div class="bm-card-task__copy">
                                    <?php if ($item_title !== '') : ?>
                                        <h3 class="bm-card-task__title"><?php echo esc_html($item_title); ?></h3>
                                    <?php endif; ?>
                                    <?php if ($item_text !== '') : ?>
                                        <p class="bm-card-task__text"><?php echo esc_html($item_text); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?php echo constructly_esc_block_href($item_url); ?>" class="bm-card-task__link">
                                <?php echo esc_html($item_label); ?>
                                <svg class="bm-icon bm-icon--sm" aria-hidden="true">
                                    <use href="#bm-icon-arrow-right"></use>
                                </svg>
                            </a>
                        <?php else : ?>
                            <?php if ($item_image !== '') : ?>
                                <div class="bm-card-task__media">
                                    <img src="<?php echo constructly_esc_block_image_src($item_image); ?>" alt="" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="bm-card-task__body">
                                <?php if ($item_title !== '') : ?>
                                    <h3 class="bm-card-task__title"><?php echo esc_html($item_title); ?></h3>
                                <?php endif; ?>
                                <?php if ($item_text !== '') : ?>
                                    <p class="bm-card-task__text"><?php echo esc_html($item_text); ?></p>
                                <?php endif; ?>
                                <a href="<?php echo constructly_esc_block_href($item_url); ?>" class="bm-card-task__link"><?php echo esc_html($item_label); ?> →</a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
