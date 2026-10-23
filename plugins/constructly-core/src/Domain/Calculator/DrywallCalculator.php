<?php

declare(strict_types=1);

namespace Brigmaster\Domain\Calculator;

use Brigmaster\Domain\DTO\EstimateInput;
use Brigmaster\Domain\DTO\EstimateResult;
use InvalidArgumentException;

final class DrywallCalculator implements CalculatorInterface
{
    private const MILLIMETERS_IN_METER = 1000.0;
    private const DEFAULT_SHEET_LENGTH_MM = 2500.0;
    private const DEFAULT_SHEET_WIDTH_MM = 1200.0;
    private const DEFAULT_SHEET_THICKNESS_MM = 12.5;
    private const DEFAULT_LAYERS = 1;
    private const DEFAULT_FRAME_STEP_MM = 600.0;
    private const DEFAULT_PARTITION_PROFILE_WIDTH_MM = 50.0;
    private const DEFAULT_RESERVE_PERCENT = 10.0;
    private const DEFAULT_FASTENER_RESERVE_PERCENT = 10.0;
    private const PROFILE_PURCHASE_STEP_M = 1.0;
    private const GUIDE_FIX_STEP_M = 0.5;
    private const WALL_HANGER_STEP_M = 0.8;
    private const CEILING_HANGER_STEP_M = 0.8;
    private const SCREWS_PER_M2_SINGLE_LAYER_STEP_400 = 23.0;
    private const SCREWS_PER_M2_SINGLE_LAYER_STEP_600 = 17.0;
    private const SCREWS_PER_M2_DOUBLE_LAYER_STEP_400 = 46.0;
    private const SCREWS_PER_M2_DOUBLE_LAYER_STEP_600 = 34.0;
    private const LB_SCREWS_PER_VERTICAL_PROFILE = 4.0;
    private const LB_SCREWS_PER_HANGER = 2.0;
    private const LB_SCREWS_PER_CRAB = 8.0;
    private const DOWELS_PER_HANGER = 2.0;
    private const DIRECT_HANGER_LABEL = 'Прямой подвес';
    private const CRAB_LABEL = 'Соединитель "Краб"';
    private const PRIMER_KG_PER_M2 = 0.1;
    private const JOINT_PUTTY_KG_PER_M2 = 0.4;
    private const FINISH_PUTTY_KG_PER_M2 = 1.2;
    private const TAPE_LM_PER_M2 = 1.2;

    private const TARGET_WALL = 'wall';
    private const TARGET_CEILING = 'ceiling';
    private const TARGET_PARTITION = 'partition';

    public function calculate(EstimateInput $input): EstimateResult
    {
        if (!in_array($input->mode, [EstimateInput::MODE_DIMENSIONS, EstimateInput::MODE_AREA], true)) {
            throw new InvalidArgumentException('Field "mode" must be one of: dimensions, area for drywall.');
        }

        $target = $this->normalizeTarget($input->drywallTarget);
        $sheetLengthMm = $this->resolvePositiveOrDefault($input->drywallSheetLengthMm, self::DEFAULT_SHEET_LENGTH_MM);
        $sheetWidthMm = $this->resolvePositiveOrDefault($input->drywallSheetWidthMm, self::DEFAULT_SHEET_WIDTH_MM);
        $sheetThicknessMm = $this->resolvePositiveOrDefault($input->drywallSheetThicknessMm, self::DEFAULT_SHEET_THICKNESS_MM);
        $layers = $this->resolveIntegerOrDefault($input->drywallLayers, self::DEFAULT_LAYERS);
        $frameStepMm = $this->resolvePositiveOrDefault($input->drywallFrameStepMm, self::DEFAULT_FRAME_STEP_MM);
        $profileWidthMm = $target === self::TARGET_PARTITION
            ? $this->resolvePositiveOrDefault($input->drywallProfileWidthMm, self::DEFAULT_PARTITION_PROFILE_WIDTH_MM)
            : 0.0;
        $reservePercent = $this->resolvePositiveOrDefault($input->reservePercent, self::DEFAULT_RESERVE_PERCENT);
        $fastenerReservePercent = $this->resolvePositiveOrDefault(
            $input->drywallFastenerReservePercent,
            self::DEFAULT_FASTENER_RESERVE_PERCENT
        );

        if (!in_array($layers, [1, 2], true)) {
            throw new InvalidArgumentException('Field "drywallLayers" must be 1 or 2.');
        }

        if (!in_array((int) round($frameStepMm), [400, 600], true)) {
            throw new InvalidArgumentException('Field "drywallFrameStepMm" must be one of: 400, 600.');
        }

        if ($target === self::TARGET_PARTITION && !in_array((int) round($profileWidthMm), [50, 75, 100], true)) {
            throw new InvalidArgumentException('Field "drywallProfileWidthMm" must be one of: 50, 75, 100 for partitions.');
        }

        $includeOpenings = $input->includeOpenings === true && $target !== self::TARGET_CEILING;
        $includeEndCladding = $target === self::TARGET_PARTITION && $input->drywallIncludeEndCladding === true;
        $includeFinishing = $input->drywallIncludeFinishing === true;
        $includeCosts = $input->drywallIncludeCosts === true;

        $dimensions = $this->resolveDimensions($input, $target);
        $openings = $includeOpenings ? $this->normalizeOpenings($input->windows, $input->doors) : [];
        $openingsAreaM2 = $includeOpenings ? $this->sumOpeningsArea($openings) : 0.0;
        $grossAreaM2 = $dimensions['grossAreaM2'];
        $netAreaM2 = $grossAreaM2 - $openingsAreaM2;

        if ($netAreaM2 <= 0.0) {
            throw new InvalidArgumentException('Net drywall area must be greater than 0 after subtracting openings.');
        }

        $partitionThicknessMm = $target === self::TARGET_PARTITION
            ? $profileWidthMm + (2.0 * $layers * $sheetThicknessMm)
            : 0.0;
        $endCladdingAreaM2 = $includeEndCladding
            ? $this->calculateOpeningEdgeArea($openings, $partitionThicknessMm / self::MILLIMETERS_IN_METER, $layers)
            : 0.0;

        $surfaceMultiplier = $target === self::TARGET_PARTITION ? 2.0 * $layers : (float) $layers;
        $boardAreaExactM2 = ($netAreaM2 * $surfaceMultiplier) + $endCladdingAreaM2;
        $boardAreaWithReserveM2 = $boardAreaExactM2 * (1.0 + ($reservePercent / 100.0));

        $sheetAreaM2 = ($sheetLengthMm / self::MILLIMETERS_IN_METER) * ($sheetWidthMm / self::MILLIMETERS_IN_METER);
        if ($sheetAreaM2 <= 0.0) {
            throw new InvalidArgumentException('Drywall sheet dimensions must be greater than 0.');
        }

        $sheetsExact = $boardAreaExactM2 / $sheetAreaM2;
        $sheetsWithReserve = $boardAreaWithReserveM2 / $sheetAreaM2;
        $sheetsToBuy = (float) ceil($sheetsWithReserve);

        $profiles = $input->mode === EstimateInput::MODE_DIMENSIONS
            ? $this->calculateProfiles(
                target: $target,
                lengthM: $dimensions['lengthM'],
                widthM: $dimensions['widthM'],
                heightM: $dimensions['heightM'],
                sheetLengthMm: $sheetLengthMm,
                frameStepMm: $frameStepMm,
                profileWidthMm: $profileWidthMm,
                openings: $openings
            )
            : $this->emptyProfiles($target, $profileWidthMm);

        $fasteners = $this->calculateFasteners(
            target: $target,
            netAreaM2: $netAreaM2,
            layers: $layers,
            frameStepMm: $frameStepMm,
            profiles: $profiles,
            fastenerReservePercent: $fastenerReservePercent
        );

        $finishing = $this->calculateFinishing(
            enabled: $includeFinishing,
            boardAreaExactM2: $boardAreaExactM2
        );

        $costs = $this->calculateCosts(
            input: $input,
            includeCosts: $includeCosts,
            sheetsToBuy: $sheetsToBuy,
            profiles: $profiles,
            fasteners: $fasteners,
            finishing: $finishing
        );

        return new EstimateResult(
            mode: $input->mode,
            calculatedVolume: $netAreaM2,
            calculatedMaterialAmount: $sheetsWithReserve,
            details: [
                'geometry' => [
                    'target' => $target,
                    'inputMode' => $input->mode,
                    'grossAreaM2' => $grossAreaM2,
                    'openingsAreaM2' => $openingsAreaM2,
                    'netAreaM2' => $netAreaM2,
                    'boardAreaExactM2' => $boardAreaExactM2,
                    'boardAreaWithReserveM2' => $boardAreaWithReserveM2,
                    'reservePercent' => $reservePercent,
                    'partitionThicknessMm' => $partitionThicknessMm,
                    'endCladdingAreaM2' => $endCladdingAreaM2,
                    'lengthM' => $dimensions['lengthM'],
                    'widthM' => $dimensions['widthM'],
                    'heightM' => $dimensions['heightM'],
                    'openingsCount' => count($openings),
                ],
                'sheets' => [
                    'sheetLengthMm' => $sheetLengthMm,
                    'sheetWidthMm' => $sheetWidthMm,
                    'sheetThicknessMm' => $sheetThicknessMm,
                    'sheetAreaM2' => $sheetAreaM2,
                    'layers' => $layers,
                    'countExact' => $sheetsExact,
                    'countWithReserve' => $sheetsWithReserve,
                    'countToBuy' => $sheetsToBuy,
                ],
                'profiles' => $profiles,
                'fasteners' => $fasteners,
                'finishing' => $finishing,
                'costs' => $costs,
                'openings' => [
                    'enabled' => $includeOpenings,
                    'items' => $openings,
                ],
                'notes' => [
                    'profilesAreaMode' => $input->mode === EstimateInput::MODE_AREA
                        ? 'В режиме по площади профили и крепёж по каркасу не считаются, потому что геометрия конструкции не задана.'
                        : '',
                    'method' => 'Расчёт ориентирован на прямоугольную конструкцию и стандартный металлический каркас.',
                ],
            ]
        );
    }

    private function normalizeTarget(?string $target): string
    {
        $normalized = trim((string) $target);
        if (!in_array($normalized, [self::TARGET_WALL, self::TARGET_CEILING, self::TARGET_PARTITION], true)) {
            throw new InvalidArgumentException('Field "drywallTarget" must be one of: wall, ceiling, partition.');
        }

        return $normalized;
    }

    /**
     * @return array{grossAreaM2: float, lengthM: float, widthM: float, heightM: float}
     */
    private function resolveDimensions(EstimateInput $input, string $target): array
    {
        if ($input->mode === EstimateInput::MODE_AREA) {
            return [
                'grossAreaM2' => $this->requirePositive($input->area, 'area'),
                'lengthM' => 0.0,
                'widthM' => 0.0,
                'heightM' => 0.0,
            ];
        }

        if ($target === self::TARGET_CEILING) {
            $lengthM = $this->requirePositive($input->length, 'length');
            $widthM = $this->requirePositive($input->width, 'width');

            return [
                'grossAreaM2' => $lengthM * $widthM,
                'lengthM' => $lengthM,
                'widthM' => $widthM,
                'heightM' => 0.0,
            ];
        }

        $lengthM = $this->requirePositive($input->length, 'length');
        $heightM = $this->requirePositive($input->height, 'height');

        return [
            'grossAreaM2' => $lengthM * $heightM,
            'lengthM' => $lengthM,
            'widthM' => 0.0,
            'heightM' => $heightM,
        ];
    }

    /**
     * @param array<int, array<string, mixed>>|null $windows
     * @param array<int, array<string, mixed>>|null $doors
     * @return array<int, array<string, mixed>>
     */
    private function normalizeOpenings(?array $windows, ?array $doors): array
    {
        $items = [];

        foreach ($windows ?? [] as $item) {
            $items[] = $this->normalizeOpening($item, 'window', 'Окно');
        }

        foreach ($doors ?? [] as $item) {
            $items[] = $this->normalizeOpening($item, 'door', 'Дверь');
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function normalizeOpening(array $item, string $type, string $label): array
    {
        $widthM = $this->requirePositive($item['widthM'] ?? null, 'opening.widthM');
        $heightM = $this->requirePositive($item['heightM'] ?? null, 'opening.heightM');
        $count = (int) round($this->requirePositive($item['count'] ?? null, 'opening.count'));

        return [
            'type' => $type,
            'label' => $label,
            'widthM' => $widthM,
            'heightM' => $heightM,
            'count' => $count,
            'areaM2' => $widthM * $heightM * $count,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     */
    private function sumOpeningsArea(array $openings): float
    {
        $area = 0.0;

        foreach ($openings as $opening) {
            $area += (float) ($opening['areaM2'] ?? 0.0);
        }

        return $area;
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     */
    private function calculateOpeningEdgeArea(array $openings, float $thicknessM, int $layers): float
    {
        if ($thicknessM <= 0.0) {
            return 0.0;
        }

        $area = 0.0;
        foreach ($openings as $opening) {
            $widthM = (float) ($opening['widthM'] ?? 0.0);
            $heightM = (float) ($opening['heightM'] ?? 0.0);
            $count = (int) ($opening['count'] ?? 0);
            $type = (string) ($opening['type'] ?? 'window');
            $edgeLengthM = $type === 'door'
                ? ((2.0 * $heightM) + $widthM)
                : ((2.0 * $heightM) + (2.0 * $widthM));

            $area += $edgeLengthM * $thicknessM * $count * $layers;
        }

        return $area;
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     * @return array<string, mixed>
     */
    private function calculateProfiles(
        string $target,
        float $lengthM,
        float $widthM,
        float $heightM,
        float $sheetLengthMm,
        float $frameStepMm,
        float $profileWidthMm,
        array $openings
    ): array {
        $sheetLengthM = $sheetLengthMm / self::MILLIMETERS_IN_METER;
        $stepM = $frameStepMm / self::MILLIMETERS_IN_METER;

        if ($target === self::TARGET_WALL) {
            $guideLengthM = $lengthM * 2.0;
            $verticalCount = (float) (ceil($lengthM / $stepM) + 1);
            $verticalLengthM = $verticalCount * $heightM;
            $seamRows = max(0.0, ceil($heightM / $sheetLengthM) - 1.0);
            $jumperLengthM = $lengthM * $seamRows;
            $hangersCount = $verticalCount * max(1.0, ceil($heightM / self::WALL_HANGER_STEP_M) - 1.0);

            return [
                'enabled' => true,
                'guide' => $this->buildProfileRow('Направляющий ПН 28×27', $guideLengthM),
                'main' => $this->buildProfileRow('Стоечный ПП 60×27', $verticalLengthM, $verticalCount),
                'cross' => $this->buildProfileRow('Горизонтальные перемычки ПП 60×27', $jumperLengthM, $seamRows),
                'hangers' => [
                    'label' => self::DIRECT_HANGER_LABEL,
                    'countBase' => $hangersCount,
                    'countWithReserve' => $hangersCount,
                ],
            ];
        }

        if ($target === self::TARGET_CEILING) {
            $guideLengthM = 2.0 * ($lengthM + $widthM);
            $mainCount = (float) (ceil($widthM / $stepM) + 1);
            $mainLengthM = $mainCount * $lengthM;
            $seamRows = max(0.0, ceil($lengthM / $sheetLengthM) - 1.0);
            $crossLengthM = $widthM * $seamRows;
            $hangersCount = $mainCount * max(1.0, ceil($lengthM / self::CEILING_HANGER_STEP_M) - 1.0);
            $crabsCount = $seamRows * max(0.0, $mainCount - 1.0);

            return [
                'enabled' => true,
                'guide' => $this->buildProfileRow('Направляющий ПНП 27×28', $guideLengthM),
                'main' => $this->buildProfileRow('Основной ПП 60×27', $mainLengthM, $mainCount),
                'cross' => $this->buildProfileRow('Поперечный ПП 60×27', $crossLengthM, $seamRows),
                'hangers' => [
                    'label' => self::DIRECT_HANGER_LABEL,
                    'countBase' => $hangersCount,
                    'countWithReserve' => $hangersCount,
                ],
                'crabs' => [
                    'label' => self::CRAB_LABEL,
                    'countBase' => $crabsCount,
                    'countWithReserve' => $crabsCount,
                ],
            ];
        }

        $section = sprintf(
            'Профиль %d мм (%s / %s)',
            (int) round($profileWidthMm),
            sprintf('ПН %d×40', (int) round($profileWidthMm)),
            sprintf('ПС %d×50', (int) round($profileWidthMm))
        );
        $guideLengthM = $lengthM * 2.0;
        $verticalCount = (float) (ceil($lengthM / $stepM) + 1);
        $verticalLengthM = $verticalCount * $heightM;
        $seamRows = max(0.0, ceil($heightM / $sheetLengthM) - 1.0);
        $jumperLengthM = $lengthM * $seamRows;
        $openingFrameLengthM = $this->sumOpeningFrameLength($openings);

        return [
            'enabled' => true,
            'guide' => $this->buildProfileRow($section . ' — направляющий', $guideLengthM),
            'main' => $this->buildProfileRow($section . ' — стойки', $verticalLengthM, $verticalCount),
            'cross' => $this->buildProfileRow($section . ' — перемычки', $jumperLengthM + $openingFrameLengthM, $seamRows + count($openings)),
            'hangers' => [
                'label' => self::DIRECT_HANGER_LABEL,
                'countBase' => 0.0,
                'countWithReserve' => 0.0,
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $openings
     */
    private function sumOpeningFrameLength(array $openings): float
    {
        $length = 0.0;

        foreach ($openings as $opening) {
            $widthM = (float) ($opening['widthM'] ?? 0.0);
            $heightM = (float) ($opening['heightM'] ?? 0.0);
            $count = (int) ($opening['count'] ?? 0);
            $type = (string) ($opening['type'] ?? 'window');
            $frameLength = $type === 'door'
                ? ((2.0 * $heightM) + $widthM)
                : ((2.0 * $heightM) + (2.0 * $widthM));

            $length += $frameLength * $count;
        }

        return $length;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyProfiles(string $target, float $profileWidthMm): array
    {
        $mainLabel = $target === self::TARGET_PARTITION
            ? sprintf('Профиль %d мм — стойки', (int) round($profileWidthMm))
            : ($target === self::TARGET_CEILING ? 'Основной ПП 60×27' : 'Стоечный ПП 60×27');
        $guideLabel = $target === self::TARGET_PARTITION
            ? sprintf('Профиль %d мм — направляющий', (int) round($profileWidthMm))
            : ($target === self::TARGET_CEILING ? 'Направляющий ПНП 27×28' : 'Направляющий ПН 28×27');

        return [
            'enabled' => false,
            'guide' => $this->buildProfileRow($guideLabel, 0.0),
            'main' => $this->buildProfileRow($mainLabel, 0.0),
            'cross' => $this->buildProfileRow('Перемычки', 0.0),
            'hangers' => [
                'label' => self::DIRECT_HANGER_LABEL,
                'countBase' => 0.0,
                'countWithReserve' => 0.0,
            ],
            'crabs' => [
                'label' => self::CRAB_LABEL,
                'countBase' => 0.0,
                'countWithReserve' => 0.0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProfileRow(string $label, float $lengthM, float $itemCount = 0.0): array
    {
        return [
            'label' => $label,
            'lengthM' => $lengthM,
            'lengthToBuyM' => ceil($lengthM / self::PROFILE_PURCHASE_STEP_M) * self::PROFILE_PURCHASE_STEP_M,
            'itemCount' => $itemCount,
        ];
    }

    /**
     * @param array<string, mixed> $profiles
     * @return array<string, mixed>
     */
    private function calculateFasteners(string $target, float $netAreaM2, int $layers, float $frameStepMm, array $profiles, float $fastenerReservePercent): array
    {
        $screwsPerM2 = $this->resolveScrewsPerM2($layers, $frameStepMm);
        $boardScrewsBase = $netAreaM2 * $screwsPerM2 * ($target === self::TARGET_PARTITION ? 2.0 : 1.0);

        $guideLengthM = (float) ($profiles['guide']['lengthM'] ?? 0.0);
        $mainLengthM = (float) ($profiles['main']['lengthM'] ?? 0.0);
        $crossLengthM = (float) ($profiles['cross']['lengthM'] ?? 0.0);
        $hangersBase = (float) ($profiles['hangers']['countBase'] ?? 0.0);
        $crabsBase = (float) ($profiles['crabs']['countBase'] ?? 0.0);

        $verticalCountApprox = (float) ($profiles['main']['itemCount'] ?? 0.0);
        $connectorScrewsBase = $target === self::TARGET_CEILING
            ? ($hangersBase * self::LB_SCREWS_PER_HANGER) + ($crabsBase * self::LB_SCREWS_PER_CRAB)
            : ($verticalCountApprox * self::LB_SCREWS_PER_VERTICAL_PROFILE);
        $dowelsBase = ceil($guideLengthM / self::GUIDE_FIX_STEP_M) + ($hangersBase * self::DOWELS_PER_HANGER);

        return [
            'reservePercent' => $fastenerReservePercent,
            'boardScrews' => $this->buildFastenerRow('Саморезы для ГКЛ', $boardScrewsBase, $fastenerReservePercent),
            'connectorScrews' => $this->buildFastenerRow('Саморезы по металлу', $connectorScrewsBase, $fastenerReservePercent),
            'dowels' => $this->buildFastenerRow('Дюбель-гвозди', $dowelsBase, $fastenerReservePercent),
            'hangers' => $this->buildFastenerRow(self::DIRECT_HANGER_LABEL, $hangersBase, $fastenerReservePercent),
            'crabs' => $this->buildFastenerRow(self::CRAB_LABEL, $crabsBase, $fastenerReservePercent),
            'profileTotalLengthM' => $guideLengthM + $mainLengthM + $crossLengthM,
        ];
    }

    private function resolveScrewsPerM2(int $layers, float $frameStepMm): float
    {
        $isStep400 = (int) round($frameStepMm) === 400;

        if ($layers === 2) {
            return $isStep400
                ? self::SCREWS_PER_M2_DOUBLE_LAYER_STEP_400
                : self::SCREWS_PER_M2_DOUBLE_LAYER_STEP_600;
        }

        return $isStep400
            ? self::SCREWS_PER_M2_SINGLE_LAYER_STEP_400
            : self::SCREWS_PER_M2_SINGLE_LAYER_STEP_600;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFastenerRow(string $label, float $countBase, float $reservePercent): array
    {
        return [
            'label' => $label,
            'countBase' => $countBase,
            'countWithReserve' => ceil($countBase * (1.0 + ($reservePercent / 100.0))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateFinishing(bool $enabled, float $boardAreaExactM2): array
    {
        if (!$enabled) {
            return [
                'enabled' => false,
                'primerKg' => 0.0,
                'jointPuttyKg' => 0.0,
                'finishPuttyKg' => 0.0,
                'tapeLm' => 0.0,
            ];
        }

        return [
            'enabled' => true,
            'primerKg' => $boardAreaExactM2 * self::PRIMER_KG_PER_M2,
            'jointPuttyKg' => $boardAreaExactM2 * self::JOINT_PUTTY_KG_PER_M2,
            'finishPuttyKg' => $boardAreaExactM2 * self::FINISH_PUTTY_KG_PER_M2,
            'tapeLm' => $boardAreaExactM2 * self::TAPE_LM_PER_M2,
        ];
    }

    /**
     * @param array<string, mixed> $profiles
     * @param array<string, mixed> $fasteners
     * @param array<string, mixed> $finishing
     * @return array<string, mixed>
     */
    private function calculateCosts(
        EstimateInput $input,
        bool $includeCosts,
        float $sheetsToBuy,
        array $profiles,
        array $fasteners,
        array $finishing
    ): array {
        if (!$includeCosts) {
            return ['enabled' => false];
        }

        $sheetCost = $this->hasPositive($input->drywallSheetPrice)
            ? $sheetsToBuy * (float) $input->drywallSheetPrice
            : null;

        $profileLengthToBuy = (float) ($profiles['guide']['lengthToBuyM'] ?? 0.0)
            + (float) ($profiles['main']['lengthToBuyM'] ?? 0.0)
            + (float) ($profiles['cross']['lengthToBuyM'] ?? 0.0);
        $profileCost = $this->hasPositive($input->drywallProfilePricePerLm)
            ? $profileLengthToBuy * (float) $input->drywallProfilePricePerLm
            : null;

        $fastenersTotal = (float) ($fasteners['boardScrews']['countWithReserve'] ?? 0.0)
            + (float) ($fasteners['connectorScrews']['countWithReserve'] ?? 0.0)
            + (float) ($fasteners['dowels']['countWithReserve'] ?? 0.0)
            + (float) ($fasteners['hangers']['countWithReserve'] ?? 0.0)
            + (float) ($fasteners['crabs']['countWithReserve'] ?? 0.0);
        $fastenersCost = $this->hasPositive($input->drywallFastenerPricePer100)
            ? ($fastenersTotal / 100.0) * (float) $input->drywallFastenerPricePer100
            : null;

        $primerCost = $finishing['enabled'] && $this->hasPositive($input->drywallPrimerPricePerKg)
            ? (float) $finishing['primerKg'] * (float) $input->drywallPrimerPricePerKg
            : null;
        $jointPuttyCost = $finishing['enabled'] && $this->hasPositive($input->drywallJointPuttyPricePerKg)
            ? (float) $finishing['jointPuttyKg'] * (float) $input->drywallJointPuttyPricePerKg
            : null;
        $finishPuttyCost = $finishing['enabled'] && $this->hasPositive($input->drywallFinishPuttyPricePerKg)
            ? (float) $finishing['finishPuttyKg'] * (float) $input->drywallFinishPuttyPricePerKg
            : null;
        $tapeCost = $finishing['enabled'] && $this->hasPositive($input->drywallTapePricePerLm)
            ? (float) $finishing['tapeLm'] * (float) $input->drywallTapePricePerLm
            : null;

        $total = 0.0;
        foreach ([$sheetCost, $profileCost, $fastenersCost, $primerCost, $jointPuttyCost, $finishPuttyCost, $tapeCost] as $cost) {
            if ($cost !== null) {
                $total += $cost;
            }
        }

        return [
            'enabled' => true,
            'sheetCost' => $sheetCost,
            'profileCost' => $profileCost,
            'fastenersCost' => $fastenersCost,
            'primerCost' => $primerCost,
            'jointPuttyCost' => $jointPuttyCost,
            'finishPuttyCost' => $finishPuttyCost,
            'tapeCost' => $tapeCost,
            'total' => $total > 0.0 ? $total : null,
        ];
    }

    private function requirePositive(mixed $value, string $field): float
    {
        if (!is_numeric($value) || (float) $value <= 0.0) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be greater than 0.', $field));
        }

        return (float) $value;
    }

    private function resolvePositiveOrDefault(?float $value, float $default): float
    {
        if ($value === null) {
            return $default;
        }

        if ($value <= 0.0) {
            throw new InvalidArgumentException('Optional numeric field must be greater than 0 when provided.');
        }

        return $value;
    }

    private function resolveIntegerOrDefault(?int $value, int $default): int
    {
        if ($value === null) {
            return $default;
        }

        if ($value <= 0) {
            throw new InvalidArgumentException('Optional integer field must be greater than 0 when provided.');
        }

        return $value;
    }

    private function hasPositive(?float $value): bool
    {
        return $value !== null && $value > 0.0;
    }
}
