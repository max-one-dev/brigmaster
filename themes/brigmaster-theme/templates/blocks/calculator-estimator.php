<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$shortcode_tag = sanitize_key((string) ($attributes['shortcodeTag'] ?? ''));
$shortcode_title = (string) ($attributes['shortcodeTitle'] ?? '');
$info_title = (string) ($attributes['infoTitle'] ?? '');
$info_text = (string) ($attributes['infoText'] ?? '');
$info_body = (string) ($attributes['infoBody'] ?? '');
$method_title = (string) ($attributes['methodTitle'] ?? '');
$method_items = is_array($attributes['methodItems'] ?? null) ? $attributes['methodItems'] : [];
$note_text = (string) ($attributes['noteText'] ?? '');
$note_link_label = (string) ($attributes['noteLinkLabel'] ?? '');
$note_link_url = (string) ($attributes['noteLinkUrl'] ?? '');
$result_title = (string) ($attributes['resultTitle'] ?? '');
$result_status = (string) ($attributes['resultStatus'] ?? '');
$result_text = (string) ($attributes['resultText'] ?? '');
?>
<section class="bm-section bm-section--muted" aria-label="Форма расчёта">
    <div class="bm-container">
        <div class="bm-calculator-layout">
            <div class="bm-calculator-layout__main">
                <?php if ($shortcode_tag !== '') : ?>
                    <?php echo do_shortcode(sprintf('[%s title="%s"]', $shortcode_tag, esc_attr($shortcode_title))); ?>
                <?php endif; ?>

                <section class="bm-calculator-info-card" aria-labelledby="how-calculator-works-title">
                    <?php if ($info_title !== '') : ?>
                        <h2 id="how-calculator-works-title" class="bm-calculator-info-card__title"><?php echo esc_html($info_title); ?></h2>
                    <?php endif; ?>
                    <?php if ($info_body !== '') : ?>
                        <?php
$bm_info_allowed = wp_kses_allowed_html('post');
$bm_info_allowed['span'] = array_merge($bm_info_allowed['span'] ?? [], [
    'data-tooltip' => true,
    'tabindex'     => true,
]);
echo wp_kses($info_body, $bm_info_allowed);
?>
                    <?php else : ?>
                        <?php /* Legacy fallback for blocks authored before the single HTML field. */ ?>
                        <?php if ($info_text !== '') : ?>
                            <p><?php echo esc_html($info_text); ?></p>
                        <?php endif; ?>
                        <?php if ($method_title !== '') : ?>
                            <h3><?php echo esc_html($method_title); ?></h3>
                        <?php endif; ?>
                        <?php if ($method_items !== []) : ?>
                            <ol>
                                <?php foreach ($method_items as $item) : ?>
                                    <?php
                                    $item_text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item;
                                    ?>
                                    <?php if ($item_text !== '') : ?>
                                        <li><?php echo esc_html($item_text); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($note_text !== '' || ($note_link_label !== '' && $note_link_url !== '')) : ?>
                        <aside class="bm-info-block">
                            <span class="bm-info-block__icon" aria-hidden="true">
                                <svg class="bm-icon bm-info-block__icon-svg">
                                    <use href="#bm-icon-info-circle"></use>
                                </svg>
                            </span>
                            <p class="bm-info-block__text">
                                <?php if ($note_text !== '') : ?>
                                    <strong><?php echo esc_html($note_text); ?></strong>
                                <?php endif; ?>
                                <?php if ($note_link_label !== '' && $note_link_url !== '') : ?>
                                    <a class="bm-link bm-link--arrow" href="<?php echo constructly_esc_block_href($note_link_url); ?>"><?php echo esc_html($note_link_label); ?></a>
                                <?php endif; ?>
                            </p>
                        </aside>
                    <?php endif; ?>
                </section>
            </div>

            <?php if ($result_title !== '' || $result_text !== '') : ?>
                <aside id="calculator-result-panel" class="bm-calculator-layout__aside" aria-labelledby="calculator-result-title" data-result-panel>
                    <div class="bm-calculator-result">
                        <div class="bm-calculator-result__head">
                            <?php if ($result_title !== '') : ?>
                                <h2 id="calculator-result-title" class="bm-calculator-result__title"><?php echo esc_html($result_title); ?></h2>
                            <?php endif; ?>
                            <?php if ($result_status !== '') : ?>
                                <span class="bm-calculator-result__status"><?php echo esc_html($result_status); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($result_text !== '') : ?>
                            <div class="bm-calculator-result__note">
                                <span class="bm-calculator-result__note-icon" aria-hidden="true">
                                    <svg class="bm-icon">
                                        <use href="#bm-icon-info-circle"></use>
                                    </svg>
                                </span>
                                <span class="bm-calculator-result__note-content"><?php echo esc_html($result_text); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

                <?php /* Mobile-only control that opens the result drawer; it belongs with the
                         result panel and is a sibling of the aside (not inside it, so its fixed
                         position is not clipped by the drawer transform). Hidden on desktop. */ ?>
                <div class="bm-calculator-sticky-result">
                    <button
                        type="button"
                        class="bm-button bm-button--primary bm-calculator-sticky-result__button"
                        aria-controls="calculator-result-panel"
                        aria-expanded="false"
                        data-result-open
                    >
                        Показать результаты
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
