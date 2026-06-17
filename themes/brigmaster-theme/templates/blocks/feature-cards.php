<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$section_title = (string) ($attributes['sectionTitle'] ?? '');
$title_id = (string) ($attributes['titleId'] ?? 'feature-cards-title');
$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$columns = (int) ($attributes['columns'] ?? 3);
$variant = (string) ($attributes['variant'] ?? '');

if ($title_id === '') {
    $title_id = 'feature-cards-title';
}

if ($columns < 1) {
    $columns = 3;
}

$grid_classes = ['bm-card-grid', 'bm-card-grid--cols-' . $columns];
if ($variant === 'stacked') {
    $grid_classes[] = 'bm-card-grid--feature-stacked';
}

if ($items === []) {
    return;
}
?>
<section class="bm-section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <?php if ($section_title !== '') : ?>
            <div class="bm-section-toolbar">
                <div class="bm-section-toolbar__main">
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-section-toolbar__title"><?php echo esc_html($section_title); ?></h2>
                </div>
            </div>
        <?php endif; ?>
        <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
            <?php foreach ($items as $item) : ?>
                <?php
                if (!is_array($item)) {
                    continue;
                }

                $icon = (string) ($item['icon'] ?? '');
                $item_title = (string) ($item['title'] ?? '');
                $item_text = (string) ($item['text'] ?? '');
                if ($item_title === '' && $item_text === '') {
                    continue;
                }
                ?>
                <article class="bm-card bm-card-feature">
                    <?php if ($icon !== '') : ?>
                        <span class="bm-card-feature__icon" aria-hidden="true">
                            <svg class="bm-icon bm-card-feature__icon-svg">
                                <use href="#bm-icon-<?php echo esc_attr($icon); ?>"></use>
                            </svg>
                        </span>
                    <?php endif; ?>
                    <div class="bm-card-feature__content">
                        <?php if ($item_title !== '') : ?>
                            <h3 class="bm-card-feature__title"><?php echo esc_html($item_title); ?></h3>
                        <?php endif; ?>
                        <?php if ($item_text !== '') : ?>
                            <p class="bm-card-feature__text"><?php echo esc_html($item_text); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
