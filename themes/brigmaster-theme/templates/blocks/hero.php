<?php
declare(strict_types=1);

$title = (string) ($attributes['title'] ?? '');
$lead = (string) ($attributes['lead'] ?? '');
$primary_label = (string) ($attributes['primaryLabel'] ?? '');
$primary_url = (string) ($attributes['primaryUrl'] ?? '#calculators');
$secondary_label = (string) ($attributes['secondaryLabel'] ?? '');
$secondary_url = (string) ($attributes['secondaryUrl'] ?? '#how-it-works');
$features = is_array($attributes['features'] ?? null) ? $attributes['features'] : [];
$demo = is_array($attributes['demo'] ?? null) ? $attributes['demo'] : [];
$note = (string) ($attributes['note'] ?? '');
?>
<section class="bm-home-hero" aria-labelledby="home-hero-title">
    <div class="bm-container">
        <div class="bm-home-hero__grid">
            <div class="bm-hero">
                <?php if ($title !== '') : ?>
                    <h1 id="home-hero-title" class="bm-hero__title"><?php echo esc_html($title); ?></h1>
                <?php endif; ?>

                <?php if ($lead !== '') : ?>
                    <p class="bm-hero__lead"><?php echo esc_html($lead); ?></p>
                <?php endif; ?>

                <div class="bm-hero__actions">
                    <?php if ($primary_label !== '') : ?>
                        <a href="<?php echo constructly_esc_block_href($primary_url); ?>" class="bm-button bm-button--primary"><?php echo esc_html($primary_label); ?></a>
                    <?php endif; ?>
                    <?php if ($secondary_label !== '') : ?>
                        <a href="<?php echo constructly_esc_block_href($secondary_url); ?>" class="bm-button bm-button--secondary"><?php echo esc_html($secondary_label); ?></a>
                    <?php endif; ?>
                </div>

                <?php if ($features !== []) : ?>
                    <ul class="bm-hero__features">
                        <?php foreach ($features as $feature) : ?>
                            <?php
                            $feature_icon = isset($feature['icon']) ? (string) $feature['icon'] : 'check-circle';
                            $feature_title = isset($feature['title']) ? (string) $feature['title'] : '';
                            $feature_text = isset($feature['text']) ? (string) $feature['text'] : '';
                            ?>
                            <li class="bm-hero__feature">
                                <svg class="bm-icon bm-hero__feature-icon" aria-hidden="true">
                                    <use href="#bm-icon-<?php echo esc_attr($feature_icon); ?>"></use>
                                </svg>
                                <span class="bm-hero__feature-copy">
                                    <?php if ($feature_title !== '') : ?>
                                        <span class="bm-hero__feature-title"><?php echo esc_html($feature_title); ?></span>
                                    <?php endif; ?>
                                    <?php if ($feature_text !== '') : ?>
                                        <span class="bm-hero__feature-text"><?php echo wp_kses($feature_text, ['br' => []]); ?></span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="bm-home-hero__visual">
                <article class="bm-home-hero-demo" aria-label="<?php echo esc_attr((string) ($demo['ariaLabel'] ?? 'Пример расчета')); ?>">
                    <h2 class="bm-home-hero-demo__title"><?php echo esc_html((string) ($demo['title'] ?? 'Пример расчета: Стяжка пола')); ?></h2>
                    <div class="bm-home-hero-demo__layout">
                        <div class="bm-home-hero-demo__fields">
                            <?php foreach (($demo['fields'] ?? []) as $field) : ?>
                                <?php if (!is_array($field)) continue; ?>
                                <?php $is_select = !empty($field['select']); ?>
                                <div class="bm-home-hero-demo__field">
                                    <span class="bm-home-hero-demo__label"><?php echo esc_html((string) ($field['label'] ?? '')); ?></span>
                                    <div class="bm-home-hero-demo__field-value<?php echo $is_select ? ' bm-home-hero-demo__field-value--select' : ''; ?>">
                                        <span class="bm-home-hero-demo__field-number"><?php echo esc_html((string) ($field['value'] ?? '')); ?></span>
                                        <?php if (!empty($field['unit'])) : ?>
                                            <span class="bm-home-hero-demo__field-unit"><?php echo esc_html((string) $field['unit']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($is_select) : ?>
                                            <svg class="bm-icon bm-icon--sm bm-home-hero-demo__select-icon" aria-hidden="true">
                                                <use href="#bm-icon-chevron-down"></use>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <span class="bm-home-hero-demo__more-link"><?php echo esc_html((string) ($demo['moreLabel'] ?? 'Дополнительные параметры')); ?></span>
                        </div>

                        <div class="bm-home-hero-demo__result">
                            <p class="bm-home-hero-demo__result-label"><?php echo esc_html((string) ($demo['resultLabel'] ?? 'Результат')); ?></p>
                            <p class="bm-home-hero-demo__result-main">
                                <span class="bm-home-hero-demo__result-summary"><?php echo esc_html((string) ($demo['resultSummary'] ?? 'Объем стяжки')); ?></span>
                                <span class="bm-home-hero-demo__result-value"><?php echo esc_html((string) ($demo['resultValue'] ?? '2.25 м³')); ?></span>
                            </p>
                            <?php if (!empty($demo['resultItems']) && is_array($demo['resultItems'])) : ?>
                                <ul class="bm-home-hero-demo__result-list">
                                    <?php foreach ($demo['resultItems'] as $item) : ?>
                                        <?php if (!is_array($item)) continue; ?>
                                        <li class="bm-home-hero-demo__result-item">
                                            <span class="bm-home-hero-demo__result-name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></span>
                                            <span class="bm-home-hero-demo__result-amount"><?php echo esc_html((string) ($item['amount'] ?? '')); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

                <?php if ($note !== '') : ?>
                    <p class="bm-home-hero__note">
                        <svg class="bm-icon bm-home-hero__note-icon" aria-hidden="true">
                            <use href="#bm-icon-info-circle"></use>
                        </svg>
                        <span class="bm-home-hero__note-text"><?php echo esc_html($note); ?></span>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
