<?php

declare(strict_types=1);

namespace Brigmaster\Http\Rest;

use Brigmaster\Application\EstimateService;
use Brigmaster\Http\Rest\Validation\EstimateRequestMapper;
use Brigmaster\Http\Rest\Validation\EstimateRequestValidator;
use InvalidArgumentException;
use WP_REST_Request;
use WP_REST_Response;

final class EstimateController
{
    private readonly EstimateRequestValidator $requestValidator;

    private readonly EstimateRequestMapper $requestMapper;

    public function __construct(
        private readonly EstimateService $estimateService
    ) {
        $this->requestValidator = new EstimateRequestValidator();
        $this->requestMapper = new EstimateRequestMapper();
    }

    public function registerRoutes(): void
    {
        \register_rest_route(
            'brigmaster/v1',
            '/estimate',
            [
                'methods' => 'POST',
                'callback' => [$this, 'handleEstimate'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function handleEstimate(WP_REST_Request $request): WP_REST_Response
    {
        $calculatorRaw = $request->get_param('calculator');
        $modeRaw = $request->get_param('mode');
        $areaRaw = $request->get_param('area');
        $thicknessRaw = $request->get_param('thickness');
        $subTypeRaw = $request->get_param('subType');
        $tileLengthCmRaw = $request->get_param('tileLengthCm');
        $tileWidthCmRaw = $request->get_param('tileWidthCm');
        $lengthRaw = $request->get_param('length');
        $widthRaw = $request->get_param('width');
        $heightRaw = $request->get_param('height');
        $includeReinforcementRaw = $request->get_param('includeReinforcement');
        $includeFormworkRaw = $request->get_param('includeFormwork');
        $rebarDiameterMmRaw = $request->get_param('rebarDiameterMm');
        $rebarStepMmRaw = $request->get_param('rebarStepMm');
        $rebarLayersRaw = $request->get_param('rebarLayers');
        $rebarReservePercentRaw = $request->get_param('rebarReservePercent');
        $formworkHeightMRaw = $request->get_param('formworkHeightM');
        $formworkReservePercentRaw = $request->get_param('formworkReservePercent');
        $totalLengthMRaw = $request->get_param('totalLengthM');
        $widthMRaw = $request->get_param('widthM');
        $heightMRaw = $request->get_param('heightM');
        $houseLengthMRaw = $request->get_param('houseLengthM');
        $houseWidthMRaw = $request->get_param('houseWidthM');
        $segmentsRaw = $request->get_param('segments');
        $longitudinalBarsCountRaw = $request->get_param('longitudinalBarsCount');
        $longitudinalDiameterMmRaw = $request->get_param('longitudinalDiameterMm');
        $longitudinalReservePercentRaw = $request->get_param('longitudinalReservePercent');
        $transverseDiameterMmRaw = $request->get_param('transverseDiameterMm');
        $transverseStepMmRaw = $request->get_param('transverseStepMm');
        $transverseReservePercentRaw = $request->get_param('transverseReservePercent');
        $pileTypeRaw = $request->get_param('pileType');
        $includePilesRaw = $request->get_param('includePiles');
        $pilesCountRaw = $request->get_param('pilesCount');
        $pileShaftDiameterMRaw = $request->get_param('pileShaftDiameterM');
        $pileShaftHeightMRaw = $request->get_param('pileShaftHeightM');
        $includePileBaseRaw = $request->get_param('includePileBase');
        $pileBaseDiameterMRaw = $request->get_param('pileBaseDiameterM');
        $pileBaseHeightMRaw = $request->get_param('pileBaseHeightM');
        $includeGrillageRaw = $request->get_param('includeGrillage');
        $includePileReinforcementRaw = $request->get_param('includePileReinforcement');
        $pileReinforcementBarsCountRaw = $request->get_param('pileReinforcementBarsCount');
        $pileReinforcementDiameterMmRaw = $request->get_param('pileReinforcementDiameterMm');
        $pileReinforcementReservePercentRaw = $request->get_param('pileReinforcementReservePercent');
        $mixtureRaw = $request->get_param('mixture');
        $useUnifiedConcreteMixtureSettingsRaw = $request->get_param('useUnifiedConcreteMixtureSettings');
        $pileMixtureRaw = $request->get_param('pileMixture');
        $grillageMixtureRaw = $request->get_param('grillageMixture');
        $brickFormatRaw = $request->get_param('brickFormat');
        $brickLengthMmRaw = $request->get_param('brickLengthMm');
        $brickWidthMmRaw = $request->get_param('brickWidthMm');
        $brickHeightMmRaw = $request->get_param('brickHeightMm');
        $jointThicknessMmRaw = $request->get_param('jointThicknessMm');
        $wallThicknessTypeRaw = $request->get_param('wallThicknessType');
        $wallLengthMRaw = $request->get_param('wallLengthM');
        $wallHeightMRaw = $request->get_param('wallHeightM');
        $reservePercentRaw = $request->get_param('reservePercent');
        $includeOpeningsRaw = $request->get_param('includeOpenings');
        $windowsRaw = $request->get_param('windows');
        $doorsRaw = $request->get_param('doors');
        $includeGablesRaw = $request->get_param('includeGables');
        $gablesRaw = $request->get_param('gables');
        $includeMasonryMeshRaw = $request->get_param('includeMasonryMesh');
        $masonryMeshFrequencyRowsRaw = $request->get_param('masonryMeshFrequencyRows');
        $useCustomMortarProportionsRaw = $request->get_param('useCustomMortarProportions');
        $cementShareRaw = $request->get_param('cementShare');
        $sandShareRaw = $request->get_param('sandShare');
        $cementPurchaseUnitRaw = $request->get_param('cementPurchaseUnit');
        $cementUnitWeightKgRaw = $request->get_param('cementUnitWeightKg');
        $cementUnitPriceRaw = $request->get_param('cementUnitPrice');
        $sandPurchaseUnitRaw = $request->get_param('sandPurchaseUnit');
        $sandUnitWeightKgRaw = $request->get_param('sandUnitWeightKg');
        $sandUnitPriceRaw = $request->get_param('sandUnitPrice');
        $cementBagWeightKgRaw = $request->get_param('cementBagWeightKg');
        $brickWeightKgRaw = $request->get_param('brickWeightKg');
        $brickPricePerUnitRaw = $request->get_param('brickPricePerUnit');
        $cementBagPriceRaw = $request->get_param('cementBagPrice');
        $sandPricePerTonneRaw = $request->get_param('sandPricePerTonne');
        $tileTargetRaw = $request->get_param('tileTarget');
        $tileLengthMmRaw = $request->get_param('tileLengthMm');
        $tileWidthMmRaw = $request->get_param('tileWidthMm');
        $tileThicknessMmRaw = $request->get_param('tileThicknessMm');
        $tileJointMmRaw = $request->get_param('tileJointMm');
        $tileLayingPatternRaw = $request->get_param('tileLayingPattern');
        $tileOffsetPercentRaw = $request->get_param('tileOffsetPercent');
        $tileIncludeOpeningsRaw = $request->get_param('tileIncludeOpenings');
        $tileOpeningsRaw = $request->get_param('tileOpenings');
        $tileIncludeCutoutsRaw = $request->get_param('tileIncludeCutouts');
        $tileCutoutsRaw = $request->get_param('tileCutouts');
        $tileIncludeAdhesiveRaw = $request->get_param('tileIncludeAdhesive');
        $tileAdhesiveConsumptionKgPerM2Raw = $request->get_param('tileAdhesiveConsumptionKgPerM2');
        $tileAdhesiveLayerMmRaw = $request->get_param('tileAdhesiveLayerMm');
        $tileAdhesiveBagWeightKgRaw = $request->get_param('tileAdhesiveBagWeightKg');
        $tileAdhesiveBagPriceRaw = $request->get_param('tileAdhesiveBagPrice');
        $tileIncludeGroutRaw = $request->get_param('tileIncludeGrout');
        $tileGroutDensityKgPerM3Raw = $request->get_param('tileGroutDensityKgPerM3');
        $tileGroutPackWeightKgRaw = $request->get_param('tileGroutPackWeightKg');
        $tileGroutPackPriceRaw = $request->get_param('tileGroutPackPrice');
        $tilePricePerM2Raw = $request->get_param('tilePricePerM2');
        $drywallTargetRaw = $request->get_param('drywallTarget');
        $drywallSheetLengthMmRaw = $request->get_param('drywallSheetLengthMm');
        $drywallSheetWidthMmRaw = $request->get_param('drywallSheetWidthMm');
        $drywallSheetThicknessMmRaw = $request->get_param('drywallSheetThicknessMm');
        $drywallLayersRaw = $request->get_param('drywallLayers');
        $drywallFrameStepMmRaw = $request->get_param('drywallFrameStepMm');
        $drywallProfileWidthMmRaw = $request->get_param('drywallProfileWidthMm');
        $drywallFastenerReservePercentRaw = $request->get_param('drywallFastenerReservePercent');
        $drywallIncludeEndCladdingRaw = $request->get_param('drywallIncludeEndCladding');
        $drywallIncludeFinishingRaw = $request->get_param('drywallIncludeFinishing');
        $drywallIncludeCostsRaw = $request->get_param('drywallIncludeCosts');
        $drywallSheetPriceRaw = $request->get_param('drywallSheetPrice');
        $drywallProfilePricePerLmRaw = $request->get_param('drywallProfilePricePerLm');
        $drywallFastenerPricePer100Raw = $request->get_param('drywallFastenerPricePer100');
        $drywallPrimerPricePerKgRaw = $request->get_param('drywallPrimerPricePerKg');
        $drywallJointPuttyPricePerKgRaw = $request->get_param('drywallJointPuttyPricePerKg');
        $drywallFinishPuttyPricePerKgRaw = $request->get_param('drywallFinishPuttyPricePerKg');
        $drywallTapePricePerLmRaw = $request->get_param('drywallTapePricePerLm');

        $errors = $this->requestValidator->validate(
            calculator: $calculatorRaw,
            mode: $modeRaw,
            area: $areaRaw,
            thickness: $thicknessRaw,
            subType: $subTypeRaw,
            tileLengthCm: $tileLengthCmRaw,
            tileWidthCm: $tileWidthCmRaw,
            length: $lengthRaw,
            width: $widthRaw,
            height: $heightRaw,
            includeReinforcement: $includeReinforcementRaw,
            includeFormwork: $includeFormworkRaw,
            rebarDiameterMm: $rebarDiameterMmRaw,
            rebarStepMm: $rebarStepMmRaw,
            rebarLayers: $rebarLayersRaw,
            rebarReservePercent: $rebarReservePercentRaw,
            formworkHeightM: $formworkHeightMRaw,
            formworkReservePercent: $formworkReservePercentRaw,
            totalLengthM: $totalLengthMRaw,
            widthM: $widthMRaw,
            heightM: $heightMRaw,
            houseLengthM: $houseLengthMRaw,
            houseWidthM: $houseWidthMRaw,
            segments: $segmentsRaw,
            longitudinalBarsCount: $longitudinalBarsCountRaw,
            longitudinalDiameterMm: $longitudinalDiameterMmRaw,
            longitudinalReservePercent: $longitudinalReservePercentRaw,
            transverseDiameterMm: $transverseDiameterMmRaw,
            transverseStepMm: $transverseStepMmRaw,
            transverseReservePercent: $transverseReservePercentRaw,
            pileType: $pileTypeRaw,
            includePiles: $includePilesRaw,
            pilesCount: $pilesCountRaw,
            pileShaftDiameterM: $pileShaftDiameterMRaw,
            pileShaftHeightM: $pileShaftHeightMRaw,
            includePileBase: $includePileBaseRaw,
            pileBaseDiameterM: $pileBaseDiameterMRaw,
            pileBaseHeightM: $pileBaseHeightMRaw,
            includeGrillage: $includeGrillageRaw,
            includePileReinforcement: $includePileReinforcementRaw,
            pileReinforcementBarsCount: $pileReinforcementBarsCountRaw,
            pileReinforcementDiameterMm: $pileReinforcementDiameterMmRaw,
            pileReinforcementReservePercent: $pileReinforcementReservePercentRaw,
            mixture: $mixtureRaw,
            useUnifiedConcreteMixtureSettings: $useUnifiedConcreteMixtureSettingsRaw,
            pileMixture: $pileMixtureRaw,
            grillageMixture: $grillageMixtureRaw,
            brickFormat: $brickFormatRaw,
            brickLengthMm: $brickLengthMmRaw,
            brickWidthMm: $brickWidthMmRaw,
            brickHeightMm: $brickHeightMmRaw,
            jointThicknessMm: $jointThicknessMmRaw,
            wallThicknessType: $wallThicknessTypeRaw,
            wallLengthM: $wallLengthMRaw,
            wallHeightM: $wallHeightMRaw,
            reservePercent: $reservePercentRaw,
            includeOpenings: $includeOpeningsRaw,
            windows: $windowsRaw,
            doors: $doorsRaw,
            includeGables: $includeGablesRaw,
            gables: $gablesRaw,
            includeMasonryMesh: $includeMasonryMeshRaw,
            masonryMeshFrequencyRows: $masonryMeshFrequencyRowsRaw,
            useCustomMortarProportions: $useCustomMortarProportionsRaw,
            cementShare: $cementShareRaw,
            sandShare: $sandShareRaw,
            cementPurchaseUnit: $cementPurchaseUnitRaw,
            cementUnitWeightKg: $cementUnitWeightKgRaw,
            cementUnitPrice: $cementUnitPriceRaw,
            sandPurchaseUnit: $sandPurchaseUnitRaw,
            sandUnitWeightKg: $sandUnitWeightKgRaw,
            sandUnitPrice: $sandUnitPriceRaw,
            cementBagWeightKg: $cementBagWeightKgRaw,
            brickWeightKg: $brickWeightKgRaw,
            brickPricePerUnit: $brickPricePerUnitRaw,
            cementBagPrice: $cementBagPriceRaw,
            sandPricePerTonne: $sandPricePerTonneRaw,
            tileTarget: $tileTargetRaw,
            tileLengthMm: $tileLengthMmRaw,
            tileWidthMm: $tileWidthMmRaw,
            tileThicknessMm: $tileThicknessMmRaw,
            tileJointMm: $tileJointMmRaw,
            tileLayingPattern: $tileLayingPatternRaw,
            tileOffsetPercent: $tileOffsetPercentRaw,
            tileIncludeOpenings: $tileIncludeOpeningsRaw,
            tileOpenings: $tileOpeningsRaw,
            tileIncludeCutouts: $tileIncludeCutoutsRaw,
            tileCutouts: $tileCutoutsRaw,
            tileIncludeAdhesive: $tileIncludeAdhesiveRaw,
            tileAdhesiveConsumptionKgPerM2: $tileAdhesiveConsumptionKgPerM2Raw,
            tileAdhesiveLayerMm: $tileAdhesiveLayerMmRaw,
            tileAdhesiveBagWeightKg: $tileAdhesiveBagWeightKgRaw,
            tileAdhesiveBagPrice: $tileAdhesiveBagPriceRaw,
            tileIncludeGrout: $tileIncludeGroutRaw,
            tileGroutDensityKgPerM3: $tileGroutDensityKgPerM3Raw,
            tileGroutPackWeightKg: $tileGroutPackWeightKgRaw,
            tileGroutPackPrice: $tileGroutPackPriceRaw,
            tilePricePerM2: $tilePricePerM2Raw,
            drywallTarget: $drywallTargetRaw,
            drywallSheetLengthMm: $drywallSheetLengthMmRaw,
            drywallSheetWidthMm: $drywallSheetWidthMmRaw,
            drywallSheetThicknessMm: $drywallSheetThicknessMmRaw,
            drywallLayers: $drywallLayersRaw,
            drywallFrameStepMm: $drywallFrameStepMmRaw,
            drywallProfileWidthMm: $drywallProfileWidthMmRaw,
            drywallFastenerReservePercent: $drywallFastenerReservePercentRaw,
            drywallIncludeEndCladding: $drywallIncludeEndCladdingRaw,
            drywallIncludeFinishing: $drywallIncludeFinishingRaw,
            drywallIncludeCosts: $drywallIncludeCostsRaw,
            drywallSheetPrice: $drywallSheetPriceRaw,
            drywallProfilePricePerLm: $drywallProfilePricePerLmRaw,
            drywallFastenerPricePer100: $drywallFastenerPricePer100Raw,
            drywallPrimerPricePerKg: $drywallPrimerPricePerKgRaw,
            drywallJointPuttyPricePerKg: $drywallJointPuttyPricePerKgRaw,
            drywallFinishPuttyPricePerKg: $drywallFinishPuttyPricePerKgRaw,
            drywallTapePricePerLm: $drywallTapePricePerLmRaw
        );

        if ($errors !== []) {
            return new WP_REST_Response(
                [
                    'code' => 'validation_error',
                    'message' => 'Validation failed.',
                    'errors' => $errors,
                ],
                400
            );
        }

        try {
            $args = $this->requestMapper->coerce($request);
            $result = $this->estimateService->calculate(...$args);
            $calculator = $args["calculator"];

            $response = [
                'calculator' => $calculator,
                'mode' => $result->mode,
            ];

            if (in_array($calculator, [EstimateService::CALCULATOR_SLAB_FOUNDATION, EstimateService::CALCULATOR_STRIP_FOUNDATION, EstimateService::CALCULATOR_PILE_FOUNDATION], true)) {
                $response = array_merge($response, $result->details);
            } else {
                $response['calculatedVolume'] = $result->calculatedVolume;
                $response['calculatedMaterialAmount'] = $result->calculatedMaterialAmount;

                if ($result->details !== []) {
                    $response = array_merge($response, $result->details);
                }
            }

            return new WP_REST_Response($response, 200);
        } catch (InvalidArgumentException $exception) {
            return new WP_REST_Response(
                [
                    'code' => 'validation_error',
                    'message' => 'Validation failed.',
                    'errors' => [
                        'general' => [$exception->getMessage()],
                    ],
                ],
                400
            );
        }
    }

}
