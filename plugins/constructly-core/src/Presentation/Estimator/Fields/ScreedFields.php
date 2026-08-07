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
        $rebarStepFieldId = $instanceId . 'rebar-step-mm';
        $rebarLayersFieldId = $instanceId . 'rebar-layers';
        $rebarReserveFieldId = $instanceId . 'rebar-reserve-percent';

        ob_start();
        ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>">
                                    Длина (м)
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>">
                                    Ширина (м)
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="screed-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Площадь', 'Режим по площади: арматура не рассчитывается – для неё нужны длина и ширина.'); ?>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="24">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота стяжки (м)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Высота стяжки', 'Средняя толщина по всей площади. Минимум по СП 29.13330 – 30 мм (0,03 м) без армирования, чаще 50–70 мм. В метрах: 0,05 = 5 см.'); ?>
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
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock">
                                <?php echo MarkupHelpers::renderFieldTooltip('Учитывать арматуру', 'Расчёт арматуры доступен только в режиме «По размерам» – нужны длина и ширина для раскладки сетки.'); ?>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="screed-reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Диаметр арматуры', 'Для бытовой стяжки – сварная сетка Ø3–5 мм (Вр-I, ГОСТ 23279); Ø6–8 мм (A400/A500) для нагруженных полов и стяжек от 80 мм.', 'rebar-diameter'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="4">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Шаг арматуры (мм)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Шаг арматуры', 'Размер ячейки сетки. Для жилья 100×100 или 150×150 мм; 200×200 мм – для толстых стяжек от 80 мм при умеренных нагрузках.', 'rebar-spacing'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Слои арматуры', 'До 80 мм – 1 слой сетки в нижней трети (15–20 мм от низа). 2 слоя – только для стяжек от 100 мм при значительных нагрузках.'); ?>
                                    </label>
                                    <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers">
                                        <option value="1" selected>1 слой</option>
                                        <option value="2">2 слоя</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас арматуры (%)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Запас арматуры', '5–10% / 10–15% на нахлёсты, отходы и брак.'); ?>
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
