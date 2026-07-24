<?php

declare(strict_types=1);

namespace Brigmaster\Http\Rest;

use Brigmaster\Application\EstimateService;
use Brigmaster\Domain\DTO\EstimateInput;
use Brigmaster\Http\Rest\Validation\EstimateRequestValidator;
use Brigmaster\Http\Rest\Validation\RequestValueHelpers;
use InvalidArgumentException;
use WP_REST_Request;
use WP_REST_Response;

final class EstimateController
{
    use RequestValueHelpers;

    private readonly EstimateRequestValidator $requestValidator;

    public function __construct(
        private readonly EstimateService $estimateService
    ) {
        $this->requestValidator = new EstimateRequestValidator();
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
            $calculator = (string) $calculatorRaw;
            $mode = (string) $modeRaw;
            $area = $this->isNumericValue($areaRaw) ? (float) $areaRaw : null;
            $thickness = $this->isNumericValue($thicknessRaw) ? (float) $thicknessRaw : null;
            $subType = $this->isNonEmptyString($subTypeRaw) ? (string) $subTypeRaw : null;
            $tileLengthCm = $this->isNumericValue($tileLengthCmRaw) ? (float) $tileLengthCmRaw : null;
            $tileWidthCm = $this->isNumericValue($tileWidthCmRaw) ? (float) $tileWidthCmRaw : null;
            $length = $this->isNumericValue($lengthRaw) ? (float) $lengthRaw : null;
            $width = $this->isNumericValue($widthRaw) ? (float) $widthRaw : null;
            $height = $this->isNumericValue($heightRaw) ? (float) $heightRaw : null;
            $includeReinforcement = is_bool($includeReinforcementRaw) ? $includeReinforcementRaw : null;
            $includeFormwork = is_bool($includeFormworkRaw) ? $includeFormworkRaw : null;
            $rebarDiameterMm = $this->isNumericValue($rebarDiameterMmRaw) ? (float) $rebarDiameterMmRaw : null;
            $rebarStepMm = $this->isNumericValue($rebarStepMmRaw) ? (float) $rebarStepMmRaw : null;
            $rebarLayers = $this->isNumericValue($rebarLayersRaw) ? (int) $rebarLayersRaw : null;
            $rebarReservePercent = $this->isNumericValue($rebarReservePercentRaw) ? (float) $rebarReservePercentRaw : null;
            $formworkHeightM = $this->isNumericValue($formworkHeightMRaw) ? (float) $formworkHeightMRaw : null;
            $formworkReservePercent = $this->isNumericValue($formworkReservePercentRaw) ? (float) $formworkReservePercentRaw : null;
            $totalLengthM = $this->isNumericValue($totalLengthMRaw) ? (float) $totalLengthMRaw : null;
            $widthM = $this->isNumericValue($widthMRaw) ? (float) $widthMRaw : null;
            $heightM = $this->isNumericValue($heightMRaw) ? (float) $heightMRaw : null;
            $houseLengthM = $this->isNumericValue($houseLengthMRaw) ? (float) $houseLengthMRaw : null;
            $houseWidthM = $this->isNumericValue($houseWidthMRaw) ? (float) $houseWidthMRaw : null;
            $segments = is_array($segmentsRaw) ? $segmentsRaw : null;
            $longitudinalBarsCount = $this->isNumericValue($longitudinalBarsCountRaw) ? (int) $longitudinalBarsCountRaw : null;
            $longitudinalDiameterMm = $this->isNumericValue($longitudinalDiameterMmRaw) ? (float) $longitudinalDiameterMmRaw : null;
            $longitudinalReservePercent = $this->isNumericValue($longitudinalReservePercentRaw) ? (float) $longitudinalReservePercentRaw : null;
            $transverseDiameterMm = $this->isNumericValue($transverseDiameterMmRaw) ? (float) $transverseDiameterMmRaw : null;
            $transverseStepMm = $this->isNumericValue($transverseStepMmRaw) ? (float) $transverseStepMmRaw : null;
            $transverseReservePercent = $this->isNumericValue($transverseReservePercentRaw) ? (float) $transverseReservePercentRaw : null;
            $pileType = $this->isNonEmptyString($pileTypeRaw) ? (string) $pileTypeRaw : null;
            $includePiles = is_bool($includePilesRaw) ? $includePilesRaw : null;
            $pilesCount = $this->isNumericValue($pilesCountRaw) ? (int) $pilesCountRaw : null;
            $pileShaftDiameterM = $this->isNumericValue($pileShaftDiameterMRaw) ? (float) $pileShaftDiameterMRaw : null;
            $pileShaftHeightM = $this->isNumericValue($pileShaftHeightMRaw) ? (float) $pileShaftHeightMRaw : null;
            $includePileBase = is_bool($includePileBaseRaw) ? $includePileBaseRaw : null;
            $pileBaseDiameterM = $this->isNumericValue($pileBaseDiameterMRaw) ? (float) $pileBaseDiameterMRaw : null;
            $pileBaseHeightM = $this->isNumericValue($pileBaseHeightMRaw) ? (float) $pileBaseHeightMRaw : null;
            $includeGrillage = is_bool($includeGrillageRaw) ? $includeGrillageRaw : null;
            $includePileReinforcement = is_bool($includePileReinforcementRaw) ? $includePileReinforcementRaw : null;
            $pileReinforcementBarsCount = $this->isNumericValue($pileReinforcementBarsCountRaw) ? (int) $pileReinforcementBarsCountRaw : null;
            $pileReinforcementDiameterMm = $this->isNumericValue($pileReinforcementDiameterMmRaw) ? (float) $pileReinforcementDiameterMmRaw : null;
            $pileReinforcementReservePercent = $this->isNumericValue($pileReinforcementReservePercentRaw) ? (float) $pileReinforcementReservePercentRaw : null;
            $mixture = is_array($mixtureRaw) ? $mixtureRaw : null;
            $useUnifiedConcreteMixtureSettings = is_bool($useUnifiedConcreteMixtureSettingsRaw) ? $useUnifiedConcreteMixtureSettingsRaw : null;
            $pileMixture = is_array($pileMixtureRaw) ? $pileMixtureRaw : null;
            $grillageMixture = is_array($grillageMixtureRaw) ? $grillageMixtureRaw : null;
            $brickFormat = $this->isNonEmptyString($brickFormatRaw) ? (string) $brickFormatRaw : null;
            $brickLengthMm = $this->isNumericValue($brickLengthMmRaw) ? (float) $brickLengthMmRaw : null;
            $brickWidthMm = $this->isNumericValue($brickWidthMmRaw) ? (float) $brickWidthMmRaw : null;
            $brickHeightMm = $this->isNumericValue($brickHeightMmRaw) ? (float) $brickHeightMmRaw : null;
            $jointThicknessMm = $this->isNumericValue($jointThicknessMmRaw) ? (float) $jointThicknessMmRaw : null;
            $wallThicknessType = $this->isNonEmptyString($wallThicknessTypeRaw) ? (string) $wallThicknessTypeRaw : null;
            $wallLengthM = $this->isNumericValue($wallLengthMRaw) ? (float) $wallLengthMRaw : null;
            $wallHeightM = $this->isNumericValue($wallHeightMRaw) ? (float) $wallHeightMRaw : null;
            $reservePercent = $this->isNumericValue($reservePercentRaw) ? (float) $reservePercentRaw : null;
            $includeOpenings = is_bool($includeOpeningsRaw) ? $includeOpeningsRaw : null;
            $windows = is_array($windowsRaw) ? $windowsRaw : null;
            $doors = is_array($doorsRaw) ? $doorsRaw : null;
            $includeGables = is_bool($includeGablesRaw) ? $includeGablesRaw : null;
            $gables = is_array($gablesRaw) ? $gablesRaw : null;
            $includeMasonryMesh = is_bool($includeMasonryMeshRaw) ? $includeMasonryMeshRaw : null;
            $masonryMeshFrequencyRows = $this->isNumericValue($masonryMeshFrequencyRowsRaw) ? (int) $masonryMeshFrequencyRowsRaw : null;
            $useCustomMortarProportions = is_bool($useCustomMortarProportionsRaw) ? $useCustomMortarProportionsRaw : null;
            $cementShare = $this->isNumericValue($cementShareRaw) ? (float) $cementShareRaw : null;
            $sandShare = $this->isNumericValue($sandShareRaw) ? (float) $sandShareRaw : null;
            $cementPurchaseUnit = $this->isNonEmptyString($cementPurchaseUnitRaw) ? (string) $cementPurchaseUnitRaw : null;
            $cementUnitWeightKg = $this->isNumericValue($cementUnitWeightKgRaw) ? (float) $cementUnitWeightKgRaw : null;
            $cementUnitPrice = $this->isNumericValue($cementUnitPriceRaw) ? (float) $cementUnitPriceRaw : null;
            $sandPurchaseUnit = $this->isNonEmptyString($sandPurchaseUnitRaw) ? (string) $sandPurchaseUnitRaw : null;
            $sandUnitWeightKg = $this->isNumericValue($sandUnitWeightKgRaw) ? (float) $sandUnitWeightKgRaw : null;
            $sandUnitPrice = $this->isNumericValue($sandUnitPriceRaw) ? (float) $sandUnitPriceRaw : null;
            $cementBagWeightKg = $this->isNumericValue($cementBagWeightKgRaw) ? (float) $cementBagWeightKgRaw : null;
            $brickWeightKg = $this->isNumericValue($brickWeightKgRaw) ? (float) $brickWeightKgRaw : null;
            $brickPricePerUnit = $this->isNumericValue($brickPricePerUnitRaw) ? (float) $brickPricePerUnitRaw : null;
            $cementBagPrice = $this->isNumericValue($cementBagPriceRaw) ? (float) $cementBagPriceRaw : null;
            $sandPricePerTonne = $this->isNumericValue($sandPricePerTonneRaw) ? (float) $sandPricePerTonneRaw : null;
            $tileTarget = $this->isNonEmptyString($tileTargetRaw) ? (string) $tileTargetRaw : null;
            $tileLengthMm = $this->isNumericValue($tileLengthMmRaw) ? (float) $tileLengthMmRaw : null;
            $tileWidthMm = $this->isNumericValue($tileWidthMmRaw) ? (float) $tileWidthMmRaw : null;
            $tileThicknessMm = $this->isNumericValue($tileThicknessMmRaw) ? (float) $tileThicknessMmRaw : null;
            $tileJointMm = $this->isNumericValue($tileJointMmRaw) ? (float) $tileJointMmRaw : null;
            $tileLayingPattern = $this->isNonEmptyString($tileLayingPatternRaw) ? (string) $tileLayingPatternRaw : null;
            $tileOffsetPercent = $this->isNumericValue($tileOffsetPercentRaw) ? (float) $tileOffsetPercentRaw : null;
            $tileIncludeOpenings = is_bool($tileIncludeOpeningsRaw) ? $tileIncludeOpeningsRaw : null;
            $tileOpenings = is_array($tileOpeningsRaw) ? $tileOpeningsRaw : null;
            $tileIncludeCutouts = is_bool($tileIncludeCutoutsRaw) ? $tileIncludeCutoutsRaw : null;
            $tileCutouts = is_array($tileCutoutsRaw) ? $tileCutoutsRaw : null;
            $tileIncludeAdhesive = is_bool($tileIncludeAdhesiveRaw) ? $tileIncludeAdhesiveRaw : null;
            $tileAdhesiveConsumptionKgPerM2 = $this->isNumericValue($tileAdhesiveConsumptionKgPerM2Raw) ? (float) $tileAdhesiveConsumptionKgPerM2Raw : null;
            $tileAdhesiveLayerMm = $this->isNumericValue($tileAdhesiveLayerMmRaw) ? (float) $tileAdhesiveLayerMmRaw : null;
            $tileAdhesiveBagWeightKg = $this->isNumericValue($tileAdhesiveBagWeightKgRaw) ? (float) $tileAdhesiveBagWeightKgRaw : null;
            $tileAdhesiveBagPrice = $this->isNumericValue($tileAdhesiveBagPriceRaw) ? (float) $tileAdhesiveBagPriceRaw : null;
            $tileIncludeGrout = is_bool($tileIncludeGroutRaw) ? $tileIncludeGroutRaw : null;
            $tileGroutDensityKgPerM3 = $this->isNumericValue($tileGroutDensityKgPerM3Raw) ? (float) $tileGroutDensityKgPerM3Raw : null;
            $tileGroutPackWeightKg = $this->isNumericValue($tileGroutPackWeightKgRaw) ? (float) $tileGroutPackWeightKgRaw : null;
            $tileGroutPackPrice = $this->isNumericValue($tileGroutPackPriceRaw) ? (float) $tileGroutPackPriceRaw : null;
            $tilePricePerM2 = $this->isNumericValue($tilePricePerM2Raw) ? (float) $tilePricePerM2Raw : null;
            $drywallTarget = $this->isNonEmptyString($drywallTargetRaw) ? (string) $drywallTargetRaw : null;
            $drywallSheetLengthMm = $this->isNumericValue($drywallSheetLengthMmRaw) ? (float) $drywallSheetLengthMmRaw : null;
            $drywallSheetWidthMm = $this->isNumericValue($drywallSheetWidthMmRaw) ? (float) $drywallSheetWidthMmRaw : null;
            $drywallSheetThicknessMm = $this->isNumericValue($drywallSheetThicknessMmRaw) ? (float) $drywallSheetThicknessMmRaw : null;
            $drywallLayers = $this->isNumericValue($drywallLayersRaw) ? (int) $drywallLayersRaw : null;
            $drywallFrameStepMm = $this->isNumericValue($drywallFrameStepMmRaw) ? (float) $drywallFrameStepMmRaw : null;
            $drywallProfileWidthMm = $this->isNumericValue($drywallProfileWidthMmRaw) ? (float) $drywallProfileWidthMmRaw : null;
            $drywallFastenerReservePercent = $this->isNumericValue($drywallFastenerReservePercentRaw) ? (float) $drywallFastenerReservePercentRaw : null;
            $drywallIncludeEndCladding = is_bool($drywallIncludeEndCladdingRaw) ? $drywallIncludeEndCladdingRaw : null;
            $drywallIncludeFinishing = is_bool($drywallIncludeFinishingRaw) ? $drywallIncludeFinishingRaw : null;
            $drywallIncludeCosts = is_bool($drywallIncludeCostsRaw) ? $drywallIncludeCostsRaw : null;
            $drywallSheetPrice = $this->isNumericValue($drywallSheetPriceRaw) ? (float) $drywallSheetPriceRaw : null;
            $drywallProfilePricePerLm = $this->isNumericValue($drywallProfilePricePerLmRaw) ? (float) $drywallProfilePricePerLmRaw : null;
            $drywallFastenerPricePer100 = $this->isNumericValue($drywallFastenerPricePer100Raw) ? (float) $drywallFastenerPricePer100Raw : null;
            $drywallPrimerPricePerKg = $this->isNumericValue($drywallPrimerPricePerKgRaw) ? (float) $drywallPrimerPricePerKgRaw : null;
            $drywallJointPuttyPricePerKg = $this->isNumericValue($drywallJointPuttyPricePerKgRaw) ? (float) $drywallJointPuttyPricePerKgRaw : null;
            $drywallFinishPuttyPricePerKg = $this->isNumericValue($drywallFinishPuttyPricePerKgRaw) ? (float) $drywallFinishPuttyPricePerKgRaw : null;
            $drywallTapePricePerLm = $this->isNumericValue($drywallTapePricePerLmRaw) ? (float) $drywallTapePricePerLmRaw : null;

            $result = $this->estimateService->calculate(
                calculator: $calculator,
                mode: $mode,
                area: $area,
                thickness: $thickness,
                subType: $subType,
                tileLengthCm: $tileLengthCm,
                tileWidthCm: $tileWidthCm,
                length: $length,
                width: $width,
                height: $height,
                includeReinforcement: $includeReinforcement,
                includeFormwork: $includeFormwork,
                rebarDiameterMm: $rebarDiameterMm,
                rebarStepMm: $rebarStepMm,
                rebarLayers: $rebarLayers,
                rebarReservePercent: $rebarReservePercent,
                formworkHeightM: $formworkHeightM,
                formworkReservePercent: $formworkReservePercent,
                totalLengthM: $totalLengthM,
                widthM: $widthM,
                heightM: $heightM,
                houseLengthM: $houseLengthM,
                houseWidthM: $houseWidthM,
                segments: $segments,
                longitudinalBarsCount: $longitudinalBarsCount,
                longitudinalDiameterMm: $longitudinalDiameterMm,
                longitudinalReservePercent: $longitudinalReservePercent,
                transverseDiameterMm: $transverseDiameterMm,
                transverseStepMm: $transverseStepMm,
                transverseReservePercent: $transverseReservePercent,
                pileType: $pileType,
                includePiles: $includePiles,
                pilesCount: $pilesCount,
                pileShaftDiameterM: $pileShaftDiameterM,
                pileShaftHeightM: $pileShaftHeightM,
                includePileBase: $includePileBase,
                pileBaseDiameterM: $pileBaseDiameterM,
                pileBaseHeightM: $pileBaseHeightM,
                includeGrillage: $includeGrillage,
                includePileReinforcement: $includePileReinforcement,
                pileReinforcementBarsCount: $pileReinforcementBarsCount,
                pileReinforcementDiameterMm: $pileReinforcementDiameterMm,
                pileReinforcementReservePercent: $pileReinforcementReservePercent,
                mixture: $mixture,
                useUnifiedConcreteMixtureSettings: $useUnifiedConcreteMixtureSettings,
                pileMixture: $pileMixture,
                grillageMixture: $grillageMixture,
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
                sandPricePerTonne: $sandPricePerTonne,
                tileTarget: $tileTarget,
                tileLengthMm: $tileLengthMm,
                tileWidthMm: $tileWidthMm,
                tileThicknessMm: $tileThicknessMm,
                tileJointMm: $tileJointMm,
                tileLayingPattern: $tileLayingPattern,
                tileOffsetPercent: $tileOffsetPercent,
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
                tilePricePerM2: $tilePricePerM2,
                drywallTarget: $drywallTarget,
                drywallSheetLengthMm: $drywallSheetLengthMm,
                drywallSheetWidthMm: $drywallSheetWidthMm,
                drywallSheetThicknessMm: $drywallSheetThicknessMm,
                drywallLayers: $drywallLayers,
                drywallFrameStepMm: $drywallFrameStepMm,
                drywallProfileWidthMm: $drywallProfileWidthMm,
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
