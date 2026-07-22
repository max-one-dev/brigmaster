<?php

declare(strict_types=1);

namespace Brigmaster\Http\Rest;

use Brigmaster\Application\EstimateService;
use Brigmaster\Domain\DTO\EstimateInput;
use InvalidArgumentException;
use WP_REST_Request;
use WP_REST_Response;

final class EstimateController
{
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

    public function __construct(
        private readonly EstimateService $estimateService
    ) {
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

        $errors = $this->validateRequest(
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

    /**
     * @return array<string, array<int, string>>
     */
    private function validateRequest(
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

    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function isNumericValue(mixed $value): bool
    {
        return (is_string($value) && trim($value) !== '' && is_numeric($value)) || is_int($value) || is_float($value);
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
