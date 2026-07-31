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
        $includeFormworkFieldId = $instanceId . 'include-formwork';
        $rebarDiameterFieldId = $instanceId . 'rebar-diameter-mm';
        $rebarStepFieldId = $instanceId . 'rebar-step-mm';
        $rebarLayersFieldId = $instanceId . 'rebar-layers';
        $rebarReserveFieldId = $instanceId . 'rebar-reserve-percent';
        $formworkHeightFieldId = $instanceId . 'formwork-height-m';
        $formworkReserveFieldId = $instanceId . 'formwork-reserve-percent';

        ob_start();
        ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Длина (м)</span>
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="8">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Ширина (м)</span>
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="slab-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Площадь', 'Режим по площади: считаются бетон и материалы, но арматура и опалубка не рассчитываются – для них нужны длина и ширина.'); ?>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="48">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота (м)</span>
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
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock"><?php echo MarkupHelpers::renderFieldTooltip('Учитывать арматуру', 'Расчёт арматуры доступен только в режиме «По размерам» – нужны длина и ширина для раскладки сетки.'); ?></span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Диаметр арматуры', 'Для монолитной плиты обычно 10–14 мм: 10–12 мм для лёгких нагрузок, 14 мм для жилого дома.', 'rebar-diameter'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Шаг арматуры (мм)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Шаг арматуры', 'Шаг сетки между стержнями. Типично 150–250 мм: 150–200 мм для жилого дома, 250 мм для лёгких нагрузок.', 'rebar-spacing'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Слои арматуры', '1 слой – для лёгких нагрузок; 2 слоя – стандарт для монолитной плиты жилого дома.'); ?>
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
                                        <?php echo MarkupHelpers::renderFieldTooltip('Запас арматуры', '5–10% для простой формы; 10–15% при большом числе стыков и подрезки. Покрывает нахлёсты, отходы и брак.'); ?>
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
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock"><?php echo MarkupHelpers::renderFieldTooltip('Учитывать опалубку', 'Расчёт опалубки доступен только в режиме «По размерам» – нужен периметр плиты.'); ?></span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeFormwork" aria-live="polite"></div>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="formwork">
                        <summary class="brigmaster-estimator__accordion-summary">Опалубка<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Высота опалубки (м)</span>
                                    </label>
                                    <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.30">
                                    <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас опалубки (%)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Запас опалубки', '5–10% на подрезку и стыки щитов. Учитывает только материал щитов.'); ?>
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
