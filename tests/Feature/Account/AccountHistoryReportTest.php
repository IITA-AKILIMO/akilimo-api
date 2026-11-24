<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\RecommendationReportService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    foreach (['read', 'write', 'sensitive'] as $limiter) {
        RateLimiter::clear("{$limiter}|127.0.0.1");
    }
    Storage::fake('local');
    config(['app.url' => 'https://api.example.test', 'akilimo-compute.reports.disk' => 'local']);
});

function storeReportFor(string $requestId): void
{
    app(RecommendationReportService::class)->store($requestId, [[
        'name' => 'fertilizer_advice.pdf',
        'content_type' => 'application/pdf',
        'content' => base64_encode("%PDF-1.4\n%%EOF"),
    ]]);
}

it('gives fresh signed links for the caller own saved report', function () {
    $me = makeFarmer();
    $token = farmerToken($me);
    $row = saveAdviceFor('d1', $me->id);
    storeReportFor($row->request_id);

    $response = $this->withToken($token)->getJson("/api/v1/account/history/{$row->request_id}/report")
        ->assertOk()
        ->assertJsonPath('data.id', $row->request_id)
        ->assertJsonPath('data.files.0.name', 'fertilizer_advice.pdf');

    expect($response->json('data.url'))->toStartWith('https://api.example.test/api/v1/recommendations/reports/')
        ->toContain('signature=')
        ->and($response->json('data.files.0.url'))->toBe($response->json('data.url'))
        ->and($response->json('data.expires_at'))->not->toBeNull();
});

it('answers 404 for another farmer, an anonymous or an unknown request, even when it has a report', function () {
    $token = farmerToken(makeFarmer());
    $theirs = saveAdviceFor('d2', makeFarmer()->id);
    $anonymous = saveAdviceFor('d3');
    storeReportFor($theirs->request_id);
    storeReportFor($anonymous->request_id);

    $this->withToken($token)->getJson("/api/v1/account/history/{$theirs->request_id}/report")->assertNotFound();
    $this->withToken($token)->getJson("/api/v1/account/history/{$anonymous->request_id}/report")->assertNotFound();
    $this->withToken($token)->getJson('/api/v1/account/history/' . str()->uuid() . '/report')->assertNotFound();
});

it('answers 404 for a deleted item', function () {
    $me = makeFarmer();
    $token = farmerToken($me);
    $row = saveAdviceFor('d1', $me->id);
    storeReportFor($row->request_id);

    $this->withToken($token)->deleteJson("/api/v1/account/history/{$row->request_id}")->assertOk();

    $this->withToken($token)->getJson("/api/v1/account/history/{$row->request_id}/report")->assertNotFound();
});

it('answers 404 when the saved advice has no report', function () {
    $me = makeFarmer();
    $row = saveAdviceFor('d1', $me->id);

    $this->withToken(farmerToken($me))->getJson("/api/v1/account/history/{$row->request_id}/report")->assertNotFound();
});

it('needs a farmer token with recorded consent', function () {
    $me = makeFarmer();
    $row = saveAdviceFor('d1', $me->id);
    storeReportFor($row->request_id);
    $url = "/api/v1/account/history/{$row->request_id}/report";

    $this->getJson($url)->assertUnauthorized();

    $unconsented = makeFarmer('long-enough-1', ['consent_at' => null]);
    $this->withToken(farmerToken($unconsented))->getJson($url)
        ->assertForbidden()->assertJsonPath('code', 'consent_required');
});
