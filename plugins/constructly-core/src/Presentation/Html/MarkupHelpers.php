<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Html;

/**
 * Pure, argument-deterministic HTML markup helpers.
 *
 * Extracted from EstimateShortcode without behavior changes: each method is
 * stateless and returns markup identical to the original inline implementation.
 */
final class MarkupHelpers
{
    /**
     * Renders a bm-tooltip trigger + template for the new unified tooltip system.
     *
     * Pattern mirrors the pile-fields reference markup (data-bm-tooltip / bm-tooltip-tpl).
     * The trigger carries NO .bm-tooltip class to avoid collision with the old system.
     *
     * @param string      $title  Short label used in aria-label (e.g. "Длина плитки").
     * @param string      $text   Body paragraph text, escaped via esc_html.
     * @param string|null $image  Basename (no extension) of the image inside
     *                            assets/img/tooltips/{$image}.jpg — or null for text-only.
     * @param string|null $alt    Alt text for the image; falls back to $title if null.
     */
    public static function renderFieldTooltip(
        string $title,
        string $text,
        ?string $image = null,
        ?string $alt = null
    ): string {
        $ariaLabel = 'Подсказка: ' . $title;
        $mediaHtml = '';

        if ($image !== null) {
            $src = esc_url(
                plugins_url(
                    'assets/img/tooltips/' . $image . '.jpg',
                    // Resolve relative to the plugin root file.
                    \dirname(__DIR__, 3) . '/constructly-core.php'
                )
            );
            $altAttr = esc_attr($alt ?? $title);
            $mediaHtml = '<div class="bm-tooltip__media"><img src="' . $src . '" alt="' . $altAttr . '" loading="lazy"></div>';
        }

        return '<span class="bm-tooltip-anchor">'
            . '<button type="button" class="bm-tooltip-trigger" data-bm-tooltip'
            . ' aria-label="' . esc_attr($ariaLabel) . '"'
            . ' aria-expanded="false">i</button>'
            . '<template class="bm-tooltip-tpl">'
            . $mediaHtml
            . '<p class="bm-tooltip__text">' . esc_html($text) . '</p>'
            . '</template>'
            . '</span>';
    }

    /**
     * Accordion summary chevron (SVG in markup; child theme animates via scaleY).
     */
    public static function accordionChevronMarkup(): string
    {
        return '<span class="brigmaster-estimator__accordion-chevron" aria-hidden="true">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" focusable="false">'
            . '<path d="M2.25 4.25L6 7.75L9.75 4.25" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg></span>';
    }
}
