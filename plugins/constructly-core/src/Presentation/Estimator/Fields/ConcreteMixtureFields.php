<?php

declare(strict_types=1);

namespace Brigmaster\Presentation\Estimator\Fields;

use Brigmaster\Presentation\Html\MarkupHelpers;

/**
 * Pure, argument-deterministic mixture field markup.
 *
 * Extracted verbatim from EstimateShortcode::renderConcreteMixtureFields()
 * without behavior changes: stateless, returns identical markup.
 */
final class ConcreteMixtureFields
{
    public static function render(
        string $instanceId,
        string $scope,
        string $title,
        bool $allowDryReady,
        bool $includeGravel
    ): string {
        $namePrefix = match ($scope) {
            'pile' => 'pile',
            'grillage' => 'grillage',
            default => '',
        };
        $errorPrefix = match ($scope) {
            'pile' => 'pileMixture',
            'grillage' => 'grillageMixture',
            default => 'mixture',
        };

        $buildName = static function (string $suffix) use ($namePrefix): string {
            if ($namePrefix === '') {
                return $suffix;
            }

            return $namePrefix . ucfirst($suffix);
        };

        $scopeSlug = $scope === '' ? 'base' : $scope;
        $typeFieldId = $instanceId . '-' . $scopeSlug . '-mixture-type';
        $priceFieldId = $instanceId . '-' . $scopeSlug . '-ready-price';
        $dryWeightFieldId = $instanceId . '-' . $scopeSlug . '-dry-bag-weight';
        $dryPriceFieldId = $instanceId . '-' . $scopeSlug . '-dry-bag-price';
        $cementShareFieldId = $instanceId . '-' . $scopeSlug . '-cement-share';
        $cementUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-type';
        $cementUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-weight';
        $cementUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-price';
        $sandShareFieldId = $instanceId . '-' . $scopeSlug . '-sand-share';
        $sandUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-type';
        $sandUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-weight';
        $sandUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-price';
        $gravelShareFieldId = $instanceId . '-' . $scopeSlug . '-gravel-share';
        $gravelUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-type';
        $gravelUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-weight';
        $gravelUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__mixture-block" data-mixture-scope="<?php echo esc_attr($scopeSlug); ?>" data-mixture-has-gravel="<?php echo $includeGravel ? '1' : '0'; ?>">
            <div class="brigmaster-estimator__field-group brigmaster-estimator__field">
                <label for="<?php echo esc_attr($typeFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span><?php echo esc_html($title); ?></span>
                    <?php
                    if ($allowDryReady) {
                        echo MarkupHelpers::renderFieldTooltip(
                            'Тип смеси',
                            '«Готовая» – товарный раствор, цена за м³. «Готовая сухая» – смесь в мешках, укажите вес мешка. «Самомесная» – компоненты по долям (Ц:П).'
                        );
                    } else {
                        echo MarkupHelpers::renderFieldTooltip(
                            'Тип смеси',
                            '«Готовая» – заказываете миксер, цена за м³. «Самомесная» – компоненты по объёмным долям (Ц:П:Щ).'
                        );
                    }
                    ?>
                </label>
                <select id="<?php echo esc_attr($typeFieldId); ?>" name="<?php echo esc_attr($buildName('mixtureType')); ?>" data-mixture-type-select>
                    <option value="ready">Готовая</option>
                    <?php if ($allowDryReady) : ?>
                        <option value="dry_ready">Готовая, сухая</option>
                    <?php endif; ?>
                    <option value="self_mix">Самомесная</option>
                </select>
                <p class="brigmaster-estimator__hint">Для самомесной смеси доли компонентов принимаются по объёму.</p>
                <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.type'); ?>" aria-live="polite"></div>
            </div>

            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-mixture-panel="ready">
                <div class="brigmaster-estimator__field">
                    <label for="<?php echo esc_attr($priceFieldId); ?>">Цена раствора за м³</label>
                    <input id="<?php echo esc_attr($priceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('readyConcretePricePerM3')); ?>" min="1" step="1" value="" placeholder="Укажите цену">
                    <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.readyConcretePricePerM3'); ?>" aria-live="polite"></div>
                </div>
            </div>

            <?php if ($allowDryReady) : ?>
                <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two brigmaster-estimator__field-group--hidden" data-mixture-panel="dry_ready">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($dryWeightFieldId); ?>">Вес мешка (кг)</label>
                        <input id="<?php echo esc_attr($dryWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('dryMixBagWeightKg')); ?>" min="1" step="1" value="25">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.dryMixBagWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($dryPriceFieldId); ?>">Цена мешка</label>
                        <input id="<?php echo esc_attr($dryPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('dryMixBagPrice')); ?>" min="1" step="1" value="" placeholder="Укажите цену">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.dryMixBagPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-mixture-panel="self_mix">
                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementShareFieldId); ?>">Доля цемента</label>
                        <input id="<?php echo esc_attr($cementShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementShare')); ?>" min="0.1" step="0.1" value="1">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementShare'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitTypeFieldId); ?>">Единица покупки цемента</label>
                        <select id="<?php echo esc_attr($cementUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('cementPurchaseUnit')); ?>">
                            <option value="bag">Мешок</option>
                            <option value="tonne">Тонна</option>
                        </select>
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementPurchaseUnit'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitWeightFieldId); ?>" data-mixture-unit-label="cement">Вес единицы цемента (кг)</label>
                        <input id="<?php echo esc_attr($cementUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementUnitWeightKg')); ?>" min="0.001" step="0.001" value="50" data-mixture-unit-input="cement">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementUnitWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitPriceFieldId); ?>" data-mixture-price-label="cement">Цена единицы цемента</label>
                        <input id="<?php echo esc_attr($cementUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementUnitPrice')); ?>" min="1" step="1" value="" placeholder="Укажите цену">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementUnitPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>

                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandShareFieldId); ?>">Доля песка</label>
                        <input id="<?php echo esc_attr($sandShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandShare')); ?>" min="0.1" step="0.1" value="2">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandShare'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitTypeFieldId); ?>">Единица покупки песка</label>
                        <select id="<?php echo esc_attr($sandUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('sandPurchaseUnit')); ?>">
                            <option value="tonne">Тонна</option>
                            <option value="bag">Мешок</option>
                        </select>
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandPurchaseUnit'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitWeightFieldId); ?>" data-mixture-unit-label="sand">Вес единицы песка (т)</label>
                        <input id="<?php echo esc_attr($sandUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandUnitWeightKg')); ?>" min="0.001" step="0.001" value="1" data-mixture-unit-input="sand">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandUnitWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitPriceFieldId); ?>" data-mixture-price-label="sand">Цена единицы песка</label>
                        <input id="<?php echo esc_attr($sandUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandUnitPrice')); ?>" min="1" step="1" value="" placeholder="Укажите цену">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandUnitPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>

                <?php if ($includeGravel) : ?>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelShareFieldId); ?>">Доля щебня</label>
                            <input id="<?php echo esc_attr($gravelShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelShare')); ?>" min="0.1" step="0.1" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelShare'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitTypeFieldId); ?>">Единица покупки щебня</label>
                            <select id="<?php echo esc_attr($gravelUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('gravelPurchaseUnit')); ?>">
                                <option value="tonne">Тонна</option>
                                <option value="bag">Мешок</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelPurchaseUnit'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitWeightFieldId); ?>" data-mixture-unit-label="gravel">Вес единицы щебня (т)</label>
                            <input id="<?php echo esc_attr($gravelUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelUnitWeightKg')); ?>" min="0.001" step="0.001" value="1" data-mixture-unit-input="gravel">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelUnitWeightKg'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitPriceFieldId); ?>" data-mixture-price-label="gravel">Цена единицы щебня</label>
                            <input id="<?php echo esc_attr($gravelUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelUnitPrice')); ?>" min="1" step="1" value="" placeholder="Укажите цену">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelUnitPrice'); ?>" aria-live="polite"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($includeGravel) : ?>
                    <p class="brigmaster-estimator__hint"><?php echo esc_html('Объёмное соотношение цемента, песка и щебня. Типично Ц:П:Щ = 1:2:4 (~В15) или 1:1,5:3 (~В20–В25). По объёму, не по массе.'); ?></p>
                <?php else : ?>
                    <p class="brigmaster-estimator__hint"><?php echo esc_html('Объёмное соотношение цемента и песка. Для стяжки типично Ц:П = 1:3. По объёму, не по массе.'); ?></p>
                <?php endif; ?>
                <p class="brigmaster-estimator__hint">В расчёте используются справочные насыпные плотности: цемент 1300 кг/м³, песок 1600 кг/м³, щебень 1400 кг/м³. Вода считается по В/Ц = 0.5.</p>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
