<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the brick estimator.
 *
 * Extracted verbatim from EstimateShortcode::renderBrickResultTemplate()
 * without behavior changes: stateless, no dependencies on shortcode state.
 */
final class BrickResultTemplate
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
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-summary">
                            <h3 class="bm-calculator-result__section-title">Кирпич</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-summary"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-geometry">
                            <h3 class="bm-calculator-result__section-title">Геометрия кладки</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-geometry"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-mortar">
                            <h3 class="bm-calculator-result__section-title">Раствор</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-mortar"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-mesh" hidden>
                            <h3 class="bm-calculator-result__section-title">Кладочная сетка</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-mesh"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-lintels" hidden>
                            <h3 class="bm-calculator-result__section-title">Перемычки</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-lintels"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="brick-costs" hidden>
                            <h3 class="bm-calculator-result__section-title">Стоимость</h3>
                            <div class="bm-calculator-result__list" data-result-list="brick-costs"></div>
                        </section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Фактический расход зависит от качества материалов и условий кладки.</span>
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
