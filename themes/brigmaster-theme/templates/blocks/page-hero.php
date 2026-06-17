<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$title = (string) ($attributes['title'] ?? '');
$lead = (string) ($attributes['lead'] ?? '');
$image = (string) ($attributes['image'] ?? '');
$paragraphs = is_array($attributes['paragraphs'] ?? null) ? $attributes['paragraphs'] : [];
$breadcrumbs = is_array($attributes['breadcrumbs'] ?? null) ? $attributes['breadcrumbs'] : [];
$features = is_array($attributes['features'] ?? null) ? $attributes['features'] : [];
$title_id = (string) ($attributes['titleId'] ?? 'page-hero-title');
$feature_columns = (int) ($attributes['columns'] ?? 0);
$features_small_title = (string) ($attributes['variant'] ?? '') === 'small-title';

if ($title_id === '') {
    $title_id = 'page-hero-title';
}

$features_classes = ['bm-hero__features'];
if ($feature_columns > 0) {
    $features_classes[] = 'bm-hero__features--cols-' . $feature_columns;
}
if ($features_small_title) {
    $features_classes[] = 'bm-hero__features--small-title';
}
?>
<section class="bm-page-hero" aria-labelledby="<?php echo esc_attr($title_id); ?>">
    <?php if ($image !== '') : ?>
        <img class="bm-page-hero__image" src="<?php echo constructly_esc_block_image_src($image); ?>" alt="" loading="eager" decoding="async">
    <?php endif; ?>
    <div class="bm-container bm-page-hero__container">
        <?php if ($breadcrumbs !== []) : ?>
            <nav class="bm-breadcrumbs bm-breadcrumbs--chevron bm-breadcrumbs--link-brand bm-page-hero__breadcrumbs" aria-label="Breadcrumb">
                <ol class="bm-breadcrumbs__list">
                    <?php foreach ($breadcrumbs as $breadcrumb) : ?>
                        <?php
                        if (!is_array($breadcrumb)) {
                            continue;
                        }

                        $label = (string) ($breadcrumb['label'] ?? '');
                        $url = (string) ($breadcrumb['url'] ?? '');
                        if ($label === '') {
                            continue;
                        }
                        ?>
                        <li class="bm-breadcrumbs__item"<?php echo $url === '' ? ' aria-current="page"' : ''; ?>>
                            <?php if ($url !== '') : ?>
                                <a href="<?php echo constructly_esc_block_href($url); ?>"><?php echo esc_html($label); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($label); ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <div class="bm-hero">
            <?php if ($title !== '') : ?>
                <h1 id="<?php echo esc_attr($title_id); ?>" class="bm-hero__title"><?php echo esc_html($title); ?></h1>
            <?php endif; ?>
            <?php if ($lead !== '') : ?>
                <p class="bm-hero__lead"><?php echo esc_html($lead); ?></p>
            <?php endif; ?>
            <?php foreach ($paragraphs as $paragraph) : ?>
                <?php
                $paragraph_text = is_array($paragraph) ? (string) ($paragraph['text'] ?? '') : (string) $paragraph;
                if ($paragraph_text === '') {
                    continue;
                }
                ?>
                <p class="bm-page-hero__text"><?php echo esc_html($paragraph_text); ?></p>
            <?php endforeach; ?>
            <?php if ($features !== []) : ?>
                <ul class="<?php echo esc_attr(implode(' ', $features_classes)); ?>">
                    <?php foreach ($features as $feature) : ?>
                        <?php
                        if (!is_array($feature)) {
                            continue;
                        }

                        $feature_icon = (string) ($feature['icon'] ?? 'check-circle');
                        $feature_title = (string) ($feature['title'] ?? '');
                        $feature_text = (string) ($feature['text'] ?? '');
                        if ($feature_title === '' && $feature_text === '') {
                            continue;
                        }
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
                                    <span class="bm-hero__feature-text"><?php echo esc_html($feature_text); ?></span>
                                <?php endif; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
