<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$title        = (string) ($attributes['title'] ?? '');
$text         = (string) ($attributes['text'] ?? '');
$button_label = (string) ($attributes['buttonLabel'] ?? '');
$url          = (string) ($attributes['url'] ?? '');
$image        = (string) ($attributes['image'] ?? '');

if ($title === '' && $button_label === '') {
    return;
}
?>
<aside class="bm-calc-cta">
    <?php if ($image !== '') : ?>
        <div class="bm-calc-cta__media">
            <img
                src="<?php echo constructly_esc_block_image_src($image); ?>"
                alt=""
                width="160"
                height="110"
                loading="lazy"
                decoding="async"
            >
        </div>
    <?php endif; ?>
    <div class="bm-calc-cta__body">
        <?php if ($title !== '') : ?>
            <h3 class="bm-calc-cta__title"><?php echo esc_html($title); ?></h3>
        <?php endif; ?>
        <?php if ($text !== '') : ?>
            <p class="bm-calc-cta__text"><?php echo esc_html($text); ?></p>
        <?php endif; ?>
    </div>
    <?php if ($button_label !== '' && $url !== '') : ?>
        <a class="bm-calc-cta__button bm-button bm-button--primary" href="<?php echo constructly_esc_block_href($url); ?>">
            <?php echo esc_html($button_label); ?>
            <svg class="bm-icon bm-icon--sm" aria-hidden="true">
                <use href="#bm-icon-arrow-right"></use>
            </svg>
        </a>
    <?php endif; ?>
</aside>
