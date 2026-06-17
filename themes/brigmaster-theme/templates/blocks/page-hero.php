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
$title_id = (string) ($attributes['titleId'] ?? 'page-hero-title');

if ($title_id === '') {
    $title_id = 'page-hero-title';
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
        </div>
    </div>
</section>
