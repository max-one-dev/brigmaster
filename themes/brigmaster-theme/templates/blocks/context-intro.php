<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$title = (string) ($attributes['title'] ?? '');
$body  = (string) ($attributes['body'] ?? '');

if ($title === '' && $body === '') {
    return;
}
?>
<section class="bm-section bm-section--bg bm-context-intro">
    <div class="bm-container">
        <div class="bm-context-intro__inner bm-prose">
            <?php if ($title !== '') : ?>
                <h2 class="bm-context-intro__title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <?php if ($body !== '') : ?>
                <?php echo wp_kses_post($body); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
