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

/** What the compute service answers with when it also returns a PDF report */
function emailReportResponse(): array
{
    return [
        ...plumberSuccessResponse(),
        'reports' => [[
            'name' => 'fertilizer_advice.pdf',
            'content_type' => 'application/pdf',
            'content' => base64_encode("%PDF-1.4\n% AKILIMO email test\n%%EOF"),
        ]],
    ];
}

/** A compute payload for a farmer who gave an email address */
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
    // what the compute service answers with; a test changes it to answer differently
    $this->computeAnswer = emailReportResponse();
    Http::fake(fn () => Http::response($this->computeAnswer, 200));
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
    Mail::assertQueued(RecommendationReportMail::class, fn (RecommendationReportMail $mail) => $mail->lang === 'sw' && $mail->locale === 'sw');

    expect(__('report_mail.subject', [], 'sw'))->toBe('Mapendekezo ya AKILIMO')
        ->and(__('report_mail.subject', [], 'en'))->toBe('AKILIMO recommendation')
        ->and(RecommendationReportMail::languageFor('rw'))->toBe('en')
        ->and(RecommendationReportMail::languageFor(null))->toBe('en');
});

it('emails the PDF whenever an address is given, whatever the send_email flag says', function (bool $flag) {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(['send_email' => $flag]))->assertOk();

    Mail::assertQueued(RecommendationReportMail::class, fn (RecommendationReportMail $mail) => $mail->hasTo('farmer@example.test'));
    Mail::assertQueuedCount(1);
})->with([true, false]);
it('sends nothing without a valid address', function (?string $address) {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(['email_address' => $address]))->assertOk();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
})->with([null, '']);

it('refuses a request whose email address is not valid, before anything is sent', function () {
    $this->postJson('/api/v1/recommendations/compute', emailPayload(['email_address' => 'not-an-address']))->assertStatus(422);

    Mail::assertNothingQueued();
});

it('sends nothing when no report was made', function () {
    $this->computeAnswer = plumberSuccessResponse();

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
