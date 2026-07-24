<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the pile foundation estimator.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, no dependencies on shortcode state.
 */
final class PileResultTemplate
{
    public static function render(): string
    {
        ob_start();
        ?>
                    <div class="bm-calculator-result__head">
                        <h2 class="bm-calculator-result__title">Результаты расчёта</h2>
                        <span class="bm-calculator-result__status">Готово</span>
                    </div>
                    <div class="bm-calculator-result__sections">
                        <section class="bm-calculator-result__breakdown" data-result-card="pile-info" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="pile-concrete" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="pile-reinforcement" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="pile-mixture" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="grillage-concrete" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="grillage-reinforcement" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="grillage-formwork" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="grillage-mixture" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="unified-mixture" hidden></section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Для точного проектирования выполните проект и геологические изыскания.</span>
                        </span>
                    </div>
                    <div class="bm-calculator-result__actions">
                        <button type="button" class="bm-button bm-button--primary" data-result-action="download">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-download"></use></svg>
                            <span>Открыть PDF</span>
                        </button>
                        <button type="button" class="bm-button bm-button--secondary" data-result-action="copy-link">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-link"></use></svg>
                            <span>Скопировать ссылку</span>
                        </button>
                        <button type="button" class="bm-button bm-button--secondary" data-result-action="print">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-print"></use></svg>
                            <span>Распечатать</span>
                        </button>
                    </div>
                    <p class="bm-calculator-result__save">Сохранение результата в проект появится позже.</p>
        <?php
        return (string) ob_get_clean();
    }
}
