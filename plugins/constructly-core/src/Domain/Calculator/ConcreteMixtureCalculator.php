<?php

declare(strict_types=1);

namespace Brigmaster\Domain\Calculator;

use InvalidArgumentException;

final class ConcreteMixtureCalculator
{
    public const TYPE_READY = 'ready';
    public const TYPE_DRY_READY = 'dry_ready';
    public const TYPE_SELF_MIX = 'self_mix';

    private const PURCHASE_UNIT_BAG = 'bag';
    private const PURCHASE_UNIT_TONNE = 'tonne';

    private const CEMENT_DENSITY_KG_PER_M3 = 1300.0;
    private const SAND_DENSITY_KG_PER_M3 = 1600.0;
    private const GRAVEL_DENSITY_KG_PER_M3 = 1400.0;

    private const CONCRETE_DRY_VOLUME_FACTOR = 1.54;
    private const MORTAR_DRY_VOLUME_FACTOR = 1.33;
    private const WATER_CEMENT_RATIO = 0.5;
    private const DRY_SCREED_CONSUMPTION_KG_PER_M2_PER_10MM = 19.0;
    private const DRY_SCREED_CONSUMPTION_KG_PER_M3 = 1900.0;

    /**
     * @param array<string, mixed>|null $mixture
     * @return array<string, mixed>|null
     */
    public function calculate(string $calculator, float $volumeM3, ?array $mixture): ?array
    {
        if ($mixture === null || $volumeM3 <= 0) {
            return null;
        }

        $type = $this->requireString($mixture, 'type');

        if ($type === self::TYPE_READY) {
            return $this->calculateReadyMix($volumeM3, $mixture);
        }

        if ($type === self::TYPE_DRY_READY) {
            if ($calculator !== 'screed') {
                throw new InvalidArgumentException('The dry_ready mixture type is supported only for screed.');
            }

            return $this->calculateDryScreedMix($volumeM3, $mixture);
        }

        if ($type === self::TYPE_SELF_MIX) {
            if ($calculator === 'screed') {
                return $this->calculateSelfMix($volumeM3, $mixture, false, self::MORTAR_DRY_VOLUME_FACTOR);
            }

            return $this->calculateSelfMix($volumeM3, $mixture, true, self::CONCRETE_DRY_VOLUME_FACTOR);
        }

        throw new InvalidArgumentException('The mixture type must be one of: ready, dry_ready, self_mix.');
    }

    /**
     * @param array<string, mixed> $mixture
     * @return array<string, mixed>
     */
    private function calculateReadyMix(float $volumeM3, array $mixture): array
    {
        $pricePerM3 = $this->optionalPositiveFloat($mixture, 'readyConcretePricePerM3');

        return [
            'type' => self::TYPE_READY,
            'displayType' => 'Готовая',
            'volumeM3' => $volumeM3,
            'pricePerM3' => $pricePerM3,
            'totalCost' => $pricePerM3 !== null ? $volumeM3 * $pricePerM3 : null,
            'note' => 'Готовая смесь считается как итоговый объём товарного бетона, поставляемого на объект в готовом виде.',
        ];
    }

    /**
     * @param array<string, mixed> $mixture
     * @return array<string, mixed>
     */
    private function calculateDryScreedMix(float $volumeM3, array $mixture): array
    {
        $bagWeightKg = $this->requirePositiveFloat($mixture, 'dryMixBagWeightKg');
        $bagPrice = $this->optionalPositiveFloat($mixture, 'dryMixBagPrice');
        $totalWeightKg = $volumeM3 * self::DRY_SCREED_CONSUMPTION_KG_PER_M3;
        $requiredBags = $totalWeightKg / $bagWeightKg;
        $roundedBags = (float) ceil($requiredBags);

        return [
            'type' => self::TYPE_DRY_READY,
            'displayType' => 'Готовая, сухая',
            'volumeM3' => $volumeM3,
            'consumptionKgPerM2Per10mm' => self::DRY_SCREED_CONSUMPTION_KG_PER_M2_PER_10MM,
            'consumptionKgPerM3' => self::DRY_SCREED_CONSUMPTION_KG_PER_M3,
            'totalWeightKg' => $totalWeightKg,
            'bagWeightKg' => $bagWeightKg,
            'bagPrice' => $bagPrice,
            'requiredBags' => $requiredBags,
            'roundedBags' => $roundedBags,
            'totalCostExact' => $bagPrice !== null ? $requiredBags * $bagPrice : null,
            'totalCostRounded' => $bagPrice !== null ? $roundedBags * $bagPrice : null,
            'note' => 'Расход принят по среднему ориентиру: 19 кг сухой смеси на 1 м² при толщине 10 мм (диапазон 18–20 кг). Точный расход уточняйте по паспорту смеси.',
        ];
    }

    /**
     * @param array<string, mixed> $mixture
     * @return array<string, mixed>
     */
    private function calculateSelfMix(float $volumeM3, array $mixture, bool $includeGravel, float $dryVolumeFactor): array
    {
        $shares = [
            'cement' => $this->requirePositiveFloat($mixture, 'cementShare'),
            'sand' => $this->requirePositiveFloat($mixture, 'sandShare'),
        ];

        if ($includeGravel) {
            $shares['gravel'] = $this->requirePositiveFloat($mixture, 'gravelShare');
        }

        $dryVolumeM3 = $volumeM3 * $dryVolumeFactor;
        $sharesSum = array_sum($shares);
        if ($sharesSum <= 0) {
            throw new InvalidArgumentException('The total self-mix shares must be greater than 0.');
        }

        $components = [
            'cement' => $this->buildComponent(
                label: 'Цемент',
                share: $shares['cement'],
                densityKgPerM3: self::CEMENT_DENSITY_KG_PER_M3,
                dryVolumeM3: $dryVolumeM3,
                sharesSum: $sharesSum,
                purchaseUnit: $this->requirePurchaseUnit($mixture, 'cementPurchaseUnit'),
                unitWeightKg: $this->requirePositiveFloat($mixture, 'cementUnitWeightKg'),
                unitPrice: $this->optionalPositiveFloat($mixture, 'cementUnitPrice')
            ),
            'sand' => $this->buildComponent(
                label: 'Песок',
                share: $shares['sand'],
                densityKgPerM3: self::SAND_DENSITY_KG_PER_M3,
                dryVolumeM3: $dryVolumeM3,
                sharesSum: $sharesSum,
                purchaseUnit: $this->requirePurchaseUnit($mixture, 'sandPurchaseUnit'),
                unitWeightKg: $this->requirePositiveFloat($mixture, 'sandUnitWeightKg'),
                unitPrice: $this->optionalPositiveFloat($mixture, 'sandUnitPrice')
            ),
        ];

        if ($includeGravel) {
            $components['gravel'] = $this->buildComponent(
                label: 'Щебень',
                share: $shares['gravel'],
                densityKgPerM3: self::GRAVEL_DENSITY_KG_PER_M3,
                dryVolumeM3: $dryVolumeM3,
                sharesSum: $sharesSum,
                purchaseUnit: $this->requirePurchaseUnit($mixture, 'gravelPurchaseUnit'),
                unitWeightKg: $this->requirePositiveFloat($mixture, 'gravelUnitWeightKg'),
                unitPrice: $this->optionalPositiveFloat($mixture, 'gravelUnitPrice')
            );
        }

        $waterLiters = $components['cement']['weightKg'] * self::WATER_CEMENT_RATIO;
        $hasCosts = false;
        $totalCostExact = 0.0;
        $totalCostRounded = 0.0;
        foreach ($components as $component) {
            if ($component['totalCostExact'] !== null) {
                $hasCosts = true;
                $totalCostExact += $component['totalCostExact'];
                $totalCostRounded += $component['totalCostRounded'];
            }
        }

        return [
            'type' => self::TYPE_SELF_MIX,
            'displayType' => 'Самомесная',
            'volumeM3' => $volumeM3,
            'dryVolumeFactor' => $dryVolumeFactor,
            'waterCementRatio' => self::WATER_CEMENT_RATIO,
            'waterLiters' => $waterLiters,
            'components' => $components,
            'totalCostExact' => $hasCosts ? $totalCostExact : null,
            'totalCostRounded' => $hasCosts ? $totalCostRounded : null,
            'note' => 'Для самомесной смеси доли принимаются по объёму. Для пересчёта в массу использованы справочные насыпные плотности, количество воды рассчитано по В/Ц = 0.5.',
        ];
    }

    /**
     * @return array<string, float|string>
     */
    private function buildComponent(
        string $label,
        float $share,
        float $densityKgPerM3,
        float $dryVolumeM3,
        float $sharesSum,
        string $purchaseUnit,
        float $unitWeightKg,
        ?float $unitPrice
    ): array {
        $volumeM3 = $dryVolumeM3 * ($share / $sharesSum);
        $weightKg = $volumeM3 * $densityKgPerM3;
        $displayUnitWeight = $purchaseUnit === self::PURCHASE_UNIT_TONNE
            ? $unitWeightKg / 1000.0
            : $unitWeightKg;
        $requiredUnits = $weightKg / $unitWeightKg;
        $roundedUnits = (float) ceil($requiredUnits);

        return [
            'label' => $label,
            'share' => $share,
            'densityKgPerM3' => $densityKgPerM3,
            'volumeM3' => $volumeM3,
            'weightKg' => $weightKg,
            'purchaseUnit' => $purchaseUnit,
            'purchaseUnitLabel' => $purchaseUnit === self::PURCHASE_UNIT_TONNE ? 'т' : 'мешок',
            'displayUnitWeight' => $displayUnitWeight,
            'unitWeightKg' => $unitWeightKg,
            'unitPrice' => $unitPrice,
            'requiredUnits' => $requiredUnits,
            'roundedUnits' => $roundedUnits,
            'totalCostExact' => $unitPrice !== null ? $requiredUnits * $unitPrice : null,
            'totalCostRounded' => $unitPrice !== null ? $roundedUnits * $unitPrice : null,
        ];
    }

    /**
     * @param array<string, mixed> $mixture
     */
    private function requireString(array $mixture, string $field): string
    {
        $value = $mixture[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('The %s field is required.', $field));
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $mixture
     */
    private function requirePositiveFloat(array $mixture, string $field): float
    {
        $value = $mixture[$field] ?? null;
        if ((!is_string($value) && !is_int($value) && !is_float($value)) || !is_numeric((string) $value)) {
            throw new InvalidArgumentException(sprintf('The %s field must be numeric.', $field));
        }

        $normalized = (float) $value;
        if ($normalized <= 0) {
            throw new InvalidArgumentException(sprintf('The %s field must be greater than 0.', $field));
        }

        return $normalized;
    }

    /**
     * Returns null when the field is absent or empty string; throws when present but not a positive number.
     *
     * @param array<string, mixed> $mixture
     */
    private function optionalPositiveFloat(array $mixture, string $field): ?float
    {
        $value = $mixture[$field] ?? null;
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if ((!is_string($value) && !is_int($value) && !is_float($value)) || !is_numeric((string) $value)) {
            throw new InvalidArgumentException(sprintf('The %s field must be numeric.', $field));
        }

        $normalized = (float) $value;
        if ($normalized <= 0) {
            throw new InvalidArgumentException(sprintf('The %s field must be greater than 0.', $field));
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $mixture
     */
    private function requirePurchaseUnit(array $mixture, string $field): string
    {
        $value = $this->requireString($mixture, $field);
        if (!in_array($value, [self::PURCHASE_UNIT_BAG, self::PURCHASE_UNIT_TONNE], true)) {
            throw new InvalidArgumentException(sprintf('The %s field must be one of: bag, tonne.', $field));
        }

        return $value;
    }
}
