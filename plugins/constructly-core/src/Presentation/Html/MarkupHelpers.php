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
    public static function renderEstimatorTooltip(string $tooltipId, string $content): string
    {
        return '<span class="brigmaster-estimator__tooltip-anchor">'
            . '<button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка" aria-expanded="false" aria-controls="' . esc_attr($tooltipId) . '">i</button>'
            . '<div id="' . esc_attr($tooltipId) . '" class="brigmaster-estimator__tooltip" role="tooltip" hidden>' . esc_html($content) . '</div>'
            . '</span>';
    }

    public static function renderTileTooltip(string $tooltipId, string $content): string
    {
        return self::renderEstimatorTooltip($tooltipId, $content);
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
