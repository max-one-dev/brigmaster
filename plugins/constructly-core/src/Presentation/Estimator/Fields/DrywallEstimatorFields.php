<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic drywall estimator field markup.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, returns identical markup.
 */
final class DrywallEstimatorFields
{
    public static function render(string $instanceId): string
    {
        $targetFieldId = $instanceId . 'drywall-target';
        $sheetFormatFieldId = $instanceId . 'drywall-sheet-format';
        $sheetLengthFieldId = $instanceId . 'drywall-sheet-length';
        $sheetWidthFieldId = $instanceId . 'drywall-sheet-width';
        $sheetThicknessFieldId = $instanceId . 'drywall-sheet-thickness';
        $layersFieldId = $instanceId . 'drywall-layers';
        $stepFieldId = $instanceId . 'drywall-step';
        $profileWidthFieldId = $instanceId . 'drywall-profile-width';
        $reserveFieldId = $instanceId . 'drywall-reserve';
        $fastenerReserveFieldId = $instanceId . 'drywall-fastener-reserve';
        $includeOpeningsFieldId = $instanceId . 'drywall-include-openings';
        $includeEndCladdingFieldId = $instanceId . 'drywall-include-end-cladding';
        $includeFinishingFieldId = $instanceId . 'drywall-include-finishing';
        $includeCostsFieldId = $instanceId . 'drywall-include-costs';
        $sheetPriceFieldId = $instanceId . 'drywall-sheet-price';
        $profilePriceFieldId = $instanceId . 'drywall-profile-price';
        $fastenerPriceFieldId = $instanceId . 'drywall-fastener-price';
        $primerPriceFieldId = $instanceId . 'drywall-primer-price';
        $jointPuttyPriceFieldId = $instanceId . 'drywall-joint-putty-price';
        $finishPuttyPriceFieldId = $instanceId . 'drywall-finish-putty-price';
        $tapePriceFieldId = $instanceId . 'drywall-tape-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($targetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Тип конструкции</span>
                    <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-target-tooltip', 'Стена — облицовка по одной плоскости. Потолок — подвесной каркас. Перегородка — двусторонняя конструкция из профиля и листов.'); ?>
                </label>
                <select id="<?php echo esc_attr($targetFieldId); ?>" name="drywallTarget" data-drywall-target-select>
                    <option value="wall">Стена</option>
                    <option value="ceiling">Потолок</option>
                    <option value="partition">Перегородка</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="drywallTarget" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sheetFormatFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Формат листа</span>
                    <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-sheet-format-tooltip', 'Можно выбрать стандартный размер листа или задать свой. Это влияет на количество листов и количество поперечных перемычек.'); ?>
                </label>
                <select id="<?php echo esc_attr($sheetFormatFieldId); ?>" data-drywall-sheet-format-select>
                    <option value="2500x1200" data-sheet-length="2500" data-sheet-width="1200">2500×1200 мм</option>
                    <option value="3000x1200" data-sheet-length="3000" data-sheet-width="1200">3000×1200 мм</option>
                    <option value="2000x1200" data-sheet-length="2000" data-sheet-width="1200">2000×1200 мм</option>
                    <option value="custom">Свой размер</option>
                </select>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="drywall-wall-dimensions">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'drywall-length'); ?>" class="brigmaster-estimator__label-row">
                    <span data-drywall-length-label>Длина стены (м)</span>
                    <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-length-tooltip', 'Если нужно посчитать комнату целиком, сложите длины всех стен и введите общую сумму.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'drywall-length'); ?>" type="number" name="drywallLength" min="0.01" step="0.01" value="6">
                <p class="brigmaster-estimator__hint" data-drywall-length-hint>Для комнаты можно указать суммарную длину всех стен.</p>
                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'drywall-height'); ?>">Высота (м)</label>
                <input id="<?php echo esc_attr($instanceId . 'drywall-height'); ?>" type="number" name="drywallHeight" min="0.01" step="0.01" value="2.7">
                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two brigmaster-estimator__field-group--hidden" data-field-group="drywall-ceiling-dimensions">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'drywall-ceiling-length'); ?>">Длина помещения (м)</label>
                <input id="<?php echo esc_attr($instanceId . 'drywall-ceiling-length'); ?>" type="number" name="drywallCeilingLength" min="0.01" step="0.01" value="6">
                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'drywall-ceiling-width'); ?>">Ширина помещения (м)</label>
                <input id="<?php echo esc_attr($instanceId . 'drywall-ceiling-width'); ?>" type="number" name="drywallCeilingWidth" min="0.01" step="0.01" value="4">
                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="drywall-area">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'drywall-area'); ?>" class="brigmaster-estimator__label-row">
                    <span>Площадь конструкции (м²)</span>
                    <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-area-tooltip', 'В режиме по площади калькулятор точно считает листы и отделку, но не считает профили и крепёж по каркасу.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'drywall-area'); ?>" type="number" name="drywallArea" min="0.01" step="0.01" value="20">
                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__accordions" data-estimator-accordions>
            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Листы и каркас<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetLengthFieldId); ?>">Длина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetLengthFieldId); ?>" type="number" name="drywallSheetLengthMm" min="1" step="1" value="2500" data-drywall-sheet-length>
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetLengthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetWidthFieldId); ?>">Ширина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetWidthFieldId); ?>" type="number" name="drywallSheetWidthMm" min="1" step="1" value="1200" data-drywall-sheet-width>
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetWidthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetThicknessFieldId); ?>">Толщина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetThicknessFieldId); ?>" type="number" name="drywallSheetThicknessMm" min="0.1" step="0.1" value="12.5">
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetThicknessMm" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($layersFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Слоёв обшивки</span>
                                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-layers-tooltip', 'Для перегородки значение применяется к каждой стороне. Один слой подходит для простых задач, два — когда нужна более жёсткая конструкция.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($layersFieldId); ?>" name="drywallLayers">
                                <option value="1">1 слой</option>
                                <option value="2">2 слоя</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallLayers" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($stepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг профиля (мм)</span>
                                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-step-tooltip', 'Чем меньше шаг, тем жёстче каркас и выше расход профиля. Для большинства бытовых конструкций берут 400 или 600 мм.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($stepFieldId); ?>" name="drywallFrameStepMm">
                                <option value="600">600</option>
                                <option value="400">400</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallFrameStepMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="drywall-profile-width">
                            <label for="<?php echo esc_attr($profileWidthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина профиля перегородки (мм)</span>
                                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-profile-width-tooltip', 'Профиль определяет базовую толщину каркаса. В результате калькулятор также покажет ориентировочную итоговую толщину перегородки с учётом слоёв ГКЛ.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($profileWidthFieldId); ?>" name="drywallProfileWidthMm">
                                <option value="50">50</option>
                                <option value="75">75</option>
                                <option value="100">100</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallProfileWidthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($reserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас на листы и профиль (%)</span>
                                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-reserve-tooltip', 'Запас компенсирует подрезку, подгонку листов и добор профиля на сложных участках.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($fastenerReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас на метизы (%)</span>
                                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-fastener-reserve-tooltip', 'Запас применяется ко всем штучным позициям: саморезам, дюбелям, подвесам и соединителям.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($fastenerReserveFieldId); ?>" type="number" name="drywallFastenerReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="drywallFastenerReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </details>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle">
            <input id="<?php echo esc_attr($includeFinishingFieldId); ?>" type="checkbox" name="drywallIncludeFinishing" value="1">
            <label for="<?php echo esc_attr($includeFinishingFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть отделку</span>
                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-finishing-tooltip', 'Добавляет ориентир по грунтовке, шпатлёвке для швов, финишной шпатлёвке и армирующей ленте. Эти материалы появляются отдельными строками в результате расчёта.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="drywallIncludeFinishing" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="drywall-openings-toggle">
            <input id="<?php echo esc_attr($includeOpeningsFieldId); ?>" type="checkbox" name="includeOpenings" value="1">
            <label for="<?php echo esc_attr($includeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть проёмы</span>
                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-openings-tooltip', 'Проёмы уменьшают чистую площадь обшивки. Для потолка блок скрывается, потому что геометрия там другая.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="includeOpenings" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle brigmaster-estimator__field-group--hidden" data-field-group="drywall-end-cladding-toggle">
            <input id="<?php echo esc_attr($includeEndCladdingFieldId); ?>" type="checkbox" name="drywallIncludeEndCladding" value="1">
            <label for="<?php echo esc_attr($includeEndCladdingFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть облицовку торцов проёмов</span>
                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-end-cladding-tooltip', 'Актуально для перегородок. В расчёт добавляются полосы ГКЛ по толщине перегородки на боковые и верхние откосы.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="drywallIncludeEndCladding" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-drywall-openings-root>
            <?php echo self::renderDrywallRepeatableGroup($instanceId, 'drywallOpenings', 'Окна и двери'); ?>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle">
            <input id="<?php echo esc_attr($includeCostsFieldId); ?>" type="checkbox" name="drywallIncludeCosts" value="1">
            <label for="<?php echo esc_attr($includeCostsFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Посчитать стоимость</span>
                <?php echo MarkupHelpers::renderEstimatorTooltip($instanceId . 'drywall-costs-tooltip', 'Все ценовые поля необязательны. Если заполнить только часть из них, в результате появятся только эти строки.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="drywallIncludeCosts" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-drywall-costs-root>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sheetPriceFieldId); ?>">Цена листа ГКЛ</label>
                <input id="<?php echo esc_attr($sheetPriceFieldId); ?>" type="number" name="drywallSheetPrice" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallSheetPrice" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($profilePriceFieldId); ?>">Цена профиля за м</label>
                <input id="<?php echo esc_attr($profilePriceFieldId); ?>" type="number" name="drywallProfilePricePerLm" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallProfilePricePerLm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($fastenerPriceFieldId); ?>">Цена метизов за 100 шт</label>
                <input id="<?php echo esc_attr($fastenerPriceFieldId); ?>" type="number" name="drywallFastenerPricePer100" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallFastenerPricePer100" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-drywall-finishing-costs-root>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($primerPriceFieldId); ?>">Цена грунтовки за кг</label>
                <input id="<?php echo esc_attr($primerPriceFieldId); ?>" type="number" name="drywallPrimerPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallPrimerPricePerKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($jointPuttyPriceFieldId); ?>">Цена шпатлёвки для швов за кг</label>
                <input id="<?php echo esc_attr($jointPuttyPriceFieldId); ?>" type="number" name="drywallJointPuttyPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallJointPuttyPricePerKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($finishPuttyPriceFieldId); ?>">Цена финишной шпатлёвки за кг</label>
                <input id="<?php echo esc_attr($finishPuttyPriceFieldId); ?>" type="number" name="drywallFinishPuttyPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallFinishPuttyPricePerKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tapePriceFieldId); ?>">Цена ленты за м</label>
                <input id="<?php echo esc_attr($tapePriceFieldId); ?>" type="number" name="drywallTapePricePerLm" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="drywallTapePricePerLm" aria-live="polite"></div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private static function renderDrywallRepeatableGroup(string $instanceId, string $group, string $title): string
    {
        $addButtonId = $instanceId . '-' . $group . '-add';
        $listId = $instanceId . '-' . $group . '-list';

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-drywall-add-item="<?php echo esc_attr($group); ?>">
                    Добавить проём
                </button>
            </div>
            <p class="brigmaster-estimator__hint">Используйте этот блок для окон и дверей. Для перегородки можно добавить и оконные, и дверные проёмы.</p>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-drywall-repeat-list="<?php echo esc_attr($group); ?>"></div>
            <div class="brigmaster-estimator__error" data-field-error="windows" aria-live="polite"></div>
            <div class="brigmaster-estimator__error" data-field-error="doors" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
