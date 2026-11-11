<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Models\ApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

const FAKE_PDF = "%PDF-1.4\n% AKILIMO test report\n%%EOF";

function plumberResponseWithReport(string $name = 'fertilizer_advice.pdf', string $bytes = FAKE_PDF): array
{
    return [
        ...plumberSuccessResponse(),
        'reports' => [['name' => $name, 'content_type' => 'application/pdf', 'content' => base64_encode($bytes)]],
    ];
}

/** Path and query of a report link, so the test client calls it on its own host */
function reportPath(string $url): string
{
    $parts = parse_url($url);

    return $parts['path'] . '?' . $parts['query'];
}

beforeEach(function () {
    $this->seed(\Database\Seeders\FertilizerSeeder::class);
    Storage::fake('local');
    Cache::flush();
    config(['app.url' => 'https://api.example.test']);
});

it('asks the compute service for the PDF and answers with a signed link', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);

    $response = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    Http::assertSent(fn ($request) => $request['returnPdf'] === true);
    expect($response->json('report.url'))->toStartWith('https://api.example.test/api/v1/recommendations/reports/')
        ->toContain('signature=')
        ->and($response->json('report.files.0.name'))->toBe('fertilizer_advice.pdf')
        ->and($response->json('report.expires_at'))->not->toBeNull();

    $download = $this->get(reportPath($response->json('report.url')))->assertOk();
    expect($download->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($download->headers->get('Content-Disposition'))->toContain('akilimo-fertilizer-advice-' . now()->toDateString() . '.pdf')
        ->and($download->streamedContent())->toBe(FAKE_PDF);
});

it('keeps the PDF out of the request log', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(json_encode(ApiRequest::latest('id')->first()->plumber_response))->not->toContain('reports')
        ->not->toContain(base64_encode(FAKE_PDF));
});

it('refuses a link with a changed or missing signature', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $url = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report.url');

    $this->get(str_replace('signature=', 'signature=0', reportPath($url)))->assertForbidden();
    $this->get(parse_url($url, PHP_URL_PATH))->assertForbidden();
});

it('refuses a link after it expires', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $url = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report.url');

    $this->travel(8)->days();

    $this->get(reportPath($url))->assertForbidden();
});

it('gives a cached report to the same farmer but not to another one', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $payload = validComputePayload();

    $first = $this->postJson('/api/v1/recommendations/compute', $payload)->json('report.url');
    $again = $this->postJson('/api/v1/recommendations/compute', $payload)->json('report.url');

    $other = $payload;
    $other['user_info']['first_name'] = 'Someone';
    $other['user_info']['last_name'] = 'Else';
    $otherReport = $this->postJson('/api/v1/recommendations/compute', $other)->assertOk()->json('report');

    Http::assertSentCount(1);
    expect($first)->not->toBeNull()
        ->and($again)->not->toBeNull()
        ->and($otherReport)->toBeNull();
});

it('answers without a report when the compute service sends none or a bad one', function (array $reports) {
    Http::fake(['*' => Http::response([...plumberSuccessResponse(), 'reports' => $reports], 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())
        ->assertOk()
        ->assertJsonPath('report', null)
        ->assertJsonPath('data.rec_type', 'FR');
})->with([
    'none' => [[]],
    'not a PDF' => [[['name' => 'a.pdf', 'content' => base64_encode('<html>')]]],
    'path in the name' => [[['name' => '../../etc/x.sh', 'content' => base64_encode(FAKE_PDF)]]],
    'bad base64' => [[['name' => 'a.pdf', 'content' => '!!!']]],
]);

it('does not ask for the PDF when reports are switched off', function () {
    config(['akilimo-compute.reports.enabled' => false]);
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk()->assertJsonPath('report', null);

    Http::assertSent(fn ($request) => $request['returnPdf'] === false);
});

it('keeps reports by default, like the recommendation history', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    $this->travel(400)->days();
    $this->artisan('reports:prune')->assertSuccessful();

    expect(Storage::disk('local')->directories('reports'))->toHaveCount(1);
});

it('prunes reports older than keep_days when it is set', function () {
    config(['akilimo-compute.reports.keep_days' => 30]);
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    $this->artisan('reports:prune')->assertSuccessful();
    expect(Storage::disk('local')->directories('reports'))->toHaveCount(1);

    $this->travel(31)->days();
    $this->artisan('reports:prune')->assertSuccessful();
    expect(Storage::disk('local')->directories('reports'))->toHaveCount(0);
});

it('files the report under the request_id of the logged computation', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);

    $id = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report.id');

    expect($id)->toBe(ApiRequest::latest('id')->first()->request_id);
});

it('renews the link of a stored report after the old one expired', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $report = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report');

    $this->travel(60)->days();
    $this->get(reportPath($report['url']))->assertForbidden();

    $fresh = $this->actingAsApiUser()->getJson("/api/v1/recommendations/reports/{$report['id']}")
        ->assertOk()
        ->assertJsonPath('data.id', $report['id'])
        ->assertJsonPath('data.files.0.name', 'fertilizer_advice.pdf')
        ->json('data');

    expect($this->get(reportPath($fresh['url']))->assertOk()->streamedContent())->toBe(FAKE_PDF);
});

it('renews links only for a key with reports:read', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $id = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report.id');

    $this->getJson("/api/v1/recommendations/reports/{$id}")->assertUnauthorized();

    $user = User::create(['name' => 'N', 'username' => 'nokey_' . str()->random(5), 'email' => str()->random(6) . '@example.com', 'password' => bcrypt('x')]);
    $raw = 'ak_' . bin2hex(random_bytes(16));
    $user->apiKeys()->create(['name' => 'compute only', 'key_prefix' => substr($raw, 0, 12), 'key_hash' => hash('sha256', $raw), 'abilities' => [TokenAbility::RECOMMENDATIONS_COMPUTE], 'is_active' => true]);
    $this->withHeader('X-Api-Key', $raw)->getJson("/api/v1/recommendations/reports/{$id}")->assertForbidden();
});

it('answers 404 for links of a report that does not exist', function () {
    $this->actingAsApiUser()->getJson('/api/v1/recommendations/reports/' . Str::uuid())->assertNotFound();
});

it('sends the request_id to the compute service as the report reference', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);

    $id = $this->postJson('/api/v1/recommendations/compute', validComputePayload())->json('report.id');

    Http::assertSent(fn ($request) => $request['requestId'] === $id);
});

it('lets an admin make a request report again from its stored payload', function () {
    Http::fakeSequence()
        ->push(plumberResponseWithReport(), 200)
        ->push(plumberResponseWithReport('fertilizer_advice.pdf', FAKE_PDF . "\n% again"), 200);
    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();
    $row = ApiRequest::latest('id')->first();
    Storage::disk('local')->deleteDirectory('reports/' . $row->request_id);

    $admin = User::create(['name' => 'A', 'username' => 'adm_' . str()->random(5), 'email' => str()->random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Admin]);

    $this->actingAs($admin)->post("/admin/requests/{$row->id}/report")->assertRedirect()->assertSessionHas('success');

    Http::assertSent(fn ($request) => $request['returnPdf'] === true && $request['email'] === false
        && $request['requestId'] === $row->request_id && !array_key_exists('fertilizers', $request->data()));
    expect(Storage::disk('local')->get('reports/' . $row->request_id . '/fertilizer_advice.pdf'))->toBe(FAKE_PDF . "\n% again");
});

it('does not let a partner make reports again', function () {
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
    $this->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();
    $row = ApiRequest::latest('id')->first();
    $partner = User::create(['name' => 'P', 'username' => 'prt_' . str()->random(5), 'email' => str()->random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Partner]);

    $this->actingAs($partner)->post("/admin/requests/{$row->id}/report")->assertRedirect()->assertSessionMissing('success');

    Http::assertSentCount(1);
});
