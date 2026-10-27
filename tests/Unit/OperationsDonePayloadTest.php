<?php

use App\Data\AkilimoComputeData;
use App\Data\ComputeRequestData;
use App\Data\UserInfoData;

/**
 * The payload for the compute service must carry what the farmer does now (ploughing, harrowing and
 * ridging). These values travel from the request to AkilimoComputeData by property name, and a
 * mismatch used to drop them silently.
 */
function computePayloadFor(array $operationsDone): array
{
    $payload = validComputePayload();
    $payload['compute_request']['operationsDone'] = $operationsDone;
    $payload['compute_request']['recommendations']['planting_practices_rec'] = true;

    $computeRequest = ComputeRequestData::from($payload['compute_request']);
    $userInfo = UserInfoData::from($payload['user_info']);

    return AkilimoComputeData::from($computeRequest->toArray(), $userInfo, ['fertilizers' => []])->toArray();
}

it('sends the operations the farmer does now to the compute service', function () {
    $payload = computePayloadFor(['ploughing_done' => true, 'harrowing_done' => false, 'ridging_done' => true]);

    expect($payload)->toHaveKeys(['ploughing', 'harrowing', 'ridging'])
        ->and($payload['ploughing'])->toBeTrue()
        ->and($payload['harrowing'])->toBeFalse()
        ->and($payload['ridging'])->toBeTrue();
});

it('sends false for operations that are not done instead of leaving them out', function () {
    $payload = computePayloadFor(['ploughing_done' => false, 'harrowing_done' => false, 'ridging_done' => false]);

    expect($payload['ploughing'])->toBeFalse()
        ->and($payload['harrowing'])->toBeFalse()
        ->and($payload['ridging'])->toBeFalse();
});
