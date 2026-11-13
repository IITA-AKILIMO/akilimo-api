<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Data;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

#[MapOutputName(SnakeCaseMapper::class)]
class AkilimoComputeData extends Data
{
    /**
     * CMP: Crop Current Maize Performance.
     */
    #[MapOutputName('CMP')]
    public float $currentMaizePerformance;

    #[MapOutputName('lang')]
    public string $recommendationLanguage;

    // The yield class in t/ha (3.75, 11.25, ...): a float, an int would cut 11.25 to 11
    #[MapOutputName('FCY')]
    public float $currentFieldYield;

    #[MapOutputName('FR')]
    public bool $fertilizerRec;

    #[MapOutputName('IC')]
    public bool $interCropRec;

    #[MapOutputName('PP')]
    public bool $plantingPracticesRec;

    #[MapOutputName('SPP')]
    public bool $scheduledPlantingRec;

    #[MapOutputName('SPH')]
    public bool $scheduledHarvestRec;

    #[MapOutputName('PD')]
    #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
    public Carbon $plantingDate;

    #[MapOutputName('PD_window')]
    public int $plantingDateWindow;

    #[MapOutputName('HD')]
    #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
    public Carbon $harvestDate;

    #[MapOutputName('HD_window')]
    public int $harvestDateWindow;

    #[MapOutputName('area')]
    public float $fieldSize;

    #[MapOutputName('areaUnits')]
    public string $areaUnit;

    #[MapOutputName('cassPD')]
    public string $cassavaProduceType;

    #[MapOutputName('cassUP')]
    public float $cassavaUnitPrice;

    #[MapOutputName('cassUP_m1')]
    public float $cassUpM1;

    #[MapOutputName('cassUP_m2')]
    public float $cassUpM2;

    #[MapOutputName('cassUP_p1')]
    public float $cassUpP1;

    #[MapOutputName('cassUP_p2')]
    public float $cassUpP2;

    #[MapOutputName('cassUW')]
    public float $cassavaUnitWeight;

    #[MapOutputName('cost_LMO_areaBasis')]
    public string $costLmoAreaBasis;

    public float $costManualHarrowing;

    public float $costManualPloughing;

    public float $costManualRidging;

    public float $costTractorHarrowing;

    public float $costTractorPloughing;

    public float $costTractorRidging;

    #[MapOutputName('cost_weeding1')]
    public float $costWeedingOne;

    #[MapOutputName('cost_weeding2')]
    public float $costWeedingTwo;

    #[MapOutputName('country')]
    public string $countryCode;

    #[MapOutputName('email')]
    public bool $sendEmail;

    #[MapOutputName('SMS')]
    public bool $sendSms;

    /** The API's request_id (api_requests); printed on the PDF report as its reference */
    #[MapOutputName('requestId')]
    public ?string $requestId = null;

    /** Ask the compute service to send the PDF reports back (stored by RecommendationReportService) */
    #[MapOutputName('returnPdf')]
    public bool $returnPdf = false;

    #[MapOutputName('fallowGreen')]
    public bool $fallowGreen;

    #[MapOutputName('fallowHeight')]
    public int $fallowHeight;

    #[MapOutputName('fallowType')]
    public string $fallowType;

    #[MapOutputName('harrowing')]
    public bool $harrowing;

    #[MapOutputName('intercrop')]
    public ?string $interCroppedCrop;

    #[MapOutputName('lat')]
    public float $mapLat;

    #[MapOutputName('lon')]
    public float $mapLong;

    #[MapOutputName('maizePD')]
    public string $maizeProduceType;

    #[MapOutputName('maizeUP')]
    public float $maizeUnitPrice;

    #[MapOutputName('maizeUW')]
    public float $maizeUnitWeight;

    #[MapOutputName('sweetPotatoPD')]
    public string $sweetPotatoProduceType;

    #[MapOutputName('sweetPotatoUW')]
    public float $sweetPotatoUnitWeight;

    #[MapOutputName('sweetPotatoUP')]
    public float $sweetPotatoUnitPrice;

    #[MapOutputName('maxInv')]
    public float $maxInvestment;

    #[MapOutputName('method_harrowing')]
    public ?string $methodHarrowing;

    #[MapOutputName('method_ploughing')]
    public ?string $methodPloughing;

    #[MapOutputName('method_ridging')]
    public ?string $methodRidging;

    #[MapOutputName('nameSF')]
    public ?string $starchFactoryName;

    #[MapOutputName('ploughing')]
    public bool $ploughing;

    #[MapOutputName('problemWeeds')]
    public bool $problemWeeds;

    #[MapOutputName('ridging')]
    public bool $ridging;

    #[MapOutputName('riskAtt')]
    public int $riskAttitude;

    #[MapOutputName('saleSF')]
    public bool $sellToStarchFactory;

    #[MapOutputName('tractor_harrow')]
    public bool $tractorHarrow;

    #[MapOutputName('tractor_plough')]
    public bool $tractorPlough;

    #[MapOutputName('tractor_ridger')]
    public bool $tractorRidger;

    #[MapOutputName('userEmail')]
    public ?string $emailAddress;

    #[MapOutputName('userField')]
    public ?string $farmName = null;

    #[MapOutputName('userName')]
    public string $userName;

    #[MapOutputName('userPhoneCC')]
    public string $phoneCountryCode;

    #[MapOutputName('userPhoneNr')]
    public ?string $phoneNumber;

    /**
     * Fertilizer availability, weight, and price data keyed by the Plumbr-expected field name
     * (e.g. ['ureaavailable' => true, 'ureaBagWt' => 50, 'ureaCostperBag' => 12000.0]).
     *
     * Populated by RecommendationService::mapFertilizersToExternalFormat() and flattened
     * into the top-level payload by toArray(), so the Plumbr API contract is preserved exactly.
     * Adding a new fertilizer type only requires a new entry in the fertilizer table — no DTO changes are needed.
     */
    public array $fertilizers = [];

    /**
     * Merges the fertilizer key-value pairs directly into the Plumbr payload so they appear
     * at the top level rather than nested under a 'fertilizers' key.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $fertilizers = $data['fertilizers'] ?? [];
        unset($data['fertilizers']);

        return array_merge($data, $fertilizers);
    }
}
