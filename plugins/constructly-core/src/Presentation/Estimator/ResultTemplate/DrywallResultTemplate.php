<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the drywall estimator.
 *
 * Extracted verbatim from EstimateShortcode::renderDrywallResultTemplate()
 * without behavior changes: stateless, no dependencies on shortcode state.
 */
final class DrywallResultTemplate
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
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-geometry">
                            <h3 class="bm-calculator-result__section-title">Геометрия</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-geometry"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-sheets">
                            <h3 class="bm-calculator-result__section-title">Листы ГКЛ</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-sheets"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-profiles">
                            <h3 class="bm-calculator-result__section-title">Профили</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-profiles"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-fasteners">
                            <h3 class="bm-calculator-result__section-title">Метизы и крепёж</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-fasteners"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-finishing" hidden>
                            <h3 class="bm-calculator-result__section-title">Отделка</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-finishing"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="drywall-costs" hidden>
                            <h3 class="bm-calculator-result__section-title">Стоимость</h3>
                            <div class="bm-calculator-result__list" data-result-list="drywall-costs"></div>
                        </section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Уточняйте у поставщика актуальные форматы листов и длины профилей.</span>
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
