<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic brick estimator field markup.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes:
 * stateless, returns identical markup.
 */
final class BrickEstimatorFields
{
    public static function render(string $instanceId): string
    {
        $brickFormatFieldId = $instanceId . 'brick-format';
        $brickLengthFieldId = $instanceId . 'brick-length-mm';
        $brickWidthFieldId = $instanceId . 'brick-width-mm';
        $brickHeightFieldId = $instanceId . 'brick-height-mm';
        $brickWeightFieldId = $instanceId . 'brick-weight-kg';
        $brickPriceFieldId = $instanceId . 'brick-price';
        $wallLengthFieldId = $instanceId . 'wall-length-m';
        $wallHeightFieldId = $instanceId . 'wall-height-m';
        $brickAreaFieldId = $instanceId . 'brick-area';
        $jointThicknessFieldId = $instanceId . 'joint-thickness-mm';
        $wallThicknessFieldId = $instanceId . 'wall-thickness-type';
        $reserveFieldId = $instanceId . 'reserve-percent';
        $includeOpeningsFieldId = $instanceId . 'include-openings';
        $includeGablesFieldId = $instanceId . 'include-gables';
        $includeMeshFieldId = $instanceId . 'include-masonry-mesh';
        $meshFrequencyFieldId = $instanceId . 'masonry-mesh-frequency';
        $cementShareFieldId = $instanceId . 'brick-cement-share';
        $sandShareFieldId = $instanceId . 'brick-sand-share';
        $cementUnitTypeFieldId = $instanceId . 'brick-cement-unit-type';
        $cementUnitWeightFieldId = $instanceId . 'brick-cement-unit-weight';
        $cementUnitPriceFieldId = $instanceId . 'brick-cement-unit-price';
        $sandUnitTypeFieldId = $instanceId . 'brick-sand-unit-type';
        $sandUnitWeightFieldId = $instanceId . 'brick-sand-unit-weight';
        $sandUnitPriceFieldId = $instanceId . 'brick-sand-unit-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-field-group="brick-main">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickFormatFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Формат кирпича</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Формат кирпича" aria-expanded="false" aria-controls="<?php echo esc_attr($brickFormatFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($brickFormatFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Стандартные размеры подставляются автоматически. Свой формат можно ввести вручную.</div>
                    </span>
                </label>
                <select id="<?php echo esc_attr($brickFormatFieldId); ?>" name="brickFormat" data-brick-format-select>
                    <option value="single_nf" data-brick-length="250" data-brick-width="120" data-brick-height="65">1 НФ (250×120×65 мм)</option>
                    <option value="one_and_half_nf" data-brick-length="250" data-brick-width="120" data-brick-height="88">1.4 НФ (250×120×88 мм)</option>
                    <option value="double_nf" data-brick-length="250" data-brick-width="120" data-brick-height="140">2.1 НФ (250×120×140 мм)</option>
                    <option value="euro_nf" data-brick-length="250" data-brick-width="85" data-brick-height="65">Евро (250×85×65 мм)</option>
                    <option value="custom">Свой размер</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="brickFormat" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickLengthFieldId); ?>">Длина кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickLengthFieldId); ?>" type="number" name="brickLengthMm" min="1" step="1" value="250" data-brick-size-input="length">
                <div class="brigmaster-estimator__error" data-field-error="brickLengthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickWidthFieldId); ?>">Ширина кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickWidthFieldId); ?>" type="number" name="brickWidthMm" min="1" step="1" value="120" data-brick-size-input="width">
                <div class="brigmaster-estimator__error" data-field-error="brickWidthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickHeightFieldId); ?>">Высота кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickHeightFieldId); ?>" type="number" name="brickHeightMm" min="1" step="1" value="65" data-brick-size-input="height">
                <div class="brigmaster-estimator__error" data-field-error="brickHeightMm" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-field-group="brick-row2">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($wallThicknessFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Толщина стены</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Толщина стены" aria-expanded="false" aria-controls="<?php echo esc_attr($wallThicknessFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($wallThicknessFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Толщина автоматически учитывает выбранный формат кирпича и растворный шов.</div>
                    </span>
                </label>
                <select id="<?php echo esc_attr($wallThicknessFieldId); ?>" name="wallThicknessType">
                    <option value="half_brick">В 0.5 кирпича</option>
                    <option value="one_brick">В 1 кирпич</option>
                    <option value="one_and_half_bricks" selected>В 1.5 кирпича</option>
                    <option value="two_bricks">В 2 кирпича</option>
                    <option value="two_and_half_bricks">В 2.5 кирпича</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="wallThicknessType" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field-group" data-field-group="brick-geometry-dimensions">
                <div class="brigmaster-estimator__field">
                    <label for="<?php echo esc_attr($wallLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                        <span>Общая длина стен (м)</span>
                        <span class="brigmaster-estimator__tooltip-anchor">
                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Общая длина стен" aria-expanded="false" aria-controls="<?php echo esc_attr($wallLengthFieldId . '-tooltip'); ?>">i</button>
                            <div id="<?php echo esc_attr($wallLengthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Сумма длин всех стен. Например: 7 + 7 + 8 + 8 = 30 м.</div>
                        </span>
                    </label>
                    <input id="<?php echo esc_attr($wallLengthFieldId); ?>" type="number" name="wallLengthM" min="0.01" step="0.01" value="30">
                    <div class="brigmaster-estimator__error" data-field-error="wallLengthM" aria-live="polite"></div>
                </div>
            </div>
            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="brick-geometry-area">
                <div class="brigmaster-estimator__field">
                    <label for="<?php echo esc_attr($brickAreaFieldId); ?>" class="brigmaster-estimator__label-row">
                        <span>Площадь стен (м²)</span>
                        <span class="brigmaster-estimator__tooltip-anchor">
                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Площадь стен" aria-expanded="false" aria-controls="<?php echo esc_attr($brickAreaFieldId . '-tooltip'); ?>">i</button>
                            <div id="<?php echo esc_attr($brickAreaFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Указывайте полную площадь стен без вычета проёмов, если планируете учесть их ниже.</div>
                        </span>
                    </label>
                    <input id="<?php echo esc_attr($brickAreaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="90">
                    <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                </div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'wall-height-common'); ?>" class="brigmaster-estimator__label-row">
                    <span>Средняя высота стен (м)</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Средняя высота стен" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'wall-height-common-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($instanceId . 'wall-height-common-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Если высота стен разная, укажите среднее значение по всем стенам.</div>
                    </span>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'wall-height-common'); ?>" type="number" name="wallHeightM" min="0.01" step="0.01" value="3" data-brick-wall-height>
                <div class="brigmaster-estimator__error" data-field-error="wallHeightM" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($jointThicknessFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Толщина шва (мм)</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Толщина шва" aria-expanded="false" aria-controls="<?php echo esc_attr($jointThicknessFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($jointThicknessFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Чаще всего 8-12 мм. Шов влияет на количество кирпича и объём раствора.</div>
                    </span>
                </label>
                <input id="<?php echo esc_attr($jointThicknessFieldId); ?>" type="number" name="jointThicknessMm" min="1" step="1" value="10">
                <div class="brigmaster-estimator__error" data-field-error="jointThicknessMm" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="brick-economics">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($reserveFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Запас (%)</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Запас" aria-expanded="false" aria-controls="<?php echo esc_attr($reserveFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($reserveFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Обычно 5-10% на бой, подрезку и подгонку кладки.</div>
                    </span>
                </label>
                <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="5">
                <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickWeightFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Масса 1 кирпича (кг)</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Масса 1 кирпича" aria-expanded="false" aria-controls="<?php echo esc_attr($brickWeightFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($brickWeightFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Необязательно. Если поле пустое, калькулятор покажет ориентир по габаритам кирпича.</div>
                    </span>
                </label>
                <input id="<?php echo esc_attr($brickWeightFieldId); ?>" type="number" name="brickWeightKg" min="0.1" step="0.1" placeholder="Например, 3.5">
                <div class="brigmaster-estimator__error" data-field-error="brickWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickPriceFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Цена 1 кирпича</span>
                    <span class="brigmaster-estimator__tooltip-anchor">
                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: Цена 1 кирпича" aria-expanded="false" aria-controls="<?php echo esc_attr($brickPriceFieldId . '-tooltip'); ?>">i</button>
                        <div id="<?php echo esc_attr($brickPriceFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Если цена не указана, блок со стоимостью кирпича не выводится.</div>
                    </span>
                </label>
                <input id="<?php echo esc_attr($brickPriceFieldId); ?>" type="number" name="brickPricePerUnit" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="brickPricePerUnit" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-brick-mortar-ratio-fields>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($cementShareFieldId); ?>">Доля цемента</label>
                <input id="<?php echo esc_attr($cementShareFieldId); ?>" type="number" name="cementShare" min="0.1" step="0.1" value="1">
                <div class="brigmaster-estimator__error" data-field-error="cementShare" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($cementUnitTypeFieldId); ?>">Единица покупки цемента</label>
                <select id="<?php echo esc_attr($cementUnitTypeFieldId); ?>" name="cementPurchaseUnit">
                    <option value="bag">Мешок</option>
                    <option value="tonne">Тонна</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="cementPurchaseUnit" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($cementUnitWeightFieldId); ?>" data-mixture-unit-label="cement">Вес единицы цемента (кг)</label>
                <input id="<?php echo esc_attr($cementUnitWeightFieldId); ?>" type="number" name="cementUnitWeightKg" min="0.001" step="0.001" value="50" data-mixture-unit-input="cement">
                <div class="brigmaster-estimator__error" data-field-error="cementUnitWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($cementUnitPriceFieldId); ?>" data-mixture-price-label="cement">Цена мешка цемента</label>
                <input id="<?php echo esc_attr($cementUnitPriceFieldId); ?>" type="number" name="cementUnitPrice" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="cementUnitPrice" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sandShareFieldId); ?>">Доля песка</label>
                <input id="<?php echo esc_attr($sandShareFieldId); ?>" type="number" name="sandShare" min="0.1" step="0.1" value="4">
                <div class="brigmaster-estimator__error" data-field-error="sandShare" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sandUnitTypeFieldId); ?>">Единица покупки песка</label>
                <select id="<?php echo esc_attr($sandUnitTypeFieldId); ?>" name="sandPurchaseUnit">
                    <option value="tonne">Тонна</option>
                    <option value="bag">Мешок</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="sandPurchaseUnit" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sandUnitWeightFieldId); ?>" data-mixture-unit-label="sand">Вес единицы песка (т)</label>
                <input id="<?php echo esc_attr($sandUnitWeightFieldId); ?>" type="number" name="sandUnitWeightKg" min="0.001" step="0.001" value="1" data-mixture-unit-input="sand">
                <div class="brigmaster-estimator__error" data-field-error="sandUnitWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sandUnitPriceFieldId); ?>" data-mixture-price-label="sand">Цена тонны песка</label>
                <input id="<?php echo esc_attr($sandUnitPriceFieldId); ?>" type="number" name="sandUnitPrice" min="0.01" step="0.01" placeholder="Укажите цену">
                <div class="brigmaster-estimator__error" data-field-error="sandUnitPrice" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="brick-mesh">
            <input id="<?php echo esc_attr($includeMeshFieldId); ?>" type="checkbox" name="includeMasonryMesh" value="1">
            <label for="<?php echo esc_attr($includeMeshFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учитывать кладочную сетку</span>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="includeMasonryMesh" aria-live="polite"></div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="brick-mesh">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($meshFrequencyFieldId); ?>">Шаг сетки по рядам</label>
                <select id="<?php echo esc_attr($meshFrequencyFieldId); ?>" name="masonryMeshFrequencyRows">
                    <option value="1">Каждый ряд</option>
                    <option value="2">Каждый 2 ряд</option>
                    <option value="3" selected>Каждый 3 ряд</option>
                    <option value="4">Каждый 4 ряд</option>
                    <option value="5">Каждый 5 ряд</option>
                </select>
                <p class="brigmaster-estimator__hint">Для частного дома чаще применяют армирование через 3-5 рядов.</p>
                <div class="brigmaster-estimator__error" data-field-error="masonryMeshFrequencyRows" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="brick-openings">
            <input id="<?php echo esc_attr($includeOpeningsFieldId); ?>" type="checkbox" name="includeOpenings" value="1">
            <label for="<?php echo esc_attr($includeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть окна и двери</span>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="includeOpenings" aria-live="polite"></div>
        </div>

        <details class="brigmaster-estimator__accordion" open data-toggle-target="brick-openings">
            <summary class="brigmaster-estimator__accordion-summary">Окна и двери<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
            <div class="brigmaster-estimator__accordion-body">
                <?php echo self::renderBrickRepeatableGroup($instanceId, 'windows', 'Окна', 'window', false); ?>
                <?php echo self::renderBrickRepeatableGroup($instanceId, 'doors', 'Двери', 'door', false); ?>
            </div>
        </details>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="brick-gables">
            <input id="<?php echo esc_attr($includeGablesFieldId); ?>" type="checkbox" name="includeGables" value="1">
            <label for="<?php echo esc_attr($includeGablesFieldId); ?>" class="brigmaster-estimator__label-row">
                <span>Учесть фронтоны</span>
            </label>
            <div class="brigmaster-estimator__error" data-field-error="includeGables" aria-live="polite"></div>
        </div>

        <details class="brigmaster-estimator__accordion" open data-toggle-target="brick-gables">
            <summary class="brigmaster-estimator__accordion-summary">Фронтоны<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
            <div class="brigmaster-estimator__accordion-body">
                <p class="brigmaster-estimator__hint brigmaster-estimator__hint--accordion">
                    Фронтон считается как треугольник по формуле `0.5 x ширина x высота`. Угол отдельно не задаётся: он автоматически определяется этими двумя размерами.
                </p>
                <?php echo self::renderBrickRepeatableGroup($instanceId, 'gables', 'Фронтоны', 'gable', true); ?>
            </div>
        </details>
        <?php

        return (string) ob_get_clean();
    }

    private static function renderBrickRepeatableGroup(string $instanceId, string $group, string $title, string $type, bool $renderInitialItem = true): string
    {
        $addButtonId = $instanceId . $group . '-add';
        $listId = $instanceId . $group . '-list';
        $defaultWidth = $type === 'door' ? '0.9' : '1.2';
        $defaultHeight = $type === 'door' ? '2.1' : '1.4';
        $defaultCount = '1';
        $itemTitle = match ($group) {
            'windows' => 'Окно',
            'doors' => 'Дверь',
            'gables' => 'Фронтон',
            default => 'Элемент',
        };
        $addButtonLabel = match ($group) {
            'windows' => 'Добавить окно',
            'doors' => 'Добавить дверь',
            'gables' => 'Добавить фронтон',
            default => 'Добавить',
        };

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-brick-add-item="<?php echo esc_attr($group); ?>">
                    <?php echo esc_html($addButtonLabel); ?>
                </button>
            </div>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-brick-repeat-list="<?php echo esc_attr($group); ?>" data-brick-item-type="<?php echo esc_attr($type); ?>">
                <?php if ($renderInitialItem) : ?>
                <article class="brigmaster-estimator__segment-card" data-brick-repeat-item data-brick-item-type="<?php echo esc_attr($type); ?>" data-brick-group="<?php echo esc_attr($group); ?>">
                    <div class="brigmaster-estimator__segment-head">
                        <h4 class="brigmaster-estimator__segment-title"><?php echo esc_html($itemTitle); ?> 1</h4>
                        <button type="button" class="brigmaster-estimator__segment-remove" data-brick-remove-item>Удалить</button>
                    </div>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-width'); ?>">Ширина (м)</label>
                            <input id="<?php echo esc_attr($listId . '-0-width'); ?>" type="number" min="0.01" step="0.01" value="<?php echo esc_attr($defaultWidth); ?>" data-brick-repeat-input="widthM" name="<?php echo esc_attr($group . '[0][widthM]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-height'); ?>">Высота (м)</label>
                            <input id="<?php echo esc_attr($listId . '-0-height'); ?>" type="number" min="0.01" step="0.01" value="<?php echo esc_attr($defaultHeight); ?>" data-brick-repeat-input="heightM" name="<?php echo esc_attr($group . '[0][heightM]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="heightM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-count'); ?>">Количество</label>
                            <input id="<?php echo esc_attr($listId . '-0-count'); ?>" type="number" min="1" step="1" value="<?php echo esc_attr($defaultCount); ?>" data-brick-repeat-input="count" name="<?php echo esc_attr($group . '[0][count]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="count" aria-live="polite"></div>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
            </div>
            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($group); ?>" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
