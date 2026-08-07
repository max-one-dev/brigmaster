<?php

declare(strict_types=1);

namespace Brigmaster\Domain\Calculator;

use Brigmaster\Domain\DTO\EstimateInput;
use InvalidArgumentException;

/**
 * Orthogonal grid rebar over a rectangular slab-like area (same model as slab foundation).
 */
final class GridRebarCalculator
{
    private const DEFAULT_REBAR_DIAMETER_MM = 4.0;
    private const DEFAULT_REBAR_STEP_MM = 200.0;
    private const DEFAULT_REBAR_LAYERS = 1;
    private const DEFAULT_REBAR_RESERVE_PERCENT = 10.0;

    /** @var array<int> */
    private const ALLOWED_REBAR_LAYERS = [1, 2];

    /**
     * @return array<string, float|int>
     */
    public static function calculate(EstimateInput $input, float $length, float $width): array
    {
        $diameterMm = $input->rebarDiameterMm ?? self::DEFAULT_REBAR_DIAMETER_MM;
        $stepMm = $input->rebarStepMm ?? self::DEFAULT_REBAR_STEP_MM;
        $layers = $input->rebarLayers ?? self::DEFAULT_REBAR_LAYERS;
        $reservePercent = $input->rebarReservePercent ?? self::DEFAULT_REBAR_RESERVE_PERCENT;

        if ($diameterMm <= 0) {
            throw new InvalidArgumentException('Field "rebarDiameterMm" must be greater than 0.');
        }

        if ($stepMm <= 0) {
            throw new InvalidArgumentException('Field "rebarStepMm" must be greater than 0.');
        }

        if (!in_array($layers, self::ALLOWED_REBAR_LAYERS, true)) {
            throw new InvalidArgumentException('Field "rebarLayers" must be one of: 1, 2.');
        }

        if ($reservePercent <= 0) {
            throw new InvalidArgumentException('Field "rebarReservePercent" must be greater than 0.');
        }

        $stepM = $stepMm / 1000.0;
        $barsAlongLength = floor($width / $stepM) + 1;
        $barsAlongWidth = floor($length / $stepM) + 1;
        $totalLength = ($barsAlongLength * $length + $barsAlongWidth * $width) * $layers;
        $totalLengthWithReserve = $totalLength * (1 + ($reservePercent / 100.0));
        $unitWeightKgPerM = RebarWeightCalculator::resolveUnitWeightKgPerM($diameterMm);
        $massKg = $totalLengthWithReserve * $unitWeightKgPerM;

        return [
            'diameterMm' => $diameterMm,
            'stepMm' => $stepMm,
            'layers' => $layers,
            'reservePercent' => $reservePercent,
            'barsAlongLength' => $barsAlongLength,
            'barsAlongWidth' => $barsAlongWidth,
            'totalLengthM' => $totalLength,
            'totalLengthWithReserveM' => $totalLengthWithReserve,
            'unitWeightKgPerM' => $unitWeightKgPerM,
            'massKg' => $massKg,
        ];
    }
}
