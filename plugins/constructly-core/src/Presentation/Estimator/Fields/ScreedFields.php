<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic screed estimator field markup.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, returns identical markup. Field IDs are derived from $instanceId
 * exactly as EstimateShortcode derives them.
 */
final class ScreedFields
{
    public static function render(string $instanceId): string
    {
        $screedIncludeReinforcementFieldId = $instanceId . 'screed-include-reinforcement';
        $lengthFieldId = $instanceId . 'length';
        $widthFieldId = $instanceId . 'width';
        $areaFieldId = $instanceId . 'area';
        $heightFieldId = $instanceId . 'height';
        $rebarDiameterFieldId = $instanceId . 'rebar-diameter-mm';
        $rebarDiameterTooltipId = $instanceId . 'rebar-diameter-tooltip';
        $rebarStepFieldId = $instanceId . 'rebar-step-mm';
        $rebarStepTooltipId = $instanceId . 'rebar-step-tooltip';
        $rebarLayersFieldId = $instanceId . 'rebar-layers';
        $rebarReserveFieldId = $instanceId . 'rebar-reserve-percent';
        $rebarReserveTooltipId = $instanceId . 'rebar-reserve-tooltip';

        ob_start();
        ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Длина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: длина" aria-expanded="false" aria-controls="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите длину помещения в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Ширина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите ширину помещения в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="screed-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: площадь" aria-expanded="false" aria-controls="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите площадь в м².</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="24">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота стяжки (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота стяжки" aria-expanded="false" aria-controls="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Укажите среднюю высоту стяжки по всей площади. В метрах: 0.05 = 5 см.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.05">
                                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', true, false); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="screed-reinforcement">
                        <input id="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1">
                        <label for="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать арматуру</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--hidden">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>">i</button>
                                <div id="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчёта арматуры нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="screed-reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Диаметр влияет на массу арматуры. Обычно 10–14 мм.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Шаг арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarStepTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Шаг сетки между стержнями, обычно 150–250 мм. Чем меньше шаг, тем плотнее сетка.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: слои арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'screed-rebar-layers-tooltip'); ?>">i</button>
                                            <div id="<?php echo esc_attr($instanceId . 'screed-rebar-layers-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для стяжки часто 1 слой сетки.</div>
                                        </span>
                                    </label>
                                    <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers">
                                        <option value="1">1 слой</option>
                                        <option value="2" selected>2 слоя</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас арматуры (%)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarReserveTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Рекомендуемый запас 5–15% на подрезку и нахлёсты.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarReserveFieldId); ?>" type="number" name="rebarReservePercent" min="1" step="1" value="10">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarReservePercent" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </details>
        <?php
        return (string) ob_get_clean();
    }
}
