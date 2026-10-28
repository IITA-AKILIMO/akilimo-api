<?php

namespace App\Data;

namespace App\Data;

use App\Http\Enums\EnumAreaUnit;
use Spatie\LaravelData\Data;

// #[MapInputName(SnakeCaseMapper::class)]
class ComputeRequestData extends Data
{
    public FarmInformationData $farmInformation;

    public InterCroppingData $interCropping;

    public RecommendationsData $recommendations;

    public PlantingData $planting;

    public FallowData $fallow;

    public TractorCostsData $tractorCosts;

    public ManualCostsData $manualCosts;

    public WeedingCostsData $weedingCosts;

    public OperationsDoneData $operationsDone;

    public FarmMethodsData $methods;

    public YieldInfoData $yieldInfo;

    public CassavaData $cassava;

    public MaizeData $maize;

    public SweetPotatoData $sweetPotato;

    public float $maxInvestment;

    public function toArray(): array
    {
        return [
            // FarmInformation
            'countryCode' => $this->farmInformation->countryCode,
            'useCase' => $this->farmInformation->useCase,
            'mapLat' => $this->farmInformation->mapLat,
            'mapLong' => $this->farmInformation->mapLong,
            'fieldSize' => $this->farmInformation->fieldSize,
            'areaUnit' => EnumAreaUnit::normalize($this->farmInformation->areaUnit)->value,

            // InterCropping
            'interCroppedCrop' => $this->interCropping->interCroppedCrop,
            'interCroppingMaizeRec' => $this->interCropping->interCroppingMaizeRec,
            'interCroppingPotatoRec' => $this->interCropping->interCroppingPotatoRec,
            'interCropRec' => $this->interCropping->isRecommended(),

            // Recommendations
            'recommendationLanguage' => $this->recommendations->lang,
            'fertilizerRec' => $this->recommendations->fertilizerRec,
            'plantingPracticesRec' => $this->recommendations->plantingPracticesRec,
            'scheduledPlantingRec' => $this->recommendations->scheduledPlantingRec,
            'scheduledHarvestRec' => $this->recommendations->scheduledHarvestRec,

            // Planting
            'plantingDate' => $this->planting->plantingDate->toDateString(),
            'plantingDateWindow' => $this->planting->plantingDateWindow,
            'harvestDate' => $this->planting->harvestDate->toDateString(),
            'harvestDateWindow' => $this->planting->harvestDateWindow,

            // Fallow
            'fallowType' => $this->fallow->fallowType,
            'fallowHeight' => $this->fallow->fallowHeight,
            'fallowGreen' => $this->fallow->fallowGreen,

            // TractorCosts
            'tractorPlough' => $this->tractorCosts->tractorPlough,
            'tractorHarrow' => $this->tractorCosts->tractorHarrow,
            'tractorRidger' => $this->tractorCosts->tractorRidger,
            'costLmoAreaBasis' => $this->tractorCosts->costLmoAreaBasis,
            'costTractorPloughing' => $this->tractorCosts->costTractorPloughing,
            'costTractorHarrowing' => $this->tractorCosts->costTractorHarrowing,
            'costTractorRidging' => $this->tractorCosts->costTractorRidging,

            // ManualCosts
            'costManualPloughing' => $this->manualCosts->costManualPloughing,
            'costManualHarrowing' => $this->manualCosts->costManualHarrowing,
            'costManualRidging' => $this->manualCosts->costManualRidging,

            // WeedingCosts
            'costWeedingOne' => $this->weedingCosts->costWeedingOne,
            'costWeedingTwo' => $this->weedingCosts->costWeedingTwo,

            // OperationsDone: AkilimoComputeData names these ploughing, harrowing and ridging (the keys
            // the compute service reads). Under the old *Done names nothing matched them, so the
            // properties stayed uninitialised and were left out of the payload without any error.
            'ploughing' => $this->operationsDone->ploughingDone,
            'harrowing' => $this->operationsDone->harrowingDone,
            'ridging' => $this->operationsDone->ridgingDone,

            // Methods
            'methodPloughing' => $this->methods->methodPloughing,
            'methodHarrowing' => $this->methods->methodHarrowing,
            'methodRidging' => $this->methods->methodRidging,
            'methodWeeding' => $this->methods->methodWeeding,

            // YieldInfo
            'currentFieldYield' => $this->yieldInfo->currentFieldYield,
            'currentMaizePerformance' => $this->yieldInfo->currentMaizePerformance,
            'sellToStarchFactory' => $this->yieldInfo->sellToStarchFactory,
            'starchFactoryName' => $this->yieldInfo->starchFactoryName,

            // Cassava
            'cassavaProduceType' => $this->cassava->produceType,
            'cassavaUnitWeight' => $this->cassava->unitWeight,
            'cassavaUnitPrice' => $this->cassava->unitPrice,
            'cassUpM1' => $this->cassava->unitPriceMaize1,
            'cassUpM2' => $this->cassava->unitPriceMaize2,
            'cassUpP1' => $this->cassava->unitPricePotato1,
            'cassUpP2' => $this->cassava->unitPricePotato2,

            // Maize
            'maizeProduceType' => $this->maize->produceType,
            'maizeUnitWeight' => $this->maize->unitWeight,
            'maizeUnitPrice' => $this->maize->unitPrice,

            // SweetPotato
            'sweetPotatoProduceType' => $this->sweetPotato->produceType,
            'sweetPotatoUnitWeight' => $this->sweetPotato->unitWeight,
            'sweetPotatoUnitPrice' => $this->sweetPotato->unitPrice,

            // Top-level
            'maxInvestment' => $this->maxInvestment,
        ];
    }
}
