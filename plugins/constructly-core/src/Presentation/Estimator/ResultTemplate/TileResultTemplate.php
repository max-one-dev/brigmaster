<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the tile estimator.
 *
 * Extracted verbatim from EstimateShortcode::renderTileResultTemplate()
 * without behavior changes: stateless, no dependencies on shortcode state.
 */
final class TileResultTemplate
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
                        <section class="bm-calculator-result__breakdown" data-result-card="tile-summary">
                            <h3 class="bm-calculator-result__section-title">Плитка</h3>
                            <div class="bm-calculator-result__list" data-result-list="tile-summary"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="tile-layout">
                            <h3 class="bm-calculator-result__section-title">Раскладка</h3>
                            <div class="bm-calculator-result__list" data-result-list="tile-layout"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="tile-adhesive" hidden>
                            <h3 class="bm-calculator-result__section-title">Клей</h3>
                            <div class="bm-calculator-result__list" data-result-list="tile-adhesive"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="tile-grout" hidden>
                            <h3 class="bm-calculator-result__section-title">Затирка</h3>
                            <div class="bm-calculator-result__list" data-result-list="tile-grout"></div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="tile-costs" hidden>
                            <h3 class="bm-calculator-result__section-title">Стоимость</h3>
                            <div class="bm-calculator-result__list" data-result-list="tile-costs"></div>
                        </section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Фактический расход зависит от формата плитки, способа укладки и условий работ.</span>
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
