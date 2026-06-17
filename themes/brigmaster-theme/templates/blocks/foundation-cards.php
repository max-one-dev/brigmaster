<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$cards = is_array($attributes['cards'] ?? null) ? $attributes['cards'] : [];

if ($cards === []) {
    return;
}
?>
<div class="bm-card-grid bm-card-grid--foundation-types">
    <?php foreach ($cards as $card) : ?>
        <?php
        if (!is_array($card)) {
            continue;
        }

        $image = (string) ($card['image'] ?? '');
        $title = (string) ($card['title'] ?? '');
        $text = (string) ($card['text'] ?? '');
        if ($title === '' && $text === '') {
            continue;
        }
        ?>
        <article class="bm-card bm-card-calculator bm-card-calculator--cover">
            <?php if ($image !== '') : ?>
                <div class="bm-card-calculator__media">
                    <img src="<?php echo constructly_esc_block_image_src($image); ?>" alt="" width="290" height="160" loading="lazy" decoding="async">
                </div>
            <?php endif; ?>
            <div class="bm-card-calculator__body">
                <?php if ($title !== '') : ?>
                    <h3 class="bm-card-calculator__title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                <?php if ($text !== '') : ?>
                    <p class="bm-card-calculator__text"><?php echo esc_html($text); ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
