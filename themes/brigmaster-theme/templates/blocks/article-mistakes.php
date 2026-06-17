<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

if ($items === []) {
    return;
}
?>
<ul class="bm-article-mistakes">
    <?php foreach ($items as $item) : ?>
        <?php
        $text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item;
        if ($text === '') {
            continue;
        }
        ?>
        <li class="bm-article-mistakes__item">
            <svg class="bm-icon bm-article-mistakes__icon" aria-hidden="true">
                <use href="#bm-icon-close"></use>
            </svg>
            <span><?php echo esc_html($text); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
