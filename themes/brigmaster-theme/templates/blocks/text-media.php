<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$title = (string) ($attributes['title'] ?? '');
$title_id = (string) ($attributes['titleId'] ?? 'text-media-title');
$paragraphs = is_array($attributes['paragraphs'] ?? null) ? $attributes['paragraphs'] : [];
$image = (string) ($attributes['image'] ?? '');
$image_alt = (string) ($attributes['imageAlt'] ?? '');
$media_position = (string) ($attributes['mediaPosition'] ?? 'right');

if ($title_id === '') {
    $title_id = 'text-media-title';
}

$block_classes = ['bm-text-media'];
if ($media_position === 'left') {
    $block_classes[] = 'bm-text-media--media-left';
}
?>
<section class="bm-section bm-section--tight" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <div class="<?php echo esc_attr(implode(' ', $block_classes)); ?>">
            <div class="bm-text-media__content">
                <?php if ($title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-text-media__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php foreach ($paragraphs as $paragraph) : ?>
                    <?php
                    $paragraph_text = is_array($paragraph) ? (string) ($paragraph['text'] ?? '') : (string) $paragraph;
                    if ($paragraph_text === '') {
                        continue;
                    }
                    ?>
                    <p class="bm-text-media__text"><?php echo esc_html($paragraph_text); ?></p>
                <?php endforeach; ?>
            </div>
            <?php if ($image !== '') : ?>
                <img class="bm-text-media__image" src="<?php echo constructly_esc_block_image_src($image); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy" decoding="async">
            <?php endif; ?>
        </div>
    </div>
</section>
