<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Strip/pile foundation estimator field markup (shared block; $calculator-driven branches).
 *
 * Extracted verbatim from EstimateShortcode without behavior changes: stateless,
 * returns identical markup. Field IDs are derived from $instanceId exactly as EstimateShortcode.
 */
final class StripPileFields
{
    public static function render(string $instanceId, string $calculator): string
    {
        $modeFieldId = $instanceId . 'mode';
        $totalLengthFieldId = $instanceId . 'total-length-m';
        $widthMFieldId = $instanceId . 'width-m';
        $heightMFieldId = $instanceId . 'height-m';
        $houseLengthFieldId = $instanceId . 'house-length-m';
        $houseWidthFieldId = $instanceId . 'house-width-m';
        $houseStripWidthFieldId = $instanceId . 'strip-section-width-m';
        $longitudinalBarsCountFieldId = $instanceId . 'longitudinal-bars-count';
        $longitudinalDiameterFieldId = $instanceId . 'longitudinal-diameter-mm';
        $longitudinalReserveFieldId = $instanceId . 'longitudinal-reserve-percent';
        $transverseDiameterFieldId = $instanceId . 'transverse-diameter-mm';
        $transverseStepFieldId = $instanceId . 'transverse-step-mm';
        $transverseReserveFieldId = $instanceId . 'transverse-reserve-percent';
        $includePilesFieldId = $instanceId . 'include-piles';
        $includeGrillageFieldId = $instanceId . 'include-grillage';
        $pileTypeFieldId = $instanceId . 'pile-type';
        $pilesCountFieldId = $instanceId . 'piles-count';
        $pileShaftDiameterFieldId = $instanceId . 'pile-shaft-diameter-m';
        $pileShaftHeightFieldId = $instanceId . 'pile-shaft-height-m';
        $includePileBaseFieldId = $instanceId . 'include-pile-base';
        $pileBaseDiameterFieldId = $instanceId . 'pile-base-diameter-m';
        $pileBaseHeightFieldId = $instanceId . 'pile-base-height-m';
        $includePileReinforcementFieldId = $instanceId . 'include-pile-reinforcement';
        $pileReinforcementBarsCountFieldId = $instanceId . 'pile-reinforcement-bars-count';
        $pileReinforcementDiameterFieldId = $instanceId . 'pile-reinforcement-diameter-mm';
        $pileReinforcementReserveFieldId = $instanceId . 'pile-reinforcement-reserve-percent';
        $formworkHeightFieldId = $instanceId . 'formwork-height-m';
        $formworkReserveFieldId = $instanceId . 'formwork-reserve-percent';
        $grillageModeHintId = $instanceId . 'grillage-mode-hint';

        ob_start();
        ?>
                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="grillage-toggle">
                        <input id="<?php echo esc_attr($includeGrillageFieldId); ?>" type="checkbox" name="includeGrillage" value="1" checked>
                        <label for="<?php echo esc_attr($includeGrillageFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Рассчитать ростверк</span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeGrillage" aria-live="polite"></div>
                    </div>
                <?php endif; ?>

                <?php if (in_array($calculator, ['strip_foundation', 'pile_foundation'], true)) : ?>
                    <?php $stripLabel = $calculator === 'pile_foundation' ? 'ростверка' : 'ленты'; ?>

                    <?php if ($calculator === 'strip_foundation') : ?>
                    <!-- П.1: Геометрия ленты — flat, всегда видима (вне аккордеона) -->
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-perimeter">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($totalLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Общая длина ленты (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($totalLengthFieldId); ?>" type="number" name="totalLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="totalLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина ленты (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($widthMFieldId); ?>" type="number" name="widthM" min="0.01" step="0.01" value="0.4">
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота ленты (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($heightMFieldId); ?>" type="number" name="heightM" min="0.01" step="0.01" value="1">
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-field-group="strip-house">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseLengthFieldId); ?>">Длина дома (м)</label>
                            <input id="<?php echo esc_attr($houseLengthFieldId); ?>" type="number" name="houseLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="houseLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseWidthFieldId); ?>">Ширина дома (м)</label>
                            <input id="<?php echo esc_attr($houseWidthFieldId); ?>" type="number" name="houseWidthM" min="0.01" step="0.01" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="houseWidthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseStripWidthFieldId); ?>">Ширина ленты (м)</label>
                            <input id="<?php echo esc_attr($houseStripWidthFieldId); ?>" type="number" name="houseModeWidthM" min="0.01" step="0.01" value="0.4" data-strip-house-width-input>
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'house-height-m'); ?>">Высота ленты (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'house-height-m'); ?>" type="number" name="houseModeHeightM" min="0.01" step="0.01" value="1" data-strip-house-height-input>
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="strip-segments">
                        <div class="brigmaster-estimator__segment-list" data-strip-segments-list>
                            <article class="brigmaster-estimator__segment-card" data-strip-segment-item data-segment-index="0">
                                <div class="brigmaster-estimator__segment-head">
                                    <h3 class="brigmaster-estimator__segment-title">Участок 1</h3>
                                    <button type="button" class="brigmaster-estimator__segment-remove" data-strip-remove-segment disabled>Удалить</button>
                                </div>
                                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>">Длина участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>" type="number" min="0.01" step="0.01" value="10" data-segment-input="segmentLengthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentLengthM" data-field-error="segments.0.segmentLengthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>">Ширина участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>" type="number" min="0.01" step="0.01" value="0.4" data-segment-input="segmentWidthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentWidthM" data-field-error="segments.0.segmentWidthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>">Высота участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>" type="number" min="0.01" step="0.01" value="1" data-segment-input="segmentHeightM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentHeightM" data-field-error="segments.0.segmentHeightM" aria-live="polite"></div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-rebar-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" type="checkbox" data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-rebar">
                                                <span>Учитывать арматуру для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeReinforcement" data-field-error="segments.0.segmentIncludeReinforcement" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-rebar-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Кол-во продольных стержней</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Кол-во продольных стержней', 'Число рабочих стержней в поперечном сечении. Для частного дома обычно 4–6: 4 при сечении до 400×600 мм, 6 при большем.'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" type="number" min="1" step="1" value="4" data-segment-input="segmentLongitudinalBarsCount">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalBarsCount" data-field-error="segments.0.segmentLongitudinalBarsCount" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр продольной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр продольной', 'Диаметр рабочих (продольных) стержней. Типично 10–14 мм. Чем больше диаметр, тем выше масса и несущая способность.', 'rebar-diameter'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" type="number" min="1" step="1" value="12" data-segment-input="segmentLongitudinalDiameterMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalDiameterMm" data-field-error="segments.0.segmentLongitudinalDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр поперечной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр поперечной', 'Диаметр хомутов. Обычно 6–10 мм: 6–8 мм для частного дома, 10 мм при повышенных требованиях.', 'stirrup-diameter'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" type="number" min="1" step="1" value="8" data-segment-input="segmentTransverseDiameterMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseDiameterMm" data-field-error="segments.0.segmentTransverseDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Шаг поперечной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Шаг поперечной', 'Расстояние между хомутами. Типично 200–400 мм: у опор чаще (200–250), в пролёте реже (300–400). Меньший шаг – больше стали.', 'stirrup-spacing'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" type="number" min="10" step="10" value="300" data-segment-input="segmentTransverseStepMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseStepMm" data-field-error="segments.0.segmentTransverseStepMm" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-formwork-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" type="checkbox" data-segment-include-formwork data-checkbox-key="segment-include-formwork">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-formwork">
                                                <span>Учитывать опалубку для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeFormwork" data-field-error="segments.0.segmentIncludeFormwork" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-formwork-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>">Высота опалубки участка (м)</label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>" type="number" min="0.01" step="0.01" value="0.8" data-segment-input="segmentFormworkHeightM">
                                            <p class="brigmaster-estimator__hint">Считаются только боковые щиты участка.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentFormworkHeightM" data-field-error="segments.0.segmentFormworkHeightM" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <button type="button" class="brigmaster-estimator__segment-add" data-strip-add-segment>
                            Добавить участок ленты
                        </button>
                        <div class="brigmaster-estimator__error" data-field-error="segments" aria-live="polite"></div>
                    </div>

                    <!-- П.4: Смесь сразу после геометрии ленты -->
                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>
                    <?php endif; /* strip_foundation geometry + mixture */ ?>

                    <?php if ($calculator === 'pile_foundation') : ?>
                    <?php $stripLabel = $calculator === 'pile_foundation' ? 'ростверка' : 'ленты'; ?>
                        <details class="brigmaster-estimator__accordion" open<?php echo $calculator === 'pile_foundation' ? ' data-pile-panel="grillage"' : ''; ?>>
                            <summary class="brigmaster-estimator__accordion-summary"><?php echo $calculator === 'pile_foundation' ? 'Геометрия ростверка' : 'Геометрия ленты'; ?><?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                    <?php if ($calculator === 'pile_foundation') : ?>
                        <div class="brigmaster-estimator__field" data-field-group="estimator-mode">
                            <label for="<?php echo esc_attr($modeFieldId); ?>">Режим расчета ростверка</label>
                            <select id="<?php echo esc_attr($modeFieldId); ?>" name="mode" required aria-describedby="<?php echo esc_attr($grillageModeHintId); ?>">
                                <option value="perimeter">По общей длине ростверка</option>
                                <option value="house">По параметрам дома</option>
                                <option value="segments">По участкам ростверка</option>
                            </select>
                            <p id="<?php echo esc_attr($grillageModeHintId); ?>" class="brigmaster-estimator__mode-hint" data-mode-hint aria-live="polite"></p>
                            <div class="brigmaster-estimator__error" data-field-error="mode" aria-live="polite"></div>
                        </div>
                    <?php endif; ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-perimeter">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($totalLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Общая длина <?php echo esc_html($stripLabel); ?> (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($totalLengthFieldId); ?>" type="number" name="totalLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="totalLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина <?php echo esc_html($stripLabel); ?> (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($widthMFieldId); ?>" type="number" name="widthM" min="0.01" step="0.01" value="0.4">
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота <?php echo esc_html($stripLabel); ?> (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($heightMFieldId); ?>" type="number" name="heightM" min="0.01" step="0.01" value="1">
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-field-group="strip-house">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseLengthFieldId); ?>">Длина дома (м)</label>
                            <input id="<?php echo esc_attr($houseLengthFieldId); ?>" type="number" name="houseLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="houseLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseWidthFieldId); ?>">Ширина дома (м)</label>
                            <input id="<?php echo esc_attr($houseWidthFieldId); ?>" type="number" name="houseWidthM" min="0.01" step="0.01" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="houseWidthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseStripWidthFieldId); ?>">Ширина <?php echo esc_html($stripLabel); ?> (м)</label>
                            <input id="<?php echo esc_attr($houseStripWidthFieldId); ?>" type="number" name="houseModeWidthM" min="0.01" step="0.01" value="0.4" data-strip-house-width-input>
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'house-height-m'); ?>">Высота <?php echo esc_html($stripLabel); ?> (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'house-height-m'); ?>" type="number" name="houseModeHeightM" min="0.01" step="0.01" value="1" data-strip-house-height-input>
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="strip-segments">
                        <div class="brigmaster-estimator__segment-list" data-strip-segments-list>
                            <article class="brigmaster-estimator__segment-card" data-strip-segment-item data-segment-index="0">
                                <div class="brigmaster-estimator__segment-head">
                                    <h3 class="brigmaster-estimator__segment-title">Участок 1</h3>
                                    <button type="button" class="brigmaster-estimator__segment-remove" data-strip-remove-segment disabled>Удалить</button>
                                </div>
                                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>">Длина участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>" type="number" min="0.01" step="0.01" value="10" data-segment-input="segmentLengthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentLengthM" data-field-error="segments.0.segmentLengthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>">Ширина участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>" type="number" min="0.01" step="0.01" value="0.4" data-segment-input="segmentWidthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentWidthM" data-field-error="segments.0.segmentWidthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>">Высота участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>" type="number" min="0.01" step="0.01" value="1" data-segment-input="segmentHeightM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentHeightM" data-field-error="segments.0.segmentHeightM" aria-live="polite"></div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-rebar-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" type="checkbox" data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-rebar">
                                                <span>Учитывать арматуру для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeReinforcement" data-field-error="segments.0.segmentIncludeReinforcement" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-rebar-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Кол-во продольных стержней</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Кол-во продольных стержней', 'Число рабочих стержней в поперечном сечении. Для частного дома обычно 4–6: 4 при сечении до 400×600 мм, 6 при большем.'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" type="number" min="1" step="1" value="4" data-segment-input="segmentLongitudinalBarsCount">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalBarsCount" data-field-error="segments.0.segmentLongitudinalBarsCount" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр продольной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр продольной', 'Диаметр рабочих (продольных) стержней. Типично 10–14 мм. Чем больше диаметр, тем выше масса и несущая способность.', 'rebar-diameter'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" type="number" min="1" step="1" value="12" data-segment-input="segmentLongitudinalDiameterMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalDiameterMm" data-field-error="segments.0.segmentLongitudinalDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр поперечной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр поперечной', 'Диаметр хомутов. Обычно 6–10 мм: 6–8 мм для частного дома, 10 мм при повышенных требованиях.', 'stirrup-diameter'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" type="number" min="1" step="1" value="8" data-segment-input="segmentTransverseDiameterMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseDiameterMm" data-field-error="segments.0.segmentTransverseDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Шаг поперечной (мм)</span>
                                                <?php echo MarkupHelpers::renderFieldTooltip('Шаг поперечной', 'Расстояние между хомутами. Типично 200–400 мм: у опор чаще (200–250), в пролёте реже (300–400). Меньший шаг – больше стали.', 'stirrup-spacing'); ?>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" type="number" min="10" step="10" value="300" data-segment-input="segmentTransverseStepMm">
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseStepMm" data-field-error="segments.0.segmentTransverseStepMm" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-formwork-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" type="checkbox" data-segment-include-formwork data-checkbox-key="segment-include-formwork">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-formwork">
                                                <span>Учитывать опалубку для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeFormwork" data-field-error="segments.0.segmentIncludeFormwork" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-formwork-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>">Высота опалубки участка (м)</label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>" type="number" min="0.01" step="0.01" value="0.8" data-segment-input="segmentFormworkHeightM">
                                            <p class="brigmaster-estimator__hint">Считаются только боковые щиты участка.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentFormworkHeightM" data-field-error="segments.0.segmentFormworkHeightM" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <button type="button" class="brigmaster-estimator__segment-add" data-strip-add-segment>
                            <?php echo $calculator === 'pile_foundation' ? 'Добавить участок ростверка' : 'Добавить участок ленты'; ?>
                        </button>
                        <div class="brigmaster-estimator__error" data-field-error="segments" aria-live="polite"></div>
                    </div>
                    <?php endif; /* pile_foundation geometry accordion: продолжается, закрывается после опалубки */ ?>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-reinforcement">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" type="checkbox" name="includeReinforcement" value="1">
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать арматуру ростверка' : 'Учитывать арматуру'; ?></span>
                            </label>
                        </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open data-toggle-target="strip-reinforcement">
                            <summary class="brigmaster-estimator__accordion-summary">Арматура<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                        <?php endif; ?>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-reinforcement-global">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalBarsCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Кол-во продольных стержней</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Кол-во продольных стержней', 'Число рабочих стержней в поперечном сечении. Для частного дома обычно 4–6: 4 при сечении до 400×600 мм, 6 при большем.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalBarsCountFieldId); ?>" type="number" name="longitudinalBarsCount" min="1" step="1" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalBarsCount" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр продольной (мм)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр продольной', 'Диаметр рабочих (продольных) стержней. Типично 10–14 мм. Чем больше диаметр, тем выше масса и несущая способность.', 'rebar-diameter'); ?>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalDiameterFieldId); ?>" type="number" name="longitudinalDiameterMm" min="1" step="1" value="12">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalDiameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас продольной (%)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Запас продольной', '5–10% для простой формы без нахлёстов; 10–15% при большом числе стыков и подрезки. Покрывает нахлёсты, отходы и брак.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalReserveFieldId); ?>" type="number" name="longitudinalReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalReservePercent" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр поперечной (мм)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Диаметр поперечной', 'Диаметр хомутов. Обычно 6–10 мм: 6–8 мм для частного дома, 10 мм при повышенных требованиях.', 'stirrup-diameter'); ?>
                            </label>
                            <input id="<?php echo esc_attr($transverseDiameterFieldId); ?>" type="number" name="transverseDiameterMm" min="1" step="1" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="transverseDiameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг поперечной (мм)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Шаг поперечной', 'Расстояние между хомутами. Типично 200–400 мм: у опор чаще (200–250), в пролёте реже (300–400). Меньший шаг – больше стали.', 'stirrup-spacing'); ?>
                            </label>
                            <input id="<?php echo esc_attr($transverseStepFieldId); ?>" type="number" name="transverseStepMm" min="10" step="10" value="300">
                            <div class="brigmaster-estimator__error" data-field-error="transverseStepMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас поперечной (%)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Запас поперечной', '5–10% для простой формы без нахлёстов; 10–15% при большом числе стыков и подрезки. Покрывает нахлёсты, отходы и брак.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($transverseReserveFieldId); ?>" type="number" name="transverseReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="transverseReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                            </div>
                        </details>
                        <?php endif; ?>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-formwork">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" type="checkbox" name="includeFormwork" value="1">
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать опалубку ростверка' : 'Учитывать опалубку'; ?></span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Учитывать опалубку', 'В расчёт попадают только боковые щиты. Распорки, подкосы и крепёж не учитываются.'); ?>
                            </label>
                        </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open data-toggle-target="strip-formwork">
                            <summary class="brigmaster-estimator__accordion-summary">Опалубка<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                        <?php endif; ?>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="strip-formwork-global">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота опалубки (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.8">
                            <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас опалубки (%)</span>
                                <?php echo MarkupHelpers::renderFieldTooltip('Запас опалубки', '5–10% на подрезку досок/фанеры и стыки щитов. Учитывает только материал щитов – распорки и крепёж отдельно.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($formworkReserveFieldId); ?>" type="number" name="formworkReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="formworkReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                            </div>
                        </details>
                        <?php endif; ?>
                    <?php if ($calculator === 'pile_foundation') : ?>
                            </div>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="piles-toggle">
                        <input id="<?php echo esc_attr($includePilesFieldId); ?>" type="checkbox" name="includePiles" value="1" checked>
                        <label for="<?php echo esc_attr($includePilesFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Рассчитать сваи</span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includePiles" aria-live="polite"></div>
                    </div>
                        <details class="brigmaster-estimator__accordion" open data-pile-panel="piles">
                            <summary class="brigmaster-estimator__accordion-summary">Сваи<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--pile-primary" data-field-group="pile-primary-row" data-pile-primary-grid data-autofit-row>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($pileTypeFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Тип свай</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: тип свай" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <p class="bm-tooltip__text">Для винтовых и забивных свай бетон не требуется. Для буронабивных выполняется расчёт бетона.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <select id="<?php echo esc_attr($pileTypeFieldId); ?>" name="pileType">
                                        <option value="bored">Буронабивные</option>
                                        <option value="screw">Винтовые</option>
                                        <option value="driven">Забивные</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="pileType" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field brigmaster-estimator__field--pile-count">
                                    <label for="<?php echo esc_attr($pilesCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Количество свай</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: количество свай" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <p class="bm-tooltip__text">Количество свай определяют по нагрузке здания и несущей способности одной сваи по геологии участка. Для точного подбора используйте проект.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($pilesCountFieldId); ?>" type="number" name="pilesCount" min="1" step="1" value="20" inputmode="numeric">
                                    <div class="brigmaster-estimator__error" data-field-error="pilesCount" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-diameter">
                                    <label for="<?php echo esc_attr($pileShaftDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр ствола сваи (м)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Диаметр ствола сваи', 'Толщина сваи по всей длине. Для частного дома обычно 0,2–0,4 м; тяжелее дом и слабее грунт – больше диаметр.', 'pile-shaft-diameter', 'Ø ствола'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($pileShaftDiameterFieldId); ?>" type="number" name="pileShaftDiameterM" min="0.01" step="0.01" value="0.3">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftDiameterM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-height">
                                    <label for="<?php echo esc_attr($pileShaftHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Высота ствола сваи (м)</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Высота ствола сваи', 'Длина сваи от верха до пяты. Подбирается так, чтобы опереться на плотный грунт ниже глубины промерзания; для частного дома чаще 2–4 м.', 'pile-shaft-length', 'Длина сваи'); ?>
                                    </label>
                                    <input id="<?php echo esc_attr($pileShaftHeightFieldId); ?>" type="number" name="pileShaftHeightM" min="0.01" step="0.01" value="2">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftHeightM" aria-live="polite"></div>
                                </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-base-toggle">
                                <input id="<?php echo esc_attr($includePileBaseFieldId); ?>" type="checkbox" name="includePileBase" value="1">
                                <label for="<?php echo esc_attr($includePileBaseFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать уширение сваи (пяту)</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileBase" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="pile-base-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Диаметр уширения (м)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Диаметр уширения', 'Размер расширенной «пяты». Увеличивает опору на грунт; обычно в 1,5–2 раза больше диаметра ствола.', 'pile-bulb-diameter', 'Ø уширения'); ?>
                                </label>
                                <input id="<?php echo esc_attr($pileBaseDiameterFieldId); ?>" type="number" name="pileBaseDiameterM" min="0.01" step="0.01" value="0.5">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseDiameterM" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота уширения (м)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Высота уширения', 'Высота расширенной части (пяты) у основания. Обычно 0,2–0,4 м.', 'pile-bulb-height', 'Высота уширения'); ?>
                                </label>
                                <input id="<?php echo esc_attr($pileBaseHeightFieldId); ?>" type="number" name="pileBaseHeightM" min="0.01" step="0.01" value="0.3">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseHeightM" aria-live="polite"></div>
                            </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-reinforcement-toggle">
                                <input id="<?php echo esc_attr($includePileReinforcementFieldId); ?>" type="checkbox" name="includePileReinforcement" value="1">
                                <label for="<?php echo esc_attr($includePileReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать арматуру свай</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileReinforcement" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="pile-reinforcement-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Кол-во стержней в свае</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Кол-во стержней в свае', 'Продольных прутков в одной свае. Для частного дома чаще всего 3–4.'); ?>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>" type="number" name="pileReinforcementBarsCount" min="1" step="1" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementBarsCount" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Диаметр арматуры свай (мм)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Диаметр арматуры свай', 'Толщина продольных прутков. Для буронабивной сваи частного дома обычно 10–14 мм.', 'rebar-diameter'); ?>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>" type="number" name="pileReinforcementDiameterMm" min="1" step="1" value="12">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementDiameterMm" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Запас арматуры свай (%)</span>
                                    <?php echo MarkupHelpers::renderFieldTooltip('Запас арматуры свай', 'Запас на нахлёсты стержней и отходы при резке. Для свай обычно 5–10%; ближе к 10%, если много стыков по длине.'); ?>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>" type="number" name="pileReinforcementReservePercent" min="1" step="1" value="10">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementReservePercent" aria-live="polite"></div>
                            </div>
                        </div>

                            </div>
                        </details>
                    <?php endif; ?>
                <?php if ($calculator === 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open>
                            <summary class="brigmaster-estimator__accordion-summary">Тип смеси<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                                <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-unified-mixture-toggle>
                                    <input id="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" type="checkbox" name="useUnifiedConcreteMixtureSettings" value="1" checked>
                                    <label for="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" class="brigmaster-estimator__label-row">
                                        <span>Использовать один тип смеси для свай и ростверка</span>
                                        <?php echo MarkupHelpers::renderFieldTooltip('Один тип смеси', 'Снимите галочку, если сваи и ростверк заливаются разными марками бетона. Опция доступна, только когда включены оба расчёта – и сваи, и ростверк. Если выбран один расчёт, используется единый тип смеси.'); ?>
                                    </label>
                                    <div class="brigmaster-estimator__error" data-field-error="useUnifiedConcreteMixtureSettings" aria-live="polite"></div>
                                </div>
                                <div data-pile-mixture-block="shared">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Общий тип смеси', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="pile">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'pile', 'Тип смеси для буронабивных свай', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="grillage">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'grillage', 'Тип смеси для ростверка', false, true); ?>
                                </div>
                            </div>
                        </details>
                <?php endif; ?>
        <?php
        return (string) ob_get_clean();
    }
}
