<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$columns = (int) ($attributes['columns'] ?? 4);
if ($columns < 1) {
    $columns = 4;
}
$heading = (string) ($attributes['title'] ?? '');

if ($items === []) {
    return;
}

$inline_tags = ['sub' => [], 'sup' => [], 'strong' => [], 'em' => []];
?>
<?php if ($heading !== '') : ?>
    <h2 class="bm-article-criteria__heading"><?php echo esc_html($heading); ?></h2>
<?php endif; ?>
<div class="bm-article-criteria bm-article-criteria--cols-<?php echo esc_attr((string) $columns); ?>">
    <?php foreach ($items as $item) : ?>
        <?php
        if (!is_array($item)) {
            continue;
        }

        $icon = (string) ($item['icon'] ?? '');
        $title = (string) ($item['title'] ?? '');
        $text = (string) ($item['text'] ?? '');
        if ($title === '' && $text === '') {
            continue;
        }
        ?>
        <div class="bm-article-criteria__item">
            <?php if ($icon !== '') : ?>
                <span class="bm-article-criteria__icon" aria-hidden="true">
                    <svg class="bm-icon bm-article-criteria__icon-svg">
                        <use href="#bm-icon-<?php echo esc_attr($icon); ?>"></use>
                    </svg>
                </span>
            <?php endif; ?>
            <?php if ($title !== '') : ?>
                <h3 class="bm-article-criteria__title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>
            <?php if ($text !== '') : ?>
                <p class="bm-article-criteria__text"><?php echo wp_kses($text, $inline_tags); ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
