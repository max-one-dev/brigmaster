<?php
declare(strict_types=1);

if ( apply_filters( 'constructly_hide_articles_block', false ) ) {
    return;
}

$title      = (string) ($attributes['title']     ?? '');
$link_label = (string) ($attributes['linkLabel'] ?? '');
$link_url   = (string) ($attributes['linkUrl']   ?? '');
$category   = (string) ($attributes['category']  ?? '');
$count      = max(1, (int) ($attributes['count']  ?? 4));
$items      = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$slugs      = is_array($attributes['slugs']  ?? null) ? array_filter(array_map('strval', $attributes['slugs'])) : [];

// Shared helper: convert a WP_Post object into a card item array.
$bm_post_to_item = static function (WP_Post $post): array {
    $post_id  = $post->ID;
    $thumb_id = (int) get_post_thumbnail_id($post_id);
    $cat      = bm_primary_category($post_id);
    return [
        'title'    => get_the_title($post_id),
        'text'     => wp_trim_words(get_the_excerpt($post), 16, '…'),
        'url'      => (string) get_permalink($post_id),
        'image'    => $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'large') : '',
        'imageAlt' => $thumb_id ? (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '',
        'tag'      => $cat instanceof WP_Term ? $cat->name : '',
        'readTime' => (string) bm_reading_time_label($post_id),
        'date'     => (string) get_post_time('d.m.Y', false, $post_id),
    ];
};

// Dynamic query — only when no static items are provided.
if ($items === []) {
    if ($slugs !== []) {
        // Slugs mode: fetch exactly these posts in the declared order.
        $slug_posts = get_posts([
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'post_name__in'    => array_values($slugs),
            'posts_per_page'   => count($slugs),
            'orderby'          => 'post_name__in',
        ]);
        foreach ($slug_posts as $slug_post) {
            $items[] = $bm_post_to_item($slug_post);
        }
        // get_posts does not call setup_postdata; no reset needed.
        // Re-sort to match original $slugs order (WP may not guarantee it).
        $slug_order = array_flip(array_values($slugs));
        usort($items, static function (array $a, array $b) use ($slug_order): int {
            // derive slug from url for ordering
            $slug_a = rtrim(basename(rtrim((string) ($a['url'] ?? ''), '/')), '/');
            $slug_b = rtrim(basename(rtrim((string) ($b['url'] ?? ''), '/')), '/');
            return ($slug_order[$slug_a] ?? 999) <=> ($slug_order[$slug_b] ?? 999);
        });
    } else {
        // Category / recent mode.
        $query_args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $count,
            'ignore_sticky_posts' => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ];
        if ($category !== '') {
            $query_args['category_name'] = $category;
        }

        $dyn_query = new WP_Query($query_args);

        if ($dyn_query->have_posts()) {
            while ($dyn_query->have_posts()) {
                $dyn_query->the_post();
                $items[] = $bm_post_to_item(get_post());
            }
        }

        wp_reset_postdata();
    }
}

// Auto-columns: clamp to 3–4 based on actual item count.
$columns = max(3, min(4, count($items)));
?>
<section class="bm-section" aria-labelledby="articles-title">
    <div class="bm-container">
        <header class="bm-section-toolbar">
            <div class="bm-section-toolbar__main">
                <?php if ($title !== '') : ?>
                    <h2 id="articles-title" class="bm-section-toolbar__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
            </div>
            <?php if ($link_label !== '' && $link_url !== '') : ?>
                <a class="bm-section-toolbar__link" href="<?php echo constructly_esc_block_href($link_url); ?>"><?php echo esc_html($link_label); ?></a>
            <?php endif; ?>
        </header>

        <?php if ($items !== []) : ?>
            <div class="bm-card-grid bm-card-grid--cols-<?php echo $columns; ?>">
                <?php foreach ($items as $item) : ?>
                    <?php
                    if (!is_array($item)) {
                        continue;
                    }

                    $item_title = (string) ($item['title'] ?? '');
                    $item_text = (string) ($item['text'] ?? '');
                    $item_url = (string) ($item['url'] ?? '#');
                    $item_image = (string) ($item['image'] ?? '');
                    $item_image_alt = (string) ($item['imageAlt'] ?? '');
                    $item_tag = (string) ($item['tag'] ?? '');
                    $item_read_time = (string) ($item['readTime'] ?? '');
                    $item_date = (string) ($item['date'] ?? '');
                    ?>
                    <article class="bm-card bm-card-article">
                        <a class="bm-card-article__link" href="<?php echo constructly_esc_block_href($item_url); ?>" aria-label="<?php echo esc_attr($item_title); ?>">
                            <?php if ($item_image !== '') : ?>
                                <div class="bm-card-article__media">
                                    <img src="<?php echo (str_starts_with($item_image, 'http') || str_starts_with($item_image, '//')) ? esc_url($item_image) : constructly_esc_block_image_src($item_image); ?>" alt="<?php echo esc_attr($item_image_alt); ?>" width="400" height="240" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="bm-card-article__body">
                                <?php if ($item_tag !== '') : ?>
                                    <span class="bm-chip bm-chip--category"><?php echo esc_html($item_tag); ?></span>
                                <?php endif; ?>
                                <?php if ($item_title !== '') : ?>
                                    <h3 class="bm-card-article__title"><?php echo esc_html($item_title); ?></h3>
                                <?php endif; ?>
                                <?php if ($item_text !== '') : ?>
                                    <p class="bm-card-article__excerpt"><?php echo esc_html($item_text); ?></p>
                                <?php endif; ?>
                                <?php if ($item_read_time !== '' || $item_date !== '') : ?>
                                    <div class="bm-card-article__meta">
                                        <?php if ($item_read_time !== '') : ?>
                                            <span class="bm-card-article__meta-item">
                                                <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                                                    <use href="#bm-icon-clock"></use>
                                                </svg>
                                                <span><?php echo esc_html($item_read_time); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($item_date !== '') : ?>
                                            <span class="bm-card-article__meta-item">
                                                <svg class="bm-icon bm-card-article__meta-icon" aria-hidden="true">
                                                    <use href="#bm-icon-calendar"></use>
                                                </svg>
                                                <span><?php echo esc_html($item_date); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
