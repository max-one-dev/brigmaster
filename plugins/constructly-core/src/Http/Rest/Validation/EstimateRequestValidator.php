<?php

declare(strict_types=1);

namespace Brigmaster\Http\Rest\Validation;

use Brigmaster\Application\EstimateService;
use Brigmaster\Domain\DTO\EstimateInput;

/**
 * Validates a raw estimate REST request.
 *
 * Extracted verbatim from EstimateController without behavior changes: identical error
 * messages, branching and ALLOWED_* rules. `validateRequest` was renamed to `validate`.
 */
final class EstimateRequestValidator
{
    use RequestValueHelpers;

    /** Single source of truth lives in EstimateService; aliased here so existing self:: usages keep working. */
    private const ALLOWED_CALCULATORS = EstimateService::ALLOWED_CALCULATORS;

    private const ALLOWED_TILE_MODES = [
        'dimensions',
        'area',
    ];

    private const ALLOWED_DRYWALL_MODES = [
        'dimensions',
        'area',
    ];

    private const ALLOWED_BRICK_MODES = [
        'dimensions',
        'area',
    ];

    private const ALLOWED_SLAB_FOUNDATION_MODES = [
        'dimensions',
        'area',
    ];

    private const ALLOWED_STRIP_FOUNDATION_MODES = [
        'perimeter',
        'house',
        'segments',
    ];

    private const ALLOWED_PILE_FOUNDATION_MODES = [
        'perimeter',
        'house',
        'segments',
    ];

    private const ALLOWED_BRICK_FORMATS = [
        'single_nf',
        'one_and_half_nf',
        'double_nf',
        'euro_nf',
        'custom',
    ];

    private const ALLOWED_BRICK_WALL_THICKNESS = [
        'half_brick',
        'one_brick',
        'one_and_half_bricks',
        'two_bricks',
        'two_and_half_bricks',
    ];

    private const ALLOWED_MIXTURE_TYPES_FOUNDATION = [
        'ready',
        'self_mix',
    ];

    private const ALLOWED_MIXTURE_TYPES_SCREED = [
        'ready',
        'dry_ready',
        'self_mix',
    ];

    private const ALLOWED_PURCHASE_UNITS = [
        'bag',
        'tonne',
    ];

    private const ALLOWED_TILE_TARGETS = [
        'floor',
        'wall',
    ];

    private const ALLOWED_TILE_PATTERNS = [
        'direct',
        'offset',
        'diagonal',
    ];

    private const ALLOWED_DRYWALL_TARGETS = [
        'wall',
        'ceiling',
        'partition',
    ];

    private const ALLOWED_DRYWALL_PROFILE_WIDTHS = [50, 75, 100];
    private const ALLOWED_DRYWALL_FRAME_STEPS = [400, 600];
    private const ALLOWED_DRYWALL_LAYERS = [1, 2];

    /** @var array<int> */
    private const ALLOWED_REBAR_LAYERS = [1, 2];

    public function validate(
        mixed $calculator,
        mixed $mode,
        mixed $area,
        mixed $thickness,
        mixed $subType,
        mixed $tileLengthCm,
        mixed $tileWidthCm,
        mixed $length,
        mixed $width,
        mixed $height,
        mixed $includeReinforcement,
        mixed $includeFormwork,
        mixed $rebarDiameterMm,
        mixed $rebarStepMm,
        mixed $rebarLayers,
        mixed $rebarReservePercent,
        mixed $formworkHeightM,
        mixed $formworkReservePercent,
        mixed $totalLengthM,
        mixed $widthM,
        mixed $heightM,
        mixed $houseLengthM,
        mixed $houseWidthM,
        mixed $segments,
        mixed $longitudinalBarsCount,
        mixed $longitudinalDiameterMm,
        mixed $longitudinalReservePercent,
        mixed $transverseDiameterMm,
        mixed $transverseStepMm,
        mixed $transverseReservePercent,
        mixed $pileType,
        mixed $includePiles,
        mixed $pilesCount,
        mixed $pileShaftDiameterM,
        mixed $pileShaftHeightM,
        mixed $includePileBase,
        mixed $pileBaseDiameterM,
        mixed $pileBaseHeightM,
        mixed $includeGrillage,
        mixed $includePileReinforcement,
        mixed $pileReinforcementBarsCount,
        mixed $pileReinforcementDiameterMm,
        mixed $pileReinforcementReservePercent,
        mixed $mixture,
        mixed $useUnifiedConcreteMixtureSettings,
        mixed $pileMixture,
        mixed $grillageMixture,
        mixed $brickFormat,
        mixed $brickLengthMm,
        mixed $brickWidthMm,
        mixed $brickHeightMm,
        mixed $jointThicknessMm,
        mixed $wallThicknessType,
        mixed $wallLengthM,
        mixed $wallHeightM,
        mixed $reservePercent,
        mixed $includeOpenings,
        mixed $windows,
        mixed $doors,
        mixed $includeGables,
        mixed $gables,
        mixed $includeMasonryMesh,
        mixed $masonryMeshFrequencyRows,
        mixed $useCustomMortarProportions,
        mixed $cementShare,
        mixed $sandShare,
        mixed $cementPurchaseUnit,
        mixed $cementUnitWeightKg,
        mixed $cementUnitPrice,
        mixed $sandPurchaseUnit,
        mixed $sandUnitWeightKg,
        mixed $sandUnitPrice,
        mixed $cementBagWeightKg,
        mixed $brickWeightKg,
        mixed $brickPricePerUnit,
        mixed $cementBagPrice,
        mixed $sandPricePerTonne,
        mixed $tileTarget,
        mixed $tileLengthMm,
        mixed $tileWidthMm,
        mixed $tileThicknessMm,
        mixed $tileJointMm,
        mixed $tileLayingPattern,
        mixed $tileOffsetPercent,
        mixed $tileIncludeOpenings,
        mixed $tileOpenings,
        mixed $tileIncludeCutouts,
        mixed $tileCutouts,
        mixed $tileIncludeAdhesive,
        mixed $tileAdhesiveConsumptionKgPerM2,
        mixed $tileAdhesiveLayerMm,
        mixed $tileAdhesiveBagWeightKg,
        mixed $tileAdhesiveBagPrice,
        mixed $tileIncludeGrout,
        mixed $tileGroutDensityKgPerM3,
        mixed $tileGroutPackWeightKg,
        mixed $tileGroutPackPrice,
        mixed $tilePricePerM2,
        mixed $drywallTarget,
        mixed $drywallSheetLengthMm,
        mixed $drywallSheetWidthMm,
        mixed $drywallSheetThicknessMm,
        mixed $drywallLayers,
        mixed $drywallFrameStepMm,
        mixed $drywallProfileWidthMm,
        mixed $drywallFastenerReservePercent,
        mixed $drywallIncludeEndCladding,
        mixed $drywallIncludeFinishing,
        mixed $drywallIncludeCosts,
        mixed $drywallSheetPrice,
        mixed $drywallProfilePricePerLm,
        mixed $drywallFastenerPricePer100,
        mixed $drywallPrimerPricePerKg,
        mixed $drywallJointPuttyPricePerKg,
        mixed $drywallFinishPuttyPricePerKg,
        mixed $drywallTapePricePerLm
    ): array
    {
        $errors = [];

        if (!$this->isNonEmptyString($calculator)) {
            $errors['calculator'][] = 'The calculator field is required and must be a string.';
        } elseif (!in_array($calculator, self::ALLOWED_CALCULATORS, true)) {
            $errors['calculator'][] = 'The calculator field must be one of: brick, screed, drywall, tile, slab_foundation, strip_foundation, pile_foundation.';
        }

        if (!$this->isNonEmptyString($mode)) {
            $errors['mode'][] = 'The mode field is required and must be a string.';
        } elseif ($calculator === EstimateService::CALCULATOR_BRICK) {
            if (!in_array($mode, self::ALLOWED_BRICK_MODES, true)) {
                $errors['mode'][] = 'The mode field for brick must be one of: dimensions, area.';
            }
        } elseif ($calculator === EstimateService::CALCULATOR_TILE) {
            if (!in_array($mode, self::ALLOWED_TILE_MODES, true)) {
                $errors['mode'][] = 'The mode field for tile must be one of: dimensions, area.';
            }
        } elseif ($calculator === EstimateService::CALCULATOR_DRYWALL) {
            if (!in_array($mode, self::ALLOWED_DRYWALL_MODES, true)) {
                $errors['mode'][] = 'The mode field for drywall must be one of: dimensions, area.';
            }
        } elseif ($calculator === EstimateService::CALCULATOR_SLAB_FOUNDATION || $calculator === EstimateService::CALCULATOR_SCREED) {
            if (!in_array($mode, self::ALLOWED_SLAB_FOUNDATION_MODES, true)) {
                $errors['mode'][] = sprintf('The mode field for %s must be one of: dimensions, area.', (string) $calculator);
            }
        } elseif ($calculator === EstimateService::CALCULATOR_STRIP_FOUNDATION) {
            if (!in_array($mode, self::ALLOWED_STRIP_FOUNDATION_MODES, true)) {
                $errors['mode'][] = 'The mode field for strip_foundation must be one of: perimeter, house, segments.';
            }
        } elseif ($calculator === EstimateService::CALCULATOR_PILE_FOUNDATION) {
            if (!in_array($mode, self::ALLOWED_PILE_FOUNDATION_MODES, true)) {
                $errors['mode'][] = 'The mode field for pile_foundation must be one of: perimeter, house, segments.';
            }
        }

        if ($calculator === EstimateService::CALCULATOR_BRICK) {
            $this->validateBrickPayload(
                errors: $errors,
                mode: $mode,
                area: $area,
                brickFormat: $brickFormat,
                brickLengthMm: $brickLengthMm,
                brickWidthMm: $brickWidthMm,
                brickHeightMm: $brickHeightMm,
                jointThicknessMm: $jointThicknessMm,
                wallThicknessType: $wallThicknessType,
                wallLengthM: $wallLengthM,
                wallHeightM: $wallHeightM,
                reservePercent: $reservePercent,
                includeOpenings: $includeOpenings,
                windows: $windows,
                doors: $doors,
                includeGables: $includeGables,
                gables: $gables,
                includeMasonryMesh: $includeMasonryMesh,
                masonryMeshFrequencyRows: $masonryMeshFrequencyRows,
                useCustomMortarProportions: $useCustomMortarProportions,
                cementShare: $cementShare,
                sandShare: $sandShare,
                cementPurchaseUnit: $cementPurchaseUnit,
                cementUnitWeightKg: $cementUnitWeightKg,
                cementUnitPrice: $cementUnitPrice,
                sandPurchaseUnit: $sandPurchaseUnit,
                sandUnitWeightKg: $sandUnitWeightKg,
                sandUnitPrice: $sandUnitPrice,
                cementBagWeightKg: $cementBagWeightKg,
                brickWeightKg: $brickWeightKg,
                brickPricePerUnit: $brickPricePerUnit,
                cementBagPrice: $cementBagPrice,
                sandPricePerTonne: $sandPricePerTonne
            );
        }

        if ($calculator === EstimateService::CALCULATOR_DRYWALL) {
            $this->validateDrywallPayload(
                errors: $errors,
                mode: $mode,
                area: $area,
                length: $length,
                width: $width,
                height: $height,
                drywallTarget: $drywallTarget,
                drywallSheetLengthMm: $drywallSheetLengthMm,
                drywallSheetWidthMm: $drywallSheetWidthMm,
                drywallSheetThicknessMm: $drywallSheetThicknessMm,
                drywallLayers: $drywallLayers,
                drywallFrameStepMm: $drywallFrameStepMm,
                drywallProfileWidthMm: $drywallProfileWidthMm,
                reservePercent: $reservePercent,
                includeOpenings: $includeOpenings,
                windows: $windows,
                doors: $doors,
                drywallFastenerReservePercent: $drywallFastenerReservePercent,
                drywallIncludeEndCladding: $drywallIncludeEndCladding,
                drywallIncludeFinishing: $drywallIncludeFinishing,
                drywallIncludeCosts: $drywallIncludeCosts,
                drywallSheetPrice: $drywallSheetPrice,
                drywallProfilePricePerLm: $drywallProfilePricePerLm,
                drywallFastenerPricePer100: $drywallFastenerPricePer100,
                drywallPrimerPricePerKg: $drywallPrimerPricePerKg,
                drywallJointPuttyPricePerKg: $drywallJointPuttyPricePerKg,
                drywallFinishPuttyPricePerKg: $drywallFinishPuttyPricePerKg,
                drywallTapePricePerLm: $drywallTapePricePerLm
            );
        }

        if ($calculator === EstimateService::CALCULATOR_TILE) {
            $this->validateTilePayload(
                errors: $errors,
                mode: $mode,
                area: $area,
                length: $length,
                width: $width,
                height: $height,
                tileTarget: $tileTarget,
                tileLengthMm: $tileLengthMm,
                tileWidthMm: $tileWidthMm,
                tileThicknessMm: $tileThicknessMm,
                tileJointMm: $tileJointMm,
                tileLayingPattern: $tileLayingPattern,
                tileOffsetPercent: $tileOffsetPercent,
                reservePercent: $reservePercent,
                tileIncludeOpenings: $tileIncludeOpenings,
                tileOpenings: $tileOpenings,
                tileIncludeCutouts: $tileIncludeCutouts,
                tileCutouts: $tileCutouts,
                tileIncludeAdhesive: $tileIncludeAdhesive,
                tileAdhesiveConsumptionKgPerM2: $tileAdhesiveConsumptionKgPerM2,
                tileAdhesiveLayerMm: $tileAdhesiveLayerMm,
                tileAdhesiveBagWeightKg: $tileAdhesiveBagWeightKg,
                tileAdhesiveBagPrice: $tileAdhesiveBagPrice,
                tileIncludeGrout: $tileIncludeGrout,
                tileGroutDensityKgPerM3: $tileGroutDensityKgPerM3,
                tileGroutPackWeightKg: $tileGroutPackWeightKg,
                tileGroutPackPrice: $tileGroutPackPrice,
                tilePricePerM2: $tilePricePerM2
            );
        }

        if ($calculator === EstimateService::CALCULATOR_SLAB_FOUNDATION) {
            if ($mode === 'dimensions') {
                $this->validatePositiveNumericField($errors, 'length', $length, 'The length field is required and must be numeric for slab_foundation in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'width', $width, 'The width field is required and must be numeric for slab_foundation in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for slab_foundation.');
            }

            if ($mode === 'area') {
                $this->validatePositiveNumericField($errors, 'area', $area, 'The area field is required and must be numeric for slab_foundation in area mode.');
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for slab_foundation.');
            }

            $this->validateStrictBooleanField($errors, 'includeReinforcement', $includeReinforcement);
            $this->validateStrictBooleanField($errors, 'includeFormwork', $includeFormwork);

            $includeReinforcementEnabled = is_bool($includeReinforcement) && $includeReinforcement;
            $includeFormworkEnabled = is_bool($includeFormwork) && $includeFormwork;
            $needsPerimeter = $mode === 'area' && ($includeReinforcementEnabled || $includeFormworkEnabled);

            if ($needsPerimeter) {
                if (!$this->isNumericValue($length) || (float) $length <= 0) {
                    $errors['length'][] = 'The length field is required and must be greater than 0 when includeReinforcement/includeFormwork is true and mode is area.';
                }

                if (!$this->isNumericValue($width) || (float) $width <= 0) {
                    $errors['width'][] = 'The width field is required and must be greater than 0 when includeReinforcement/includeFormwork is true and mode is area.';
                }
            }

            if ($includeReinforcementEnabled) {
                $this->validateOptionalPositiveNumericField($errors, 'rebarDiameterMm', $rebarDiameterMm);
                $this->validateOptionalPositiveNumericField($errors, 'rebarStepMm', $rebarStepMm);
                $this->validateOptionalPositiveNumericField($errors, 'rebarReservePercent', $rebarReservePercent);

                if ($rebarLayers !== null && !$this->isValidRebarLayers($rebarLayers)) {
                    $errors['rebarLayers'][] = 'The rebarLayers field must be one of: 1, 2.';
                }
            }

            if ($includeFormworkEnabled) {
                $this->validateOptionalPositiveNumericField($errors, 'formworkHeightM', $formworkHeightM);
                $this->validateOptionalPositiveNumericField($errors, 'formworkReservePercent', $formworkReservePercent);
            }

            $this->validateConcreteMixtureConfig($errors, 'mixture', $mixture, self::ALLOWED_MIXTURE_TYPES_FOUNDATION, true);
        }

        if ($calculator === EstimateService::CALCULATOR_SCREED) {
            if ($mode === 'dimensions') {
                $this->validatePositiveNumericField($errors, 'length', $length, 'The length field is required and must be numeric for screed in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'width', $width, 'The width field is required and must be numeric for screed in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for screed.');
            }

            if ($mode === 'area') {
                $this->validatePositiveNumericField($errors, 'area', $area, 'The area field is required and must be numeric for screed in area mode.');
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for screed.');
            }

            $this->validateStrictBooleanField($errors, 'includeReinforcement', $includeReinforcement);
            $includeReinforcementEnabled = is_bool($includeReinforcement) && $includeReinforcement;
            $needsGeometry = $mode === 'area' && $includeReinforcementEnabled;

            if ($needsGeometry) {
                if (!$this->isNumericValue($length) || (float) $length <= 0) {
                    $errors['length'][] = 'The length field is required and must be greater than 0 when includeReinforcement is true and mode is area.';
                }

                if (!$this->isNumericValue($width) || (float) $width <= 0) {
                    $errors['width'][] = 'The width field is required and must be greater than 0 when includeReinforcement is true and mode is area.';
                }
            }

            if ($includeReinforcementEnabled) {
                $this->validateOptionalPositiveNumericField($errors, 'rebarDiameterMm', $rebarDiameterMm);
                $this->validateOptionalPositiveNumericField($errors, 'rebarStepMm', $rebarStepMm);
                $this->validateOptionalPositiveNumericField($errors, 'rebarReservePercent', $rebarReservePercent);

                if ($rebarLayers !== null && !$this->isValidRebarLayers($rebarLayers)) {
                    $errors['rebarLayers'][] = 'The rebarLayers field must be one of: 1, 2.';
                }
            }

            $this->validateConcreteMixtureConfig($errors, 'mixture', $mixture, self::ALLOWED_MIXTURE_TYPES_SCREED, false);
        }

        if ($calculator === EstimateService::CALCULATOR_STRIP_FOUNDATION) {
            $this->validateStripFoundationPayload(
                errors: $errors,
                mode: $mode,
                totalLengthM: $totalLengthM,
                widthM: $widthM,
                heightM: $heightM,
                houseLengthM: $houseLengthM,
                houseWidthM: $houseWidthM,
                segments: $segments,
                includeReinforcement: $includeReinforcement,
                includeFormwork: $includeFormwork,
                longitudinalBarsCount: $longitudinalBarsCount,
                longitudinalDiameterMm: $longitudinalDiameterMm,
                longitudinalReservePercent: $longitudinalReservePercent,
                transverseDiameterMm: $transverseDiameterMm,
                transverseStepMm: $transverseStepMm,
                transverseReservePercent: $transverseReservePercent,
                formworkHeightM: $formworkHeightM,
                formworkReservePercent: $formworkReservePercent,
                contextLabel: 'strip_foundation'
            );

            $this->validateConcreteMixtureConfig($errors, 'mixture', $mixture, self::ALLOWED_MIXTURE_TYPES_FOUNDATION, true);
        }

        if ($calculator === EstimateService::CALCULATOR_PILE_FOUNDATION) {
            $this->validateStrictBooleanField($errors, 'includePiles', $includePiles);
            $this->validateStrictBooleanField($errors, 'includePileBase', $includePileBase);
            $this->validateStrictBooleanField($errors, 'includeGrillage', $includeGrillage);
            $this->validateStrictBooleanField($errors, 'includePileReinforcement', $includePileReinforcement);
            $this->validateStrictBooleanField($errors, 'useUnifiedConcreteMixtureSettings', $useUnifiedConcreteMixtureSettings);

            $includePilesEnabled = !is_bool($includePiles) || $includePiles;
            if ($includePilesEnabled) {
                $pileTypeNormalized = $this->isNonEmptyString($pileType) ? (string) $pileType : 'bored';
                if (!in_array($pileTypeNormalized, ['bored', 'screw', 'driven'], true)) {
                    $errors['pileType'][] = 'The pileType field must be one of: bored, screw, driven.';
                }

                $this->validatePositiveNumericField($errors, 'pilesCount', $pilesCount, 'The pilesCount field is required and must be numeric when includePiles is true.');
                if ($this->isNumericValue($pilesCount) && !$this->isIntegerNumber($pilesCount)) {
                    $errors['pilesCount'][] = 'The pilesCount field must be an integer.';
                }

                if ($pileTypeNormalized === 'bored') {
                    $this->validatePositiveNumericField($errors, 'pileShaftDiameterM', $pileShaftDiameterM, 'The pileShaftDiameterM field is required and must be numeric for bored piles.');
                    $this->validatePositiveNumericField($errors, 'pileShaftHeightM', $pileShaftHeightM, 'The pileShaftHeightM field is required and must be numeric for bored piles.');

                    $includePileBaseEnabled = !is_bool($includePileBase) || $includePileBase;
                    if ($includePileBaseEnabled) {
                        $this->validatePositiveNumericField($errors, 'pileBaseDiameterM', $pileBaseDiameterM, 'The pileBaseDiameterM field is required and must be numeric when includePileBase is true.');
                        $this->validatePositiveNumericField($errors, 'pileBaseHeightM', $pileBaseHeightM, 'The pileBaseHeightM field is required and must be numeric when includePileBase is true.');
                    }

                    $pileReinforcementEnabled = is_bool($includePileReinforcement) && $includePileReinforcement;
                    if ($pileReinforcementEnabled) {
                        $this->validatePositiveNumericField($errors, 'pileReinforcementBarsCount', $pileReinforcementBarsCount, 'The pileReinforcementBarsCount field is required and must be numeric when includePileReinforcement is true.');
                        if ($this->isNumericValue($pileReinforcementBarsCount) && !$this->isIntegerNumber($pileReinforcementBarsCount)) {
                            $errors['pileReinforcementBarsCount'][] = 'The pileReinforcementBarsCount field must be an integer.';
                        }

                        $this->validatePositiveNumericField($errors, 'pileReinforcementDiameterMm', $pileReinforcementDiameterMm, 'The pileReinforcementDiameterMm field is required and must be numeric when includePileReinforcement is true.');
                        $this->validatePositiveNumericField($errors, 'pileReinforcementReservePercent', $pileReinforcementReservePercent, 'The pileReinforcementReservePercent field is required and must be numeric when includePileReinforcement is true.');
                    }
                }
            }

            $includeGrillageEnabled = !is_bool($includeGrillage) || $includeGrillage;
            if ($includeGrillageEnabled) {
                $this->validateStripFoundationPayload(
                    errors: $errors,
                    mode: $mode,
                    totalLengthM: $totalLengthM,
                    widthM: $widthM,
                    heightM: $heightM,
                    houseLengthM: $houseLengthM,
                    houseWidthM: $houseWidthM,
                    segments: $segments,
                    includeReinforcement: $includeReinforcement,
                    includeFormwork: $includeFormwork,
                    longitudinalBarsCount: $longitudinalBarsCount,
                    longitudinalDiameterMm: $longitudinalDiameterMm,
                    longitudinalReservePercent: $longitudinalReservePercent,
                    transverseDiameterMm: $transverseDiameterMm,
                    transverseStepMm: $transverseStepMm,
                    transverseReservePercent: $transverseReservePercent,
                    formworkHeightM: $formworkHeightM,
                    formworkReservePercent: $formworkReservePercent,
                    contextLabel: 'pile_foundation'
                );
            }

            $useUnifiedMixture = !is_bool($useUnifiedConcreteMixtureSettings) || $useUnifiedConcreteMixtureSettings;
            $pileHasConcrete = $includePilesEnabled && $this->isBoredPileType($pileType);
            $grillageHasConcrete = $includeGrillageEnabled;
            $requiresAnyMixture = $pileHasConcrete || $grillageHasConcrete;

            if ($useUnifiedMixture && $requiresAnyMixture) {
                $this->validateConcreteMixtureConfig($errors, 'mixture', $mixture, self::ALLOWED_MIXTURE_TYPES_FOUNDATION, true);
            } else {
                if ($pileHasConcrete) {
                    $this->validateConcreteMixtureConfig($errors, 'pileMixture', $pileMixture, self::ALLOWED_MIXTURE_TYPES_FOUNDATION, true);
                }

                if ($grillageHasConcrete) {
                    $this->validateConcreteMixtureConfig($errors, 'grillageMixture', $grillageMixture, self::ALLOWED_MIXTURE_TYPES_FOUNDATION, true);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validatePositiveNumericField(array &$errors, string $field, mixed $value, string $requiredMessage): void
    {
        if (!$this->isNumericValue($value)) {
            $errors[$field][] = $requiredMessage;

            return;
        }

        if ((float) $value <= 0) {
            $errors[$field][] = sprintf('The %s field must be greater than 0.', $field);
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateOptionalPositiveNumericField(array &$errors, string $field, mixed $value): void
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return;
        }

        if (!$this->isNumericValue($value)) {
            $errors[$field][] = sprintf('The %s field must be numeric.', $field);
            return;
        }

        if ((float) $value <= 0) {
            $errors[$field][] = sprintf('The %s field must be greater than 0.', $field);
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateStrictBooleanField(array &$errors, string $field, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!is_bool($value)) {
            $errors[$field][] = sprintf('The %s field must be a boolean.', $field);
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateBrickPayload(
        array &$errors,
        mixed $mode,
        mixed $area,
        mixed $brickFormat,
        mixed $brickLengthMm,
        mixed $brickWidthMm,
        mixed $brickHeightMm,
        mixed $jointThicknessMm,
        mixed $wallThicknessType,
        mixed $wallLengthM,
        mixed $wallHeightM,
        mixed $reservePercent,
        mixed $includeOpenings,
        mixed $windows,
        mixed $doors,
        mixed $includeGables,
        mixed $gables,
        mixed $includeMasonryMesh,
        mixed $masonryMeshFrequencyRows,
        mixed $useCustomMortarProportions,
        mixed $cementShare,
        mixed $sandShare,
        mixed $cementPurchaseUnit,
        mixed $cementUnitWeightKg,
        mixed $cementUnitPrice,
        mixed $sandPurchaseUnit,
        mixed $sandUnitWeightKg,
        mixed $sandUnitPrice,
        mixed $cementBagWeightKg,
        mixed $brickWeightKg,
        mixed $brickPricePerUnit,
        mixed $cementBagPrice,
        mixed $sandPricePerTonne
    ): void {
        if ($mode === EstimateInput::MODE_DIMENSIONS) {
            $this->validatePositiveNumericField($errors, 'wallLengthM', $wallLengthM, 'The wallLengthM field is required and must be numeric for brick in dimensions mode.');
        }

        if ($mode === EstimateInput::MODE_AREA) {
            $this->validatePositiveNumericField($errors, 'area', $area, 'The area field is required and must be numeric for brick in area mode.');
        }

        $this->validatePositiveNumericField($errors, 'wallHeightM', $wallHeightM, 'The wallHeightM field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'brickLengthMm', $brickLengthMm, 'The brickLengthMm field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'brickWidthMm', $brickWidthMm, 'The brickWidthMm field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'brickHeightMm', $brickHeightMm, 'The brickHeightMm field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'jointThicknessMm', $jointThicknessMm, 'The jointThicknessMm field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'reservePercent', $reservePercent, 'The reservePercent field is required and must be numeric for brick.');

        if (!$this->isNonEmptyString($brickFormat)) {
            $errors['brickFormat'][] = 'The brickFormat field is required for brick.';
        } elseif (!in_array((string) $brickFormat, self::ALLOWED_BRICK_FORMATS, true)) {
            $errors['brickFormat'][] = 'The brickFormat field must be one of: single_nf, one_and_half_nf, double_nf, euro_nf, custom.';
        }

        if (!$this->isNonEmptyString($wallThicknessType)) {
            $errors['wallThicknessType'][] = 'The wallThicknessType field is required for brick.';
        } elseif (!in_array((string) $wallThicknessType, self::ALLOWED_BRICK_WALL_THICKNESS, true)) {
            $errors['wallThicknessType'][] = 'The wallThicknessType field must be one of: half_brick, one_brick, one_and_half_bricks, two_bricks, two_and_half_bricks.';
        }

        $this->validateStrictBooleanField($errors, 'includeOpenings', $includeOpenings);
        $this->validateStrictBooleanField($errors, 'includeGables', $includeGables);
        $this->validateStrictBooleanField($errors, 'includeMasonryMesh', $includeMasonryMesh);
        $this->validateOptionalPositiveNumericField($errors, 'brickWeightKg', $brickWeightKg);
        $this->validateOptionalPositiveNumericField($errors, 'brickPricePerUnit', $brickPricePerUnit);
        $this->validateOptionalPositiveNumericField($errors, 'cementBagWeightKg', $cementBagWeightKg);
        $this->validateOptionalPositiveNumericField($errors, 'cementBagPrice', $cementBagPrice);
        $this->validateOptionalPositiveNumericField($errors, 'sandPricePerTonne', $sandPricePerTonne);
        $this->validatePositiveNumericField($errors, 'cementShare', $cementShare, 'The cementShare field is required and must be numeric for brick.');
        $this->validatePositiveNumericField($errors, 'sandShare', $sandShare, 'The sandShare field is required and must be numeric for brick.');
        $this->validatePurchaseUnitField($errors, 'cementPurchaseUnit', $cementPurchaseUnit);
        $this->validatePositiveNumericField($errors, 'cementUnitWeightKg', $cementUnitWeightKg, 'The cementUnitWeightKg field is required and must be numeric for brick.');
        $this->validateOptionalPositiveNumericField($errors, 'cementUnitPrice', $cementUnitPrice);
        $this->validatePurchaseUnitField($errors, 'sandPurchaseUnit', $sandPurchaseUnit);
        $this->validatePositiveNumericField($errors, 'sandUnitWeightKg', $sandUnitWeightKg, 'The sandUnitWeightKg field is required and must be numeric for brick.');
        $this->validateOptionalPositiveNumericField($errors, 'sandUnitPrice', $sandUnitPrice);

        if (is_bool($includeOpenings) && $includeOpenings) {
            $this->validateBrickElements($errors, 'windows', $windows, 'window');
            $this->validateBrickElements($errors, 'doors', $doors, 'door');
        }

        if (is_bool($includeGables) && $includeGables) {
            $this->validateBrickElements($errors, 'gables', $gables, 'gable');
        }

        if (is_bool($includeMasonryMesh) && $includeMasonryMesh) {
            $this->validatePositiveNumericField($errors, 'masonryMeshFrequencyRows', $masonryMeshFrequencyRows, 'The masonryMeshFrequencyRows field is required and must be numeric when includeMasonryMesh is true.');
            if ($this->isNumericValue($masonryMeshFrequencyRows) && !$this->isIntegerNumber($masonryMeshFrequencyRows)) {
                $errors['masonryMeshFrequencyRows'][] = 'The masonryMeshFrequencyRows field must be an integer.';
            }
        }

    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateBrickElements(array &$errors, string $field, mixed $items, string $type): void
    {
        if ($items === null) {
            return;
        }

        if (!is_array($items)) {
            $errors[$field][] = sprintf('The %s field must be an array.', $field);
            return;
        }

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $errors[$field][] = sprintf('The %s[%d] value must be an object.', $field, $index);
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.widthM', $field, $index),
                $item['widthM'] ?? null,
                sprintf('The widthM field is required in %s[%d].', $field, $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.heightM', $field, $index),
                $item['heightM'] ?? null,
                sprintf('The heightM field is required in %s[%d].', $field, $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.count', $field, $index),
                $item['count'] ?? null,
                sprintf('The count field is required in %s[%d].', $field, $index)
            );

            if (isset($item['count']) && $this->isNumericValue($item['count']) && !$this->isIntegerNumber($item['count'])) {
                $errors[sprintf('%s.%d.count', $field, $index)][] = 'The count field must be an integer.';
            }

        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateDrywallPayload(
        array &$errors,
        mixed $mode,
        mixed $area,
        mixed $length,
        mixed $width,
        mixed $height,
        mixed $drywallTarget,
        mixed $drywallSheetLengthMm,
        mixed $drywallSheetWidthMm,
        mixed $drywallSheetThicknessMm,
        mixed $drywallLayers,
        mixed $drywallFrameStepMm,
        mixed $drywallProfileWidthMm,
        mixed $reservePercent,
        mixed $includeOpenings,
        mixed $windows,
        mixed $doors,
        mixed $drywallFastenerReservePercent,
        mixed $drywallIncludeEndCladding,
        mixed $drywallIncludeFinishing,
        mixed $drywallIncludeCosts,
        mixed $drywallSheetPrice,
        mixed $drywallProfilePricePerLm,
        mixed $drywallFastenerPricePer100,
        mixed $drywallPrimerPricePerKg,
        mixed $drywallJointPuttyPricePerKg,
        mixed $drywallFinishPuttyPricePerKg,
        mixed $drywallTapePricePerLm
    ): void {
        if (!$this->isNonEmptyString($drywallTarget) || !in_array((string) $drywallTarget, self::ALLOWED_DRYWALL_TARGETS, true)) {
            $errors['drywallTarget'][] = 'The drywallTarget field must be one of: wall, ceiling, partition.';
        }

        if ($mode === EstimateInput::MODE_DIMENSIONS) {
            if ($drywallTarget === 'ceiling') {
                $this->validatePositiveNumericField($errors, 'length', $length, 'The length field is required and must be numeric for drywall ceilings in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'width', $width, 'The width field is required and must be numeric for drywall ceilings in dimensions mode.');
            } else {
                $this->validatePositiveNumericField($errors, 'length', $length, 'The length field is required and must be numeric for drywall in dimensions mode.');
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for drywall in dimensions mode.');
            }
        }

        if ($mode === EstimateInput::MODE_AREA) {
            $this->validatePositiveNumericField($errors, 'area', $area, 'The area field is required and must be numeric for drywall in area mode.');
        }

        $this->validatePositiveNumericField($errors, 'drywallSheetLengthMm', $drywallSheetLengthMm, 'The drywallSheetLengthMm field is required and must be numeric for drywall.');
        $this->validatePositiveNumericField($errors, 'drywallSheetWidthMm', $drywallSheetWidthMm, 'The drywallSheetWidthMm field is required and must be numeric for drywall.');
        $this->validatePositiveNumericField($errors, 'drywallSheetThicknessMm', $drywallSheetThicknessMm, 'The drywallSheetThicknessMm field is required and must be numeric for drywall.');
        $this->validatePositiveNumericField($errors, 'reservePercent', $reservePercent, 'The reservePercent field is required and must be numeric for drywall.');
        $this->validatePositiveNumericField($errors, 'drywallFastenerReservePercent', $drywallFastenerReservePercent, 'The drywallFastenerReservePercent field is required and must be numeric for drywall.');
        $this->validateStrictBooleanField($errors, 'includeOpenings', $includeOpenings);
        $this->validateStrictBooleanField($errors, 'drywallIncludeEndCladding', $drywallIncludeEndCladding);
        $this->validateStrictBooleanField($errors, 'drywallIncludeFinishing', $drywallIncludeFinishing);
        $this->validateStrictBooleanField($errors, 'drywallIncludeCosts', $drywallIncludeCosts);
        $this->validateOptionalPositiveNumericField($errors, 'drywallSheetPrice', $drywallSheetPrice);
        $this->validateOptionalPositiveNumericField($errors, 'drywallProfilePricePerLm', $drywallProfilePricePerLm);
        $this->validateOptionalPositiveNumericField($errors, 'drywallFastenerPricePer100', $drywallFastenerPricePer100);
        $this->validateOptionalPositiveNumericField($errors, 'drywallPrimerPricePerKg', $drywallPrimerPricePerKg);
        $this->validateOptionalPositiveNumericField($errors, 'drywallJointPuttyPricePerKg', $drywallJointPuttyPricePerKg);
        $this->validateOptionalPositiveNumericField($errors, 'drywallFinishPuttyPricePerKg', $drywallFinishPuttyPricePerKg);
        $this->validateOptionalPositiveNumericField($errors, 'drywallTapePricePerLm', $drywallTapePricePerLm);

        if ($this->isNumericValue($drywallLayers) && !in_array((int) $drywallLayers, self::ALLOWED_DRYWALL_LAYERS, true)) {
            $errors['drywallLayers'][] = 'The drywallLayers field must be one of: 1, 2.';
        } elseif (!$this->isNumericValue($drywallLayers)) {
            $errors['drywallLayers'][] = 'The drywallLayers field is required and must be numeric.';
        }

        if ($this->isNumericValue($drywallFrameStepMm) && !in_array((int) $drywallFrameStepMm, self::ALLOWED_DRYWALL_FRAME_STEPS, true)) {
            $errors['drywallFrameStepMm'][] = 'The drywallFrameStepMm field must be one of: 400, 600.';
        } elseif (!$this->isNumericValue($drywallFrameStepMm)) {
            $errors['drywallFrameStepMm'][] = 'The drywallFrameStepMm field is required and must be numeric.';
        }

        if ($drywallTarget === 'partition') {
            if ($this->isNumericValue($drywallProfileWidthMm) && !in_array((int) $drywallProfileWidthMm, self::ALLOWED_DRYWALL_PROFILE_WIDTHS, true)) {
                $errors['drywallProfileWidthMm'][] = 'The drywallProfileWidthMm field must be one of: 50, 75, 100.';
            } elseif (!$this->isNumericValue($drywallProfileWidthMm)) {
                $errors['drywallProfileWidthMm'][] = 'The drywallProfileWidthMm field is required for partitions.';
            }
        }

        if ($drywallTarget !== 'ceiling' && is_bool($includeOpenings) && $includeOpenings) {
            $this->validateBrickElements($errors, 'windows', $windows, 'window');
            $this->validateBrickElements($errors, 'doors', $doors, 'door');
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateTilePayload(
        array &$errors,
        mixed $mode,
        mixed $area,
        mixed $length,
        mixed $width,
        mixed $height,
        mixed $tileTarget,
        mixed $tileLengthMm,
        mixed $tileWidthMm,
        mixed $tileThicknessMm,
        mixed $tileJointMm,
        mixed $tileLayingPattern,
        mixed $tileOffsetPercent,
        mixed $reservePercent,
        mixed $tileIncludeOpenings,
        mixed $tileOpenings,
        mixed $tileIncludeCutouts,
        mixed $tileCutouts,
        mixed $tileIncludeAdhesive,
        mixed $tileAdhesiveConsumptionKgPerM2,
        mixed $tileAdhesiveLayerMm,
        mixed $tileAdhesiveBagWeightKg,
        mixed $tileAdhesiveBagPrice,
        mixed $tileIncludeGrout,
        mixed $tileGroutDensityKgPerM3,
        mixed $tileGroutPackWeightKg,
        mixed $tileGroutPackPrice,
        mixed $tilePricePerM2
    ): void {
        if (!$this->isNonEmptyString($tileTarget) || !in_array((string) $tileTarget, self::ALLOWED_TILE_TARGETS, true)) {
            $errors['tileTarget'][] = 'The tileTarget field must be one of: floor, wall.';
        }

        if (!$this->isNonEmptyString($tileLayingPattern) || !in_array((string) $tileLayingPattern, self::ALLOWED_TILE_PATTERNS, true)) {
            $errors['tileLayingPattern'][] = 'The tileLayingPattern field must be one of: direct, offset, diagonal.';
        }

        if ($mode === EstimateInput::MODE_DIMENSIONS) {
            $this->validatePositiveNumericField($errors, 'length', $length, 'The length field is required and must be numeric for tile in dimensions mode.');
            $this->validatePositiveNumericField($errors, 'width', $width, 'The width field is required and must be numeric for tile in dimensions mode.');
            if ($tileTarget === 'wall') {
                $this->validatePositiveNumericField($errors, 'height', $height, 'The height field is required and must be numeric for tile walls in dimensions mode.');
            }
        }

        if ($mode === EstimateInput::MODE_AREA) {
            $this->validatePositiveNumericField($errors, 'area', $area, 'The area field is required and must be numeric for tile in area mode.');
        }

        $this->validatePositiveNumericField($errors, 'tileLengthMm', $tileLengthMm, 'The tileLengthMm field is required and must be numeric for tile.');
        $this->validatePositiveNumericField($errors, 'tileWidthMm', $tileWidthMm, 'The tileWidthMm field is required and must be numeric for tile.');
        $this->validateOptionalPositiveNumericField($errors, 'tileThicknessMm', $tileThicknessMm);
        $this->validateOptionalPositiveNumericField($errors, 'tileJointMm', $tileJointMm);
        $this->validateOptionalPositiveNumericField($errors, 'tileOffsetPercent', $tileOffsetPercent);
        $this->validateOptionalPositiveNumericField($errors, 'reservePercent', $reservePercent);
        $this->validateOptionalPositiveNumericField($errors, 'tilePricePerM2', $tilePricePerM2);
        $this->validateStrictBooleanField($errors, 'tileIncludeOpenings', $tileIncludeOpenings);
        $this->validateStrictBooleanField($errors, 'tileIncludeCutouts', $tileIncludeCutouts);
        $this->validateStrictBooleanField($errors, 'tileIncludeAdhesive', $tileIncludeAdhesive);
        $this->validateStrictBooleanField($errors, 'tileIncludeGrout', $tileIncludeGrout);

        if ($tileLayingPattern === 'offset') {
            $this->validateOptionalPositiveNumericField($errors, 'tileOffsetPercent', $tileOffsetPercent);
        }

        if ($tileTarget === 'wall' && is_bool($tileIncludeOpenings) && $tileIncludeOpenings) {
            $this->validateTileOpenings($errors, 'tileOpenings', $tileOpenings);
        }

        if (is_bool($tileIncludeCutouts) && $tileIncludeCutouts) {
            $this->validateTileCutouts($errors, 'tileCutouts', $tileCutouts);
        }

        if (is_bool($tileIncludeAdhesive) && $tileIncludeAdhesive) {
            $this->validateOptionalPositiveNumericField($errors, 'tileAdhesiveConsumptionKgPerM2', $tileAdhesiveConsumptionKgPerM2);
            $this->validateOptionalPositiveNumericField($errors, 'tileAdhesiveLayerMm', $tileAdhesiveLayerMm);
            $this->validateOptionalPositiveNumericField($errors, 'tileAdhesiveBagWeightKg', $tileAdhesiveBagWeightKg);
            $this->validateOptionalPositiveNumericField($errors, 'tileAdhesiveBagPrice', $tileAdhesiveBagPrice);
        }

        if (is_bool($tileIncludeGrout) && $tileIncludeGrout) {
            $this->validateOptionalPositiveNumericField($errors, 'tileGroutDensityKgPerM3', $tileGroutDensityKgPerM3);
            $this->validateOptionalPositiveNumericField($errors, 'tileGroutPackWeightKg', $tileGroutPackWeightKg);
            $this->validateOptionalPositiveNumericField($errors, 'tileGroutPackPrice', $tileGroutPackPrice);
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateTileOpenings(array &$errors, string $field, mixed $items): void
    {
        if (!is_array($items) || $items === []) {
            $errors[$field][] = sprintf('The %s field must contain at least one opening.', $field);
            return;
        }

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $errors[$field][] = sprintf('The %s[%d] value must be an object.', $field, $index);
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.widthM', $field, $index),
                $item['widthM'] ?? null,
                sprintf('The widthM field is required in %s[%d].', $field, $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.heightM', $field, $index),
                $item['heightM'] ?? null,
                sprintf('The heightM field is required in %s[%d].', $field, $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.count', $field, $index),
                $item['count'] ?? null,
                sprintf('The count field is required in %s[%d].', $field, $index)
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateTileCutouts(array &$errors, string $field, mixed $items): void
    {
        if (!is_array($items) || $items === []) {
            $errors[$field][] = sprintf('The %s field must contain at least one cutout.', $field);
            return;
        }

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $errors[$field][] = sprintf('The %s[%d] value must be an object.', $field, $index);
                continue;
            }

            $shape = $item['shape'] ?? null;
            if (!$this->isNonEmptyString($shape) || !in_array((string) $shape, ['circle', 'rect'], true)) {
                $errors[sprintf('%s.%d.shape', $field, $index)][] = 'The shape field must be one of: circle, rect.';
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.count', $field, $index),
                $item['count'] ?? null,
                sprintf('The count field is required in %s[%d].', $field, $index)
            );

            if ($shape === 'circle') {
                $this->validatePositiveNumericField(
                    $errors,
                    sprintf('%s.%d.diameterMm', $field, $index),
                    $item['diameterMm'] ?? null,
                    sprintf('The diameterMm field is required in %s[%d].', $field, $index)
                );
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.widthMm', $field, $index),
                $item['widthMm'] ?? null,
                sprintf('The widthMm field is required in %s[%d].', $field, $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('%s.%d.heightMm', $field, $index),
                $item['heightMm'] ?? null,
                sprintf('The heightMm field is required in %s[%d].', $field, $index)
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateStripFoundationPayload(
        array &$errors,
        mixed $mode,
        mixed $totalLengthM,
        mixed $widthM,
        mixed $heightM,
        mixed $houseLengthM,
        mixed $houseWidthM,
        mixed $segments,
        mixed $includeReinforcement,
        mixed $includeFormwork,
        mixed $longitudinalBarsCount,
        mixed $longitudinalDiameterMm,
        mixed $longitudinalReservePercent,
        mixed $transverseDiameterMm,
        mixed $transverseStepMm,
        mixed $transverseReservePercent,
        mixed $formworkHeightM,
        mixed $formworkReservePercent,
        string $contextLabel
    ): void {
        if ($mode === 'perimeter') {
            $this->validatePositiveNumericField($errors, 'totalLengthM', $totalLengthM, sprintf('The totalLengthM field is required and must be numeric for %s in perimeter mode.', $contextLabel));
            $this->validatePositiveNumericField($errors, 'widthM', $widthM, sprintf('The widthM field is required and must be numeric for %s in perimeter mode.', $contextLabel));
            $this->validatePositiveNumericField($errors, 'heightM', $heightM, sprintf('The heightM field is required and must be numeric for %s in perimeter mode.', $contextLabel));
        }

        if ($mode === 'house') {
            $this->validatePositiveNumericField($errors, 'houseLengthM', $houseLengthM, sprintf('The houseLengthM field is required and must be numeric for %s in house mode.', $contextLabel));
            $this->validatePositiveNumericField($errors, 'houseWidthM', $houseWidthM, sprintf('The houseWidthM field is required and must be numeric for %s in house mode.', $contextLabel));
            $this->validatePositiveNumericField($errors, 'widthM', $widthM, sprintf('The widthM field is required and must be numeric for %s in house mode.', $contextLabel));
            $this->validatePositiveNumericField($errors, 'heightM', $heightM, sprintf('The heightM field is required and must be numeric for %s in house mode.', $contextLabel));
        }

        if ($mode === 'segments') {
            $this->validateSegmentsForStripFoundation($errors, $segments);
        }

        $this->validateStrictBooleanField($errors, 'includeReinforcement', $includeReinforcement);
        $this->validateStrictBooleanField($errors, 'includeFormwork', $includeFormwork);

        $includeReinforcementEnabled = is_bool($includeReinforcement) && $includeReinforcement;
        if ($includeReinforcementEnabled) {
            $this->validatePositiveNumericField($errors, 'longitudinalBarsCount', $longitudinalBarsCount, 'The longitudinalBarsCount field is required and must be numeric when includeReinforcement is true.');
            $this->validatePositiveNumericField($errors, 'longitudinalDiameterMm', $longitudinalDiameterMm, 'The longitudinalDiameterMm field is required and must be numeric when includeReinforcement is true.');
            $this->validatePositiveNumericField($errors, 'longitudinalReservePercent', $longitudinalReservePercent, 'The longitudinalReservePercent field is required and must be numeric when includeReinforcement is true.');
            $this->validatePositiveNumericField($errors, 'transverseDiameterMm', $transverseDiameterMm, 'The transverseDiameterMm field is required and must be numeric when includeReinforcement is true.');
            $this->validatePositiveNumericField($errors, 'transverseStepMm', $transverseStepMm, 'The transverseStepMm field is required and must be numeric when includeReinforcement is true.');
            $this->validatePositiveNumericField($errors, 'transverseReservePercent', $transverseReservePercent, 'The transverseReservePercent field is required and must be numeric when includeReinforcement is true.');

            if ($this->isNumericValue($longitudinalBarsCount) && !$this->isIntegerNumber($longitudinalBarsCount)) {
                $errors['longitudinalBarsCount'][] = 'The longitudinalBarsCount field must be an integer.';
            }

            if ($mode === 'segments' && is_array($segments)) {
                $this->validateStripReinforcementSegments($errors, $segments);
            }
        }

        $includeFormworkEnabled = is_bool($includeFormwork) && $includeFormwork;
        if ($includeFormworkEnabled) {
            $this->validatePositiveNumericField($errors, 'formworkHeightM', $formworkHeightM, 'The formworkHeightM field is required and must be numeric when includeFormwork is true.');
            $this->validatePositiveNumericField($errors, 'formworkReservePercent', $formworkReservePercent, 'The formworkReservePercent field is required and must be numeric when includeFormwork is true.');

            if ($mode === 'segments' && is_array($segments)) {
                $this->validateStripFormworkSegments($errors, $segments);
            }
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateSegmentsForStripFoundation(array &$errors, mixed $segments): void
    {
        if (!is_array($segments) || $segments === []) {
            $errors['segments'][] = 'The segments field is required and must contain at least one segment for strip_foundation in segments mode.';
            return;
        }

        foreach ($segments as $index => $segment) {
            if (!is_array($segment)) {
                $errors['segments'][] = sprintf('The segments[%d] value must be an object.', $index);
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentLengthM', $index),
                $segment['segmentLengthM'] ?? null,
                sprintf('The segmentLengthM field is required and must be numeric in segments[%d].', $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentWidthM', $index),
                $segment['segmentWidthM'] ?? null,
                sprintf('The segmentWidthM field is required and must be numeric in segments[%d].', $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentHeightM', $index),
                $segment['segmentHeightM'] ?? null,
                sprintf('The segmentHeightM field is required and must be numeric in segments[%d].', $index)
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     * @param array<int, mixed> $segments
     */
    private function validateStripReinforcementSegments(array &$errors, array $segments): void
    {
        foreach ($segments as $index => $segment) {
            if (!is_array($segment)) {
                continue;
            }

            if (array_key_exists('segmentIncludeReinforcement', $segment) && !is_bool($segment['segmentIncludeReinforcement'])) {
                $errors[sprintf('segments.%d.segmentIncludeReinforcement', $index)][] = 'The segmentIncludeReinforcement field must be a boolean.';
            }

            $include = !array_key_exists('segmentIncludeReinforcement', $segment) || $segment['segmentIncludeReinforcement'] === true;
            if (!$include) {
                continue;
            }

            if (array_key_exists('segmentUseGlobalRebarParams', $segment) && !is_bool($segment['segmentUseGlobalRebarParams'])) {
                $errors[sprintf('segments.%d.segmentUseGlobalRebarParams', $index)][] = 'The segmentUseGlobalRebarParams field must be a boolean.';
            }

            $useGlobal = !array_key_exists('segmentUseGlobalRebarParams', $segment) || $segment['segmentUseGlobalRebarParams'] === true;
            if ($useGlobal) {
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentLongitudinalBarsCount', $index),
                $segment['segmentLongitudinalBarsCount'] ?? null,
                sprintf('The segmentLongitudinalBarsCount field is required and must be numeric in segments[%d] when segmentUseGlobalRebarParams is false.', $index)
            );
            if (isset($segment['segmentLongitudinalBarsCount']) && $this->isNumericValue($segment['segmentLongitudinalBarsCount']) && !$this->isIntegerNumber($segment['segmentLongitudinalBarsCount'])) {
                $errors[sprintf('segments.%d.segmentLongitudinalBarsCount', $index)][] = 'The segmentLongitudinalBarsCount field must be an integer.';
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentLongitudinalDiameterMm', $index),
                $segment['segmentLongitudinalDiameterMm'] ?? null,
                sprintf('The segmentLongitudinalDiameterMm field is required and must be numeric in segments[%d] when segmentUseGlobalRebarParams is false.', $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentTransverseDiameterMm', $index),
                $segment['segmentTransverseDiameterMm'] ?? null,
                sprintf('The segmentTransverseDiameterMm field is required and must be numeric in segments[%d] when segmentUseGlobalRebarParams is false.', $index)
            );
            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentTransverseStepMm', $index),
                $segment['segmentTransverseStepMm'] ?? null,
                sprintf('The segmentTransverseStepMm field is required and must be numeric in segments[%d] when segmentUseGlobalRebarParams is false.', $index)
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     * @param array<int, mixed> $segments
     */
    private function validateStripFormworkSegments(array &$errors, array $segments): void
    {
        foreach ($segments as $index => $segment) {
            if (!is_array($segment)) {
                continue;
            }

            if (array_key_exists('segmentIncludeFormwork', $segment) && !is_bool($segment['segmentIncludeFormwork'])) {
                $errors[sprintf('segments.%d.segmentIncludeFormwork', $index)][] = 'The segmentIncludeFormwork field must be a boolean.';
            }

            $include = !array_key_exists('segmentIncludeFormwork', $segment) || $segment['segmentIncludeFormwork'] === true;
            if (!$include) {
                continue;
            }

            if (array_key_exists('segmentUseGlobalFormworkParams', $segment) && !is_bool($segment['segmentUseGlobalFormworkParams'])) {
                $errors[sprintf('segments.%d.segmentUseGlobalFormworkParams', $index)][] = 'The segmentUseGlobalFormworkParams field must be a boolean.';
            }

            $useGlobal = !array_key_exists('segmentUseGlobalFormworkParams', $segment) || $segment['segmentUseGlobalFormworkParams'] === true;
            if ($useGlobal) {
                continue;
            }

            $this->validatePositiveNumericField(
                $errors,
                sprintf('segments.%d.segmentFormworkHeightM', $index),
                $segment['segmentFormworkHeightM'] ?? null,
                sprintf('The segmentFormworkHeightM field is required and must be numeric in segments[%d] when segmentUseGlobalFormworkParams is false.', $index)
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     * @param array<string, mixed>|mixed $mixture
     * @param array<int, string> $allowedTypes
     */
    private function validateConcreteMixtureConfig(array &$errors, string $fieldPrefix, mixed $mixture, array $allowedTypes, bool $requiresGravel): void
    {
        if (!is_array($mixture)) {
            $errors[$fieldPrefix][] = sprintf('The %s field is required and must be an object.', $fieldPrefix);
            return;
        }

        $type = $mixture['type'] ?? null;
        if (!$this->isNonEmptyString($type)) {
            $errors[$fieldPrefix . '.type'][] = sprintf('The %s.type field is required.', $fieldPrefix);
            return;
        }

        $type = (string) $type;
        if (!in_array($type, $allowedTypes, true)) {
            $errors[$fieldPrefix . '.type'][] = sprintf(
                'The %s.type field must be one of: %s.',
                $fieldPrefix,
                implode(', ', $allowedTypes)
            );
            return;
        }

        if ($type === 'ready') {
            $this->validateOptionalPositiveNumericField(
                $errors,
                $fieldPrefix . '.readyConcretePricePerM3',
                $mixture['readyConcretePricePerM3'] ?? null
            );
            return;
        }

        if ($type === 'dry_ready') {
            $this->validatePositiveNumericField(
                $errors,
                $fieldPrefix . '.dryMixBagWeightKg',
                $mixture['dryMixBagWeightKg'] ?? null,
                sprintf('The %s.dryMixBagWeightKg field is required and must be numeric.', $fieldPrefix)
            );
            $this->validateOptionalPositiveNumericField(
                $errors,
                $fieldPrefix . '.dryMixBagPrice',
                $mixture['dryMixBagPrice'] ?? null
            );
            return;
        }

        $this->validatePositiveNumericField(
            $errors,
            $fieldPrefix . '.cementShare',
            $mixture['cementShare'] ?? null,
            sprintf('The %s.cementShare field is required and must be numeric.', $fieldPrefix)
        );
        $this->validatePositiveNumericField(
            $errors,
            $fieldPrefix . '.sandShare',
            $mixture['sandShare'] ?? null,
            sprintf('The %s.sandShare field is required and must be numeric.', $fieldPrefix)
        );
        $this->validatePurchaseUnitField($errors, $fieldPrefix . '.cementPurchaseUnit', $mixture['cementPurchaseUnit'] ?? null);
        $this->validatePurchaseUnitField($errors, $fieldPrefix . '.sandPurchaseUnit', $mixture['sandPurchaseUnit'] ?? null);
        $this->validatePositiveNumericField(
            $errors,
            $fieldPrefix . '.cementUnitWeightKg',
            $mixture['cementUnitWeightKg'] ?? null,
            sprintf('The %s.cementUnitWeightKg field is required and must be numeric.', $fieldPrefix)
        );
        $this->validateOptionalPositiveNumericField(
            $errors,
            $fieldPrefix . '.cementUnitPrice',
            $mixture['cementUnitPrice'] ?? null
        );
        $this->validatePositiveNumericField(
            $errors,
            $fieldPrefix . '.sandUnitWeightKg',
            $mixture['sandUnitWeightKg'] ?? null,
            sprintf('The %s.sandUnitWeightKg field is required and must be numeric.', $fieldPrefix)
        );
        $this->validateOptionalPositiveNumericField(
            $errors,
            $fieldPrefix . '.sandUnitPrice',
            $mixture['sandUnitPrice'] ?? null
        );

        if ($requiresGravel) {
            $this->validatePositiveNumericField(
                $errors,
                $fieldPrefix . '.gravelShare',
                $mixture['gravelShare'] ?? null,
                sprintf('The %s.gravelShare field is required and must be numeric.', $fieldPrefix)
            );
            $this->validatePurchaseUnitField($errors, $fieldPrefix . '.gravelPurchaseUnit', $mixture['gravelPurchaseUnit'] ?? null);
            $this->validatePositiveNumericField(
                $errors,
                $fieldPrefix . '.gravelUnitWeightKg',
                $mixture['gravelUnitWeightKg'] ?? null,
                sprintf('The %s.gravelUnitWeightKg field is required and must be numeric.', $fieldPrefix)
            );
            $this->validateOptionalPositiveNumericField(
                $errors,
                $fieldPrefix . '.gravelUnitPrice',
                $mixture['gravelUnitPrice'] ?? null
            );
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validatePurchaseUnitField(array &$errors, string $field, mixed $value): void
    {
        if (!$this->isNonEmptyString($value)) {
            $errors[$field][] = sprintf('The %s field is required.', $field);
            return;
        }

        if (!in_array((string) $value, self::ALLOWED_PURCHASE_UNITS, true)) {
            $errors[$field][] = sprintf('The %s field must be one of: bag, tonne.', $field);
        }
    }

    private function isBoredPileType(mixed $pileType): bool
    {
        if (!$this->isNonEmptyString($pileType)) {
            return true;
        }

        return (string) $pileType === 'bored';
    }

    private function isIntegerNumber(mixed $value): bool
    {
        if (!$this->isNumericValue($value)) {
            return false;
        }

        $number = (float) $value;
        return $number === (float) (int) $number;
    }

    private function isValidRebarLayers(mixed $value): bool
    {
        if (!$this->isNumericValue($value)) {
            return false;
        }

        $normalized = (float) $value;
        if ($normalized !== (float) (int) $normalized) {
            return false;
        }

        return in_array((int) $normalized, self::ALLOWED_REBAR_LAYERS, true);
    }
}
