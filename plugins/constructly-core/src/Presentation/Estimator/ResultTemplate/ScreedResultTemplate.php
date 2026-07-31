<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the screed estimator.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, no dependencies on shortcode state.
 */
final class ScreedResultTemplate
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
                        <section class="bm-calculator-result__breakdown" data-result-card="screed-concrete">
                            <h3 class="bm-calculator-result__section-title">Стяжка</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material" data-result-screed-area-row>
                                    <span class="bm-calculator-result__material-head">
                                        <span>Площадь</span>
                                        <strong><span data-result-screed-area>-</span> м²</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Объём смеси
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip="<?php echo esc_attr('Объём раствора с учётом технологического запаса.'); ?>" aria-label="<?php echo esc_attr('Подсказка: объём смеси'); ?>" aria-expanded="false">i</button>
                                        </span>
                                        <strong><span data-result-volume>-</span> м³</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Высота</span>
                                        <strong><span data-result-screed-height>-</span> м</strong>
                                    </span>
                                </div>
                                <div data-result-card="mixture" hidden></div>
                            </div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="screed-reinforcement" hidden>
                            <h3 class="bm-calculator-result__section-title">Арматура (сетка, ориентир)</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Масса с запасом</span>
                                        <strong><span data-result-screed-rebar-mass>-</span> кг</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Общая длина с запасом</span>
                                        <strong><span data-result-screed-rebar-length>-</span> м</strong>
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Фактический расход зависит от основания, влажности и условий работ.</span>
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
