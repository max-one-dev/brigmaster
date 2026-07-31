<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic tile estimator field markup.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, returns identical markup.
 */
final class TileEstimatorFields
{
    public static function render(string $instanceId): string
    {
        $tileTargetFieldId = $instanceId . 'tile-target';
        $tilePatternFieldId = $instanceId . 'tile-pattern';
        $tileOffsetFieldId = $instanceId . 'tile-offset-percent';
        $tileLengthFieldId = $instanceId . 'tile-length-mm';
        $tileWidthFieldId = $instanceId . 'tile-width-mm';
        $tileThicknessFieldId = $instanceId . 'tile-thickness-mm';
        $tileJointFieldId = $instanceId . 'tile-joint-mm';
        $reserveFieldId = $instanceId . 'tile-reserve-percent';
        $tilePriceFieldId = $instanceId . 'tile-price-m2';
        $tileIncludeOpeningsFieldId = $instanceId . 'tile-include-openings';
        $tileIncludeCutoutsFieldId = $instanceId . 'tile-include-cutouts';
        $tileIncludeAdhesiveFieldId = $instanceId . 'tile-include-adhesive';
        $tileIncludeGroutFieldId = $instanceId . 'tile-include-grout';
        $tileAdhesiveConsumptionFieldId = $instanceId . 'tile-adhesive-consumption';
        $tileAdhesiveLayerFieldId = $instanceId . 'tile-adhesive-layer';
        $tileAdhesiveBagWeightFieldId = $instanceId . 'tile-adhesive-bag-weight';
        $tileAdhesiveBagPriceFieldId = $instanceId . 'tile-adhesive-bag-price';
        $tileGroutDensityFieldId = $instanceId . 'tile-grout-density';
        $tileGroutPackWeightFieldId = $instanceId . 'tile-grout-pack-weight';
        $tileGroutPackPriceFieldId = $instanceId . 'tile-grout-pack-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileTargetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Что облицовываем</span>
                </label>
                <select id="<?php echo esc_attr($tileTargetFieldId); ?>" name="tileTarget" data-tile-target-select>
                    <option value="floor">Пол</option>
                    <option value="wall">Стены</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="tileTarget" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tilePatternFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Способ укладки</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Способ укладки', 'Прямая – меньший запас. Смещение и диагональ повышают подрезку – калькулятор предлагает больший запас.'); ?>
                </label>
                <select id="<?php echo esc_attr($tilePatternFieldId); ?>" name="tileLayingPattern" data-tile-pattern-select>
                    <option value="direct">Прямая</option>
                    <option value="offset">Со смещением</option>
                    <option value="diagonal">Диагональная</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="tileLayingPattern" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="tile-dimensions" data-tile-autofit>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-room-length'); ?>" class="brigmaster-estimator__label-row">
                    <span data-tile-length-label>Длина помещения (м)</span>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-room-length'); ?>" type="number" name="length" min="0.01" step="0.01" value="6">
                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-room-width'); ?>" class="brigmaster-estimator__label-row">
                    <span data-tile-width-label>Ширина помещения (м)</span>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-room-width'); ?>" type="number" name="width" min="0.01" step="0.01" value="4">
                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="tile-wall-height">
                <label for="<?php echo esc_attr($instanceId . 'tile-wall-height'); ?>" class="brigmaster-estimator__label-row">
                    <span>Высота стен (м)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Высота стен', 'Если высота стен разная по периметру, укажите среднее значение.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-wall-height'); ?>" type="number" name="height" min="0.01" step="0.01" value="2.7">
                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="tile-area">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-area'); ?>" class="brigmaster-estimator__label-row">
                    <span>Площадь облицовки (м²)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Площадь облицовки', 'Режим по площади: считается расход материалов, но ориентировочная раскладка плитки не строится.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-area'); ?>" type="number" name="area" min="0.01" step="0.01" value="24">
                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Длина плитки (мм)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Длина плитки', 'Длина одной плитки без шва, в мм. Шов учитывается отдельно.', 'tile-size'); ?>
                </label>
                <input id="<?php echo esc_attr($tileLengthFieldId); ?>" type="number" name="tileLengthMm" min="1" step="1" value="600">
                <div class="brigmaster-estimator__error" data-field-error="tileLengthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileWidthFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Ширина плитки (мм)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Ширина плитки', 'Ширина одной плитки без шва, в мм. Для квадратной – совпадает с длиной.', 'tile-width'); ?>
                </label>
                <input id="<?php echo esc_attr($tileWidthFieldId); ?>" type="number" name="tileWidthMm" min="1" step="1" value="600">
                <div class="brigmaster-estimator__error" data-field-error="tileWidthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileThicknessFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Толщина плитки (мм)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Толщина плитки', 'Нужна для расчёта затирки. По умолчанию: стены 8 мм, пол 9 мм.', 'tile-thickness'); ?>
                </label>
                <input id="<?php echo esc_attr($tileThicknessFieldId); ?>" type="number" name="tileThicknessMm" min="1" step="1" value="9" data-tile-thickness-input>
                <div class="brigmaster-estimator__error" data-field-error="tileThicknessMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileJointFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Ширина шва (мм)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Ширина шва', 'Ширина шва между плитками. Обычно 1,5–3 мм для стен и 2–5 мм для пола. Влияет на количество плиток и расход затирки.', 'grout-joint'); ?>
                </label>
                <input id="<?php echo esc_attr($tileJointFieldId); ?>" type="number" name="tileJointMm" min="1" step="0.1" value="2">
                <div class="brigmaster-estimator__error" data-field-error="tileJointMm" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-tile-autofit>
            <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="tile-offset">
                <label for="<?php echo esc_attr($tileOffsetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Смещение (% длины плитки)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Смещение', 'Только для укладки со смещением. 50% – классический сдвиг на половину плитки.', 'tile-offset'); ?>
                </label>
                <input id="<?php echo esc_attr($tileOffsetFieldId); ?>" type="number" name="tileOffsetPercent" min="1" step="1" value="50">
                <div class="brigmaster-estimator__error" data-field-error="tileOffsetPercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($reserveFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Запас (%)</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Запас', 'Зависит от способа укладки: прямая – 5%, смещение – 7%, диагональ – 10% и выше. Можно изменить под задачу.'); ?>
                </label>
                <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="5" data-tile-reserve-input>
                <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tilePriceFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Цена плитки за м²</span>
                    <?php echo MarkupHelpers::renderFieldTooltip('Цена плитки за м²', 'Необязательно. Без цены карточка стоимости плитки не выводится.'); ?>
                </label>
                <input id="<?php echo esc_attr($tilePriceFieldId); ?>" type="number" name="tilePricePerM2" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="tilePricePerM2" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="tile-openings">
            <input id="<?php echo esc_attr($tileIncludeOpeningsFieldId); ?>" type="checkbox" name="tileIncludeOpenings" value="1">
            <label for="<?php echo esc_attr($tileIncludeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть окна и двери</span>
                <?php echo MarkupHelpers::renderFieldTooltip('Учесть окна и двери', 'Вычитает площадь окон и дверей из облицовываемой площади. Подрезку у проёмов калькулятор не считает – запас всё равно нужен.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="tileIncludeOpenings" aria-live="polite"></div>
            <p class="brigmaster-estimator__hint">Доступно, когда выбрана облицовка стен.</p>
        </div>

        <details class="brigmaster-estimator__accordion" open data-toggle-target="tile-openings">
            <summary class="brigmaster-estimator__accordion-summary">Окна и двери<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
            <div class="brigmaster-estimator__accordion-body">
                <?php echo self::renderTileRepeatableGroup($instanceId, 'tileOpenings', 'Окна и двери', 'opening', false); ?>
            </div>
        </details>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="tile-cutouts">
            <input id="<?php echo esc_attr($tileIncludeCutoutsFieldId); ?>" type="checkbox" name="tileIncludeCutouts" value="1">
            <label for="<?php echo esc_attr($tileIncludeCutoutsFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть отверстия</span>
                <?php echo MarkupHelpers::renderFieldTooltip('Учесть отверстия', 'Вырез уменьшает площадь, но часто съедает целую плитку – добавляется ориентир по потерям на каждый вырез.'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="tileIncludeCutouts" aria-live="polite"></div>
        </div>

        <details class="brigmaster-estimator__accordion" open data-toggle-target="tile-cutouts">
            <summary class="brigmaster-estimator__accordion-summary">Вырезы и отверстия<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
            <div class="brigmaster-estimator__accordion-body">
                <?php echo self::renderTileRepeatableGroup($instanceId, 'tileCutouts', 'Вырезы и отверстия', 'cutout', true); ?>
            </div>
        </details>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="tile-adhesive">
            <input id="<?php echo esc_attr($tileIncludeAdhesiveFieldId); ?>" type="checkbox" name="tileIncludeAdhesive" value="1">
            <label for="<?php echo esc_attr($tileIncludeAdhesiveFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Рассчитать клей</span>
                <?php echo MarkupHelpers::renderFieldTooltip('Рассчитать клей', 'Расход клея справочный: зависит от размера плитки, основания, зуба шпателя, толщины слоя и производителя смеси (см. упаковку).'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="tileIncludeAdhesive" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-tile-adhesive-fields>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileAdhesiveConsumptionFieldId); ?>">Расход клея (кг/м²)</label>
                <input id="<?php echo esc_attr($tileAdhesiveConsumptionFieldId); ?>" type="number" name="tileAdhesiveConsumptionKgPerM2" min="0.01" step="0.01" value="3.5">
                <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveConsumptionKgPerM2" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileAdhesiveLayerFieldId); ?>">Толщина слоя клея (мм)</label>
                <input id="<?php echo esc_attr($tileAdhesiveLayerFieldId); ?>" type="number" name="tileAdhesiveLayerMm" min="0.1" step="0.1" value="3">
                <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveLayerMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileAdhesiveBagWeightFieldId); ?>">Вес мешка клея (кг)</label>
                <input id="<?php echo esc_attr($tileAdhesiveBagWeightFieldId); ?>" type="number" name="tileAdhesiveBagWeightKg" min="0.1" step="0.1" value="25">
                <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveBagWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileAdhesiveBagPriceFieldId); ?>">Цена мешка клея</label>
                <input id="<?php echo esc_attr($tileAdhesiveBagPriceFieldId); ?>" type="number" name="tileAdhesiveBagPrice" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveBagPrice" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="tile-grout">
            <input id="<?php echo esc_attr($tileIncludeGroutFieldId); ?>" type="checkbox" name="tileIncludeGrout" value="1">
            <label for="<?php echo esc_attr($tileIncludeGroutFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Рассчитать затирку</span>
                <?php echo MarkupHelpers::renderFieldTooltip('Рассчитать затирку', 'Затирка ориентировочно: размеры плитки, толщина, ширина шва и плотность смеси; фактический расход зависит от производителя (см. упаковку).'); ?>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="tileIncludeGrout" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three brigmaster-estimator__field-group--hidden" data-tile-grout-fields>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileGroutDensityFieldId); ?>">Плотность затирки (кг/м³)</label>
                <input id="<?php echo esc_attr($tileGroutDensityFieldId); ?>" type="number" name="tileGroutDensityKgPerM3" min="0.1" step="0.1" value="1600">
                <div class="brigmaster-estimator__error" data-field-error="tileGroutDensityKgPerM3" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileGroutPackWeightFieldId); ?>">Вес упаковки затирки (кг)</label>
                <input id="<?php echo esc_attr($tileGroutPackWeightFieldId); ?>" type="number" name="tileGroutPackWeightKg" min="0.1" step="0.1" value="2">
                <div class="brigmaster-estimator__error" data-field-error="tileGroutPackWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileGroutPackPriceFieldId); ?>">Цена упаковки затирки</label>
                <input id="<?php echo esc_attr($tileGroutPackPriceFieldId); ?>" type="number" name="tileGroutPackPrice" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="tileGroutPackPrice" aria-live="polite"></div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private static function renderTileRepeatableGroup(string $instanceId, string $group, string $title, string $type, bool $renderInitialItem = true): string
    {
        $addButtonId = $instanceId . '-' . $group . '-add';
        $listId = $instanceId . '-' . $group . '-list';
        $addButtonLabel = $type === 'cutout' ? 'Добавить вырез или отверстие' : 'Добавить окно или дверь';
        $description = $type === 'cutout'
            ? 'Используйте этот блок для труб, розеток, люков, трапов и других мест, где приходится вырезать плитку.'
            : 'Используйте этот блок только для окон, дверей и других полноразмерных проёмов на стенах.';

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-tile-add-item="<?php echo esc_attr($group); ?>">
                    <?php echo esc_html($addButtonLabel); ?>
                </button>
            </div>
            <p class="brigmaster-estimator__hint"><?php echo esc_html($description); ?></p>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-tile-repeat-list="<?php echo esc_attr($group); ?>" data-tile-item-type="<?php echo esc_attr($type); ?>">
                <?php if ($renderInitialItem) : ?>
                <article class="brigmaster-estimator__segment-card" data-tile-repeat-item data-tile-item-type="<?php echo esc_attr($type); ?>" data-tile-group="<?php echo esc_attr($group); ?>">
                    <?php if ($type === 'cutout') : ?>
                    <div class="brigmaster-estimator__segment-head">
                        <h4 class="brigmaster-estimator__segment-title">Вырез или отверстие 1</h4>
                        <button type="button" class="brigmaster-estimator__segment-remove" data-tile-remove-item>Удалить</button>
                    </div>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-tile-autofit>
                        <div class="brigmaster-estimator__field">
                            <label>Что это за элемент</label>
                            <select data-tile-repeat-input="shape">
                                <option value="circle">Круглое отверстие</option>
                                <option value="rect">Прямоугольный вырез</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="tileCutouts.0.shape" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field" data-tile-shape-circle>
                            <label>Диаметр отверстия (мм)</label>
                            <input type="number" min="1" step="1" value="80" data-tile-repeat-input="diameterMm">
                            <div class="brigmaster-estimator__error" data-field-error="tileCutouts.0.diameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-tile-shape-rect>
                            <label>Ширина выреза (мм)</label>
                            <input type="number" min="1" step="1" value="150" data-tile-repeat-input="widthMm">
                            <div class="brigmaster-estimator__error" data-field-error="tileCutouts.0.widthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-tile-shape-rect>
                            <label>Высота выреза (мм)</label>
                            <input type="number" min="1" step="1" value="150" data-tile-repeat-input="heightMm">
                            <div class="brigmaster-estimator__error" data-field-error="tileCutouts.0.heightMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label>Количество</label>
                            <input type="number" min="1" step="1" value="1" data-tile-repeat-input="count">
                            <div class="brigmaster-estimator__error" data-field-error="tileCutouts.0.count" aria-live="polite"></div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="brigmaster-estimator__segment-head">
                        <h4 class="brigmaster-estimator__segment-title">Окно или дверь 1</h4>
                        <button type="button" class="brigmaster-estimator__segment-remove" data-tile-remove-item>Удалить</button>
                    </div>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label>Что вычитаем</label>
                            <select data-tile-repeat-input="type">
                                <option value="window">Окно</option>
                                <option value="door">Дверь</option>
                            </select>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label>Ширина (м)</label>
                            <input type="number" min="0.01" step="0.01" value="1.2" data-tile-repeat-input="widthM">
                            <div class="brigmaster-estimator__error" data-field-error="tileOpenings.0.widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label>Высота (м)</label>
                            <input type="number" min="0.01" step="0.01" value="1.4" data-tile-repeat-input="heightM">
                            <div class="brigmaster-estimator__error" data-field-error="tileOpenings.0.heightM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label>Количество</label>
                            <input type="number" min="1" step="1" value="1" data-tile-repeat-input="count">
                            <div class="brigmaster-estimator__error" data-field-error="tileOpenings.0.count" aria-live="polite"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endif; ?>
            </div>
            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($group); ?>" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
