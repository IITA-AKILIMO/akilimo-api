<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Data\AkilimoComputeData;
use App\Data\ComputeRequestData;
use App\Data\FallowData;
use App\Data\InterCroppingData;
use App\Data\OperationsDoneData;
use App\Data\RecommendationsData;
use App\Data\TractorCostsData;
use App\Data\UserInfoData;
use App\Data\YieldInfoData;

/**
 * A boolean the client leaves out must reach the compute service as false. A property without a default stays
 * uninitialised and is dropped from the payload, and the service (R) then fails with
 * "missing value where TRUE/FALSE needed" (a 500).
 */
it('defaults every boolean of the request sections to false', function (string $class) {
    $missing = [];

    foreach ((new ReflectionClass($class))->getProperties() as $property) {
        if ((string)$property->getType() === 'bool' && !$property->hasDefaultValue()) {
            $missing[] = $property->getName();
        }
    }

    expect($missing)->toBe([], "{$class} has booleans without a default: " . implode(', ', $missing));
})->with([
    FallowData::class,
    InterCroppingData::class,
    OperationsDoneData::class,
    RecommendationsData::class,
    TractorCostsData::class,
    UserInfoData::class,
    YieldInfoData::class,
]);

it('sends every boolean to the compute service as false when the request leaves them out', function () {
    $payload = validComputePayload();
    unset(
        $payload['compute_request']['fallow']['fallow_green'],
        $payload['compute_request']['tractorCosts']['tractor_plough'],
        $payload['compute_request']['tractorCosts']['tractor_harrow'],
        $payload['compute_request']['tractorCosts']['tractor_ridger'],
        $payload['compute_request']['operationsDone']['ploughing_done'],
        $payload['compute_request']['operationsDone']['harrowing_done'],
        $payload['compute_request']['operationsDone']['ridging_done'],
        $payload['compute_request']['yieldInfo']['sell_to_starch_factory'],
        $payload['user_info']['send_sms'],
    );

    $computeRequest = ComputeRequestData::from($payload['compute_request']);
    $userInfo = UserInfoData::from($payload['user_info']);
    $sent = AkilimoComputeData::from($computeRequest->toArray(), $userInfo, ['fertilizers' => []])->toArray();

    foreach (['fallowGreen', 'tractor_plough', 'tractor_harrow', 'tractor_ridger', 'ploughing', 'harrowing', 'ridging', 'saleSF', 'SMS'] as $key) {
        expect($sent)->toHaveKey($key)
            ->and($sent[$key])->toBeFalse();
    }
});
