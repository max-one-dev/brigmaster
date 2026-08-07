<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$heading = (string) ($attributes['title'] ?? '');

if ($items === []) {
    return;
}

$inline_tags = ['sub' => [], 'sup' => [], 'strong' => [], 'em' => []];
?>
<?php if ($heading !== '') : ?>
    <h2 class="bm-article-mistakes__heading"><?php echo esc_html($heading); ?></h2>
<?php endif; ?>
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
            <span><?php echo wp_kses($text, $inline_tags); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
