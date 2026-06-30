<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$section_title = (string) ($attributes['sectionTitle'] ?? '');
$title_id = (string) ($attributes['titleId'] ?? 'contacts-form-title');
$text = (string) ($attributes['text'] ?? '');
$channels = is_array($attributes['channels'] ?? null) ? $attributes['channels'] : [];
$shortcode = (string) ($attributes['shortcode'] ?? '');

if ($title_id === '') {
    $title_id = 'contacts-form-title';
}
?>
<section class="bm-section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <div class="bm-contact-form-block">
            <div class="bm-contact-form-block__intro">
                <?php if ($section_title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-contact-form-block__title"><?php echo esc_html($section_title); ?></h2>
                <?php endif; ?>
                <?php if ($text !== '') : ?>
                    <p class="bm-contact-form-block__text"><?php echo esc_html($text); ?></p>
                <?php endif; ?>
                <?php foreach ($channels as $channel) : ?>
                    <?php
                    if (!is_array($channel)) {
                        continue;
                    }

                    $icon = (string) ($channel['icon'] ?? '');
                    $channel_title = (string) ($channel['title'] ?? '');
                    $value = (string) ($channel['value'] ?? '');
                    $note = (string) ($channel['note'] ?? '');
                    if ($channel_title === '' && $value === '') {
                        continue;
                    }
                    ?>
                    <article class="bm-contact-channel">
                        <?php if ($icon !== '') : ?>
                            <svg class="bm-icon bm-contact-channel__icon" aria-hidden="true">
                                <use href="#bm-icon-<?php echo esc_attr($icon); ?>"></use>
                            </svg>
                        <?php endif; ?>
                        <div class="bm-contact-channel__content">
                            <?php if ($channel_title !== '') : ?>
                                <h3 class="bm-contact-channel__title"><?php echo esc_html($channel_title); ?></h3>
                            <?php endif; ?>
                            <?php if ($value !== '') : ?>
                                <p class="bm-contact-channel__value"><?php echo wp_kses($value, ['a' => ['href' => true, 'class' => true]]); ?></p>
                            <?php endif; ?>
                            <?php if ($note !== '') : ?>
                                <p class="bm-contact-channel__note"><?php echo esc_html($note); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($shortcode !== '') : ?>
                <?php echo do_shortcode($shortcode); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
