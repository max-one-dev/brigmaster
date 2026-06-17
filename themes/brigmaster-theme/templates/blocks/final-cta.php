<?php
declare(strict_types=1);

$title = (string) ($attributes['title'] ?? '');
$text = (string) ($attributes['text'] ?? '');
$button_label = (string) ($attributes['buttonLabel'] ?? '');
$button_url = (string) ($attributes['buttonUrl'] ?? '#calculators');
$image = array_key_exists('image', $attributes) ? (string) $attributes['image'] : 'assets/src/images/illustrations/cta-house-dark.jpg';
$variant = (string) ($attributes['variant'] ?? 'dark');
$title_id = trim((string) ($attributes['titleId'] ?? 'final-cta-title'));

if ($title_id === '') {
    $title_id = 'final-cta-title';
}

$cta_class = $variant === 'soft' ? 'bm-cta-block bm-cta-block--soft' : 'bm-cta-block bm-cta-block--dark';
$button_class = $variant === 'soft' ? 'bm-button bm-button--primary' : 'bm-button bm-button--inverse';
?>
<section class="bm-section bm-section--tight" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <aside class="<?php echo esc_attr($cta_class); ?>">
            <?php if ($image !== '') : ?>
                <img src="<?php echo constructly_esc_block_image_src($image); ?>" alt="" loading="lazy" decoding="async">
            <?php endif; ?>
            <div class="bm-cta-block__content">
                <?php if ($title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-cta-block__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($text !== '') : ?>
                    <p class="bm-cta-block__lead"><?php echo esc_html($text); ?></p>
                <?php endif; ?>
                <?php if ($button_label !== '') : ?>
                    <div class="bm-cta-block__actions">
                        <a href="<?php echo constructly_esc_block_href($button_url); ?>" class="<?php echo esc_attr($button_class); ?>"><?php echo esc_html($button_label); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>
