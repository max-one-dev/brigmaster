<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reading layout: left sidebar (auto-generated TOC + a soft "next step" card) and
 * a prose column. $content holds the rendered InnerBlocks (the prose authored with
 * Gutenberg blocks). The TOC list is built from the prose headings by toc.js. The
 * sidebar card copy is set per page via attributes (not editable in the inspector).
 */
$content = (string) ($content ?? '');
$sidebar_title = (string) ($attributes['sidebarTitle'] ?? '');
$sidebar_text = (string) ($attributes['sidebarText'] ?? '');
$button_label = (string) ($attributes['buttonLabel'] ?? '');
$button_url = (string) ($attributes['buttonUrl'] ?? '');
$sidebar_image = (string) ($attributes['sidebarImage'] ?? '');
$has_card = $sidebar_title !== '' || $sidebar_text !== '' || ($button_label !== '' && $button_url !== '');
$card_classes = 'bm-sidebar-card bm-sidebar-card--soft' . ($sidebar_image !== '' ? ' bm-sidebar-card--media' : '');
?>
<section class="bm-section bm-section--tight">
    <div class="bm-container">
        <div class="bm-content-layout bm-content-layout--with-left-sidebar">
            <aside class="bm-content-layout__sidebar" aria-label="Навигация по странице">
                <nav class="bm-toc bm-toc--collapsible bm-toc--in-sidebar bm-sidebar-card" data-bm-component="toc" aria-label="Содержание">
                    <button type="button" class="bm-toc__toggle" aria-expanded="false" aria-controls="content-layout-toc-panel">
                        <span>Содержание</span>
                        <svg class="bm-icon bm-toc__toggle-icon" aria-hidden="true">
                            <use href="#bm-icon-chevron-down"></use>
                        </svg>
                    </button>
                    <div id="content-layout-toc-panel" class="bm-toc__panel">
                        <div class="bm-toc__panel-inner">
                            <h2 class="bm-toc__title">Содержание</h2>
                            <ol class="bm-toc__list"></ol>
                        </div>
                    </div>
                </nav>

                <?php
                if ($has_card) :
                    // The media variant wraps copy in __content next to the image; the
                    // plain variant keeps copy as direct children of the card.
                    $card_inner_open = $sidebar_image !== '' ? '<div class="bm-sidebar-card__content">' : '';
                    $card_inner_close = $sidebar_image !== '' ? '</div>' : '';
                    ?>
                    <aside class="<?php echo esc_attr($card_classes); ?>">
                        <?php if ($sidebar_image !== '') : ?>
                            <img class="bm-sidebar-card__image" src="<?php echo esc_url(constructly_esc_block_image_src($sidebar_image)); ?>" alt="" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <?php echo $card_inner_open; // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
                            <?php if ($sidebar_title !== '') : ?>
                                <h2 class="bm-sidebar-card__title"><?php echo esc_html($sidebar_title); ?></h2>
                            <?php endif; ?>
                            <?php if ($sidebar_text !== '') : ?>
                                <p class="bm-sidebar-card__text"><?php echo esc_html($sidebar_text); ?></p>
                            <?php endif; ?>
                            <?php if ($button_label !== '' && $button_url !== '') : ?>
                                <div class="bm-sidebar-card__action">
                                    <a href="<?php echo constructly_esc_block_href($button_url); ?>" class="bm-button bm-button--secondary"><?php echo esc_html($button_label); ?></a>
                                </div>
                            <?php endif; ?>
                        <?php echo $card_inner_close; // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
                    </aside>
                <?php endif; ?>
            </aside>

            <article class="bm-content-layout__main bm-prose">
                <?php echo $content; ?>
            </article>
        </div>
    </div>
</section>
