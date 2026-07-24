<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic slab foundation estimator field markup.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, returns identical markup. Field IDs are derived from $instanceId
 * exactly as EstimateShortcode derives them.
 */
final class SlabFoundationFields
{
    public static function render(string $instanceId): string
    {
        $lengthFieldId = $instanceId . 'length';
        $widthFieldId = $instanceId . 'width';
        $areaFieldId = $instanceId . 'area';
        $heightFieldId = $instanceId . 'height';
        $includeReinforcementFieldId = $instanceId . 'include-reinforcement';
        $reinforcementModeLockTooltipId = $instanceId . 'reinforcement-mode-lock-tooltip';
        $rebarDiameterFieldId = $instanceId . 'rebar-diameter-mm';
        $rebarDiameterTooltipId = $instanceId . 'rebar-diameter-tooltip';
        $rebarStepFieldId = $instanceId . 'rebar-step-mm';
        $rebarStepTooltipId = $instanceId . 'rebar-step-tooltip';
        $rebarLayersFieldId = $instanceId . 'rebar-layers';
        $rebarLayersTooltipId = $instanceId . 'rebar-layers-tooltip';
        $rebarReserveFieldId = $instanceId . 'rebar-reserve-percent';
        $rebarReserveTooltipId = $instanceId . 'rebar-reserve-tooltip';
        $includeFormworkFieldId = $instanceId . 'include-formwork';
        $formworkModeLockTooltipId = $instanceId . 'formwork-mode-lock-tooltip';
        $formworkHeightFieldId = $instanceId . 'formwork-height-m';
        $formworkHeightTooltipId = $instanceId . 'formwork-height-tooltip';
        $formworkReserveFieldId = $instanceId . 'formwork-reserve-percent';
        $formworkReserveTooltipId = $instanceId . 'formwork-reserve-tooltip';

        ob_start();
        ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Длина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: длина" aria-expanded="false" aria-controls="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите длину в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="8">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Ширина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите ширину в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="slab-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: площадь" aria-expanded="false" aria-controls="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите площадь в м² (например, 48).</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="48">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота" aria-expanded="false" aria-controls="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите высоту в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.25">
                                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="reinforcement">
                        <input id="<?php echo esc_attr($includeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1">
                        <label for="<?php echo esc_attr($includeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать арматуру</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>">i</button>
                                <div id="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчета арматуры нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Диаметр влияет на массу арматуры. Стандартно для частного дома 10–14 мм.</div>
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
                                            <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Шаг сетки между стержнями, обычно 150–250 мм. Чем меньше шаг, тем выше расход.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: слои арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarLayersTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarLayersTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>1 слой для лёгких нагрузок, 2 слоя — стандарт для жилых домов.</div>
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

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="formwork">
                        <input id="<?php echo esc_attr($includeFormworkFieldId); ?>" type="checkbox" name="includeFormwork" value="1">
                        <label for="<?php echo esc_attr($includeFormworkFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать опалубку</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: опалубка недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkModeLockTooltipId); ?>">i</button>
                                <div id="<?php echo esc_attr($formworkModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчета опалубки нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeFormwork" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="formwork">
                        <summary class="brigmaster-estimator__accordion-summary">Опалубка<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Высота опалубки (м)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkHeightTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($formworkHeightTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Опалубка считается по периметру плиты и указанной высоте. Обычно равна высоте заливки.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.30">
                                    <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас опалубки (%)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkReserveTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($formworkReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Рекомендуется 5–15% на подрезку и стыковки.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($formworkReserveFieldId); ?>" type="number" name="formworkReservePercent" min="1" step="1" value="10">
                                    <div class="brigmaster-estimator__error" data-field-error="formworkReservePercent" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </details>
        <?php
        return (string) ob_get_clean();
    }
}
