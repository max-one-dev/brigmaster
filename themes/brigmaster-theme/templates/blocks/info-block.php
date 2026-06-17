<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$text = (string) ($attributes['text'] ?? '');

if ($text === '') {
    return;
}
?>
<aside class="bm-info-block">
    <span class="bm-info-block__icon" aria-hidden="true">
        <svg class="bm-icon bm-info-block__icon-svg">
            <use href="#bm-icon-info-circle"></use>
        </svg>
    </span>
    <p class="bm-info-block__text"><?php echo esc_html($text); ?></p>
</aside>
