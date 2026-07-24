<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\ResultTemplate;

/**
 * Pure, argument-deterministic result-panel markup for the strip foundation estimator.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, no dependencies on shortcode state.
 */
final class StripResultTemplate
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
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-concrete">
                            <h3 class="bm-calculator-result__section-title">Бетон</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Общая длина ленты</span>
                                        <strong><span data-result-strip-concrete-length>-</span> м</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Объём бетона
                                            <button class="bm-tooltip" type="button" data-tooltip="Объём с учётом технологического запаса на потери при заливке." aria-label="Подсказка: объём бетона">
                                                <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg>
                                            </button>
                                        </span>
                                        <strong><span data-result-strip-concrete-volume>-</span> м³</strong>
                                    </span>
                                </div>
                            </div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-mixture" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-reinforcement" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-formwork" hidden>
                            <h3 class="bm-calculator-result__section-title">Опалубка</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Суммарная площадь щитов
                                            <button class="bm-tooltip" type="button" data-tooltip="Площадь боковых поверхностей ленты с запасом на раскрой пиломатериала." aria-label="Подсказка: площадь опалубки">
                                                <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg>
                                            </button>
                                        </span>
                                        <strong><span data-result-strip-formwork-area>-</span> м²</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Погонные метры</span>
                                        <strong><span data-result-strip-formwork-linear>-</span> м</strong>
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
