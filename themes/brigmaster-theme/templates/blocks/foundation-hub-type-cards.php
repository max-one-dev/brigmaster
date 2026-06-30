<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$section_title = (string) ($attributes['sectionTitle'] ?? '');
$subtitle = (string) ($attributes['subtitle'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url = (string) ($attributes['linkUrl'] ?? '');
$anchor_id = trim((string) ($attributes['anchorId'] ?? 'hub-calculators'));
$title_id = trim((string) ($attributes['titleId'] ?? 'hub-calculators-title'));
$cards = is_array($attributes['cards'] ?? null) ? $attributes['cards'] : [];
$columns = (int) ($attributes['columns'] ?? 0);

if ($anchor_id === '') {
    $anchor_id = 'hub-calculators';
}

if ($title_id === '') {
    $title_id = 'hub-calculators-title';
}
?>
<section id="<?php echo esc_attr($anchor_id); ?>" class="bm-section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <header class="bm-section-toolbar">
            <div class="bm-section-toolbar__main">
                <?php if ($section_title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-section-toolbar__title"><?php echo esc_html($section_title); ?></h2>
                <?php endif; ?>
                <?php if ($subtitle !== '') : ?>
                    <p class="bm-section-toolbar__lead"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($link_label !== '' && $link_url !== '') : ?>
                <a class="bm-section-toolbar__link" href="<?php echo constructly_esc_block_href($link_url); ?>"><?php echo esc_html($link_label); ?></a>
            <?php endif; ?>
        </header>

        <?php if ($cards !== []) : ?>
            <div class="<?php echo esc_attr('bm-card-grid bm-card-grid--foundation-types' . ($columns > 0 ? ' bm-card-grid--cols-' . $columns : '')); ?>">
                <?php foreach ($cards as $card) : ?>
                    <?php
                    if (!is_array($card)) {
                        continue;
                    }

                    $image = (string) ($card['image'] ?? '');
                    $title = (string) ($card['title'] ?? '');
                    $text = (string) ($card['text'] ?? '');
                    $href = (string) ($card['href'] ?? '#');
                    $cta = (string) ($card['cta'] ?? '');
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
                            <?php if ($cta !== '') : ?>
                                <a href="<?php echo constructly_esc_block_href($href); ?>" class="bm-button bm-button--secondary">
                                    <?php echo esc_html($cta); ?>
                                    <svg class="bm-icon bm-icon--sm" aria-hidden="true">
                                        <use href="#bm-icon-arrow-right"></use>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
