<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Mail\RecommendationReportMail;
use Database\Seeders\FertilizerSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/** A compute payload for a farmer who asked for the advice by email */
function emailPayload(array $user = [], string $lang = 'en'): array
{
    $payload = validComputePayload();
    $payload['user_info'] = array_merge($payload['user_info'], ['send_email' => true, 'email_address' => 'farmer@example.test'], $user);
    $payload['compute_request']['recommendations']['lang'] = $lang;

    return $payload;
}

beforeEach(function () {
    $this->seed(FertilizerSeeder::class);
    Storage::fake('local');
    Cache::flush();
    Mail::fake();
    config([
        'app.url' => 'https://api.example.test',
        'akilimo-compute.reports.disk' => 'local',
        'mail.default' => 'smtp',
    ]);
    Http::fake(['*' => Http::response(plumberResponseWithReport(), 200)]);
});

it('emails the PDF to the address the farmer gave', function () {
    $this->postJson('/api/v1/recommendations/compute', emailPayload())->assertOk();

    Mail::assertQueued(RecommendationReportMail::class, fn (RecommendationReportMail $mail) => $mail->hasTo('farmer@example.test')
        && $mail->lang === 'en'
        && count($mail->files) === 1
        && str_starts_with($mail->files[0]['name'], 'akilimo-fertilizer-advice-')
        && Storage::disk('local')->exists($mail->files[0]['path']));
    Mail::assertQueuedCount(1);
});

it('tells the compute service not to send an email of its own', function () {
    $this->postJson('/api/v1/recommendations/compute', emailPayload())->assertOk();

    Http::assertSent(fn ($request) => $request['email'] === false && $request['returnPdf'] === true);
});

it('writes the mail in Swahili when the advice is in Swahili, and in English for other languages', function () {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(lang: 'sw'))->assertOk();
    Mail::assertQueued(RecommendationReportMail::class, fn (RecommendationReportMail $mail) => $mail->lang === 'sw'
        && $mail->envelope()->subject === 'Mapendekezo ya AKILIMO');

    expect(RecommendationReportMail::languageFor('rw'))->toBe('en')
        ->and(RecommendationReportMail::languageFor(null))->toBe('en');
});

it('sends nothing unless the farmer asked for it', function () {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(['send_email' => false]))->assertOk();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

it('sends nothing without a valid address', function (?string $address) {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(['email_address' => $address]))->assertOk();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
})->with([null, '', 'not-an-address']);

it('sends nothing when no report was made', function () {
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);

    $this->postJson('/api/v1/recommendations/compute', emailPayload())->assertOk();

    Mail::assertNothingQueued();
});

it('sends nothing when the email switch is off', function () {
    config(['akilimo-compute.reports.email' => false]);

    $this->postJson('/api/v1/recommendations/compute', emailPayload())->assertOk();

    Mail::assertNothingQueued();
});

it('sends nothing in production while the mailer is the log', function () {
    config(['mail.default' => 'log']);
    app()->detectEnvironment(fn () => 'production');

    $this->postJson('/api/v1/recommendations/compute', emailPayload())->assertOk();

    Mail::assertNothingQueued();
});

it('still answers when the mail cannot be queued', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('mail service down'));

    $this->postJson('/api/v1/recommendations/compute', emailPayload())
        ->assertOk()
        ->assertJsonPath('report.files.0.name', 'fertilizer_advice.pdf');
});
