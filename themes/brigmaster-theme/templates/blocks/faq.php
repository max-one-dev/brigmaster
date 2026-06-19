<?php
declare(strict_types=1);

$title = (string) ($attributes['title'] ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url = (string) ($attributes['linkUrl'] ?? '');
$section_id = trim((string) ($attributes['sectionId'] ?? 'faq'));
$title_id = trim((string) ($attributes['titleId'] ?? 'faq-title'));
$variant = (string) ($attributes['variant'] ?? '');
$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

if ($section_id === '') {
    $section_id = 'faq';
}

if ($title_id === '') {
    $title_id = 'faq-title';
}
$section_classes = $variant === 'calculator' ? 'bm-section bm-calculator-faq' : 'bm-section bm-faq-section';

// Build FAQPage JSON-LD entities
$faq_ld_entities = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        continue;
    }
    $q = trim((string) ($item['question'] ?? ''));
    $a = trim((string) ($item['answer'] ?? ''));
    if ($q === '' || $a === '') {
        continue;
    }
    $faq_ld_entities[] = [
        '@type'          => 'Question',
        'name'           => $q,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => wp_strip_all_tags($a),
        ],
    ];
}
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr($section_classes); ?>" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <div class="bm-container">
        <header class="bm-section-toolbar">
            <div class="bm-section-toolbar__main">
                <?php if ($title !== '') : ?>
                    <h2 id="<?php echo esc_attr($title_id); ?>" class="bm-section-toolbar__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
            </div>
            <?php if ($link_label !== '' && $link_url !== '') : ?>
                <a class="bm-section-toolbar__link" href="<?php echo constructly_esc_block_href($link_url); ?>"><?php echo esc_html($link_label); ?></a>
            <?php endif; ?>
        </header>

        <?php if ($items !== []) : ?>
            <div class="bm-faq-grid bm-accordion" data-bm-component="accordion">
                <?php foreach ($items as $index => $item) : ?>
                    <?php
                    if (!is_array($item)) {
                        continue;
                    }

                    $question = (string) ($item['question'] ?? '');
                    $answer = (string) ($item['answer'] ?? '');
                    $panel_id = 'faq-panel-' . (string) ($index + 1);
                    $is_open = false;
                    ?>
                    <article class="bm-faq-grid__item bm-accordion__item<?php echo $is_open ? ' is-open' : ''; ?>">
                        <button class="bm-accordion__trigger" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($panel_id); ?>">
                            <span><?php echo esc_html($question); ?></span>
                            <svg class="bm-icon bm-accordion__icon" aria-hidden="true">
                                <use href="#bm-icon-chevron-down"></use>
                            </svg>
                        </button>
                        <div id="<?php echo esc_attr($panel_id); ?>" class="bm-accordion__panel">
                            <div class="bm-accordion__panel-inner">
                                <div class="bm-accordion__content">
                                    <p class="bm-accordion__text"><?php echo esc_html($answer); ?></p>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php if ($faq_ld_entities !== []) : ?>
<script type="application/ld+json">
<?php echo wp_json_encode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faq_ld_entities,
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
); ?>
</script>
<?php endif; ?>
