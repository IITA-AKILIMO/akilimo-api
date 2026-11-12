<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\RecommendationReportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

const PDF_BYTES = "%PDF-1.4\n% AKILIMO test report\n%%EOF";

/** A report as the compute service returns it */
function reportEntry(string $name, string $bytes = PDF_BYTES): array
{
    return ['name' => $name, 'content_type' => 'application/pdf', 'content' => base64_encode($bytes)];
}

function bodyOf(?StreamedResponse $response): ?string
{
    if ($response === null) {
        return null;
    }
    ob_start();
    $response->sendContent();

    return (string)ob_get_clean();
}

beforeEach(function () {
    Storage::fake('local');
    config([
        'akilimo-compute.reports.disk' => 'local',
        'akilimo-compute.reports.keep_days' => 0,
        'app.url' => 'https://api.example.test',
    ]);
    $this->reports = app(RecommendationReportService::class);
    $this->id = (string)Str::uuid();
});

it('files each report flat, with the request id in the file name', function () {
    $stored = $this->reports->store($this->id, [reportEntry('fertilizer_advice.pdf'), reportEntry('PP_advice.pdf')]);

    expect($stored)->toBe(['fertilizer_advice.pdf', 'PP_advice.pdf'])      // the names the download links use
        ->and(Storage::disk('local')->allFiles('reports'))->toEqualCanonicalizing([
            "reports/fertilizer_advice-{$this->id}.pdf",
            "reports/pp_advice-{$this->id}.pdf",                             // one folder, no sub-folders
        ]);
});

it('skips what is not a known report or not a PDF', function () {
    $stored = $this->reports->store($this->id, [
        reportEntry('somebody_elses.pdf'),
        reportEntry('../../fertilizer_advice.pdf.sh'),
        reportEntry('fertilizer_advice.pdf', '<html>not a pdf</html>'),
        ['name' => 'intercrop_advice.pdf', 'content' => '!!!not base64'],
        reportEntry('SP_advice.pdf'),
    ]);

    expect($stored)->toBe(['SP_advice.pdf'])
        ->and(Storage::disk('local')->allFiles('reports'))->toBe(["reports/sp_advice-{$this->id}.pdf"]);
});

it('finds the files of a report by name, and links to them', function () {
    $this->reports->store($this->id, [reportEntry('SP_advice.pdf'), reportEntry('fertilizer_advice.pdf')]);
    $this->reports->store((string)Str::uuid(), [reportEntry('intercrop_advice.pdf')]);   // another request

    expect($this->reports->files($this->id))->toBe(['SP_advice.pdf', 'fertilizer_advice.pdf'])
        ->and($this->reports->files((string)Str::uuid()))->toBe([])
        ->and($this->reports->files('not-a-uuid'))->toBe([]);

    $links = $this->reports->links($this->id);
    expect($links['id'])->toBe($this->id)
        ->and(array_column($links['files'], 'name'))->toBe(['SP_advice.pdf', 'fertilizer_advice.pdf'])
        ->and($links['url'])->toContain("/reports/{$this->id}/SP_advice.pdf")
        ->toContain('signature=')
        ->and($this->reports->links((string)Str::uuid()))->toBeNull();
});

it('makes links from the file names it is given, without asking the store', function () {
    // nothing is stored: the names come from store(), so there is nothing to look up
    $links = $this->reports->links($this->id, ['fertilizer_advice.pdf']);

    expect($links['files'])->toHaveCount(1)
        ->and($links['url'])->toContain("/reports/{$this->id}/fertilizer_advice.pdf");
});

it('downloads a report, whatever the case of the name in an old link', function () {
    $this->reports->store($this->id, [reportEntry('PP_advice.pdf', PDF_BYTES . "\npp")]);

    expect(bodyOf($this->reports->download($this->id, 'PP_advice.pdf')))->toBe(PDF_BYTES . "\npp")
        ->and(bodyOf($this->reports->download($this->id, 'pp_advice.pdf')))->toBe(PDF_BYTES . "\npp")
        ->and($this->reports->download($this->id, 'SP_advice.pdf'))->toBeNull()          // that one was not made
        ->and($this->reports->download($this->id, 'notes.txt'))->toBeNull()
        ->and($this->reports->download($this->id, '../x.pdf'))->toBeNull()
        ->and($this->reports->download((string)Str::uuid(), 'PP_advice.pdf'))->toBeNull() // another request's id
        ->and($this->reports->download('not-a-uuid', 'PP_advice.pdf'))->toBeNull();
});

it('names the download for a farmer, with the day it was made', function () {
    $this->reports->store($this->id, [reportEntry('fertilizer_advice.pdf')]);

    expect($this->reports->downloadName($this->id, 'fertilizer_advice.pdf'))
        ->toBe('akilimo-fertilizer-advice-' . now()->toDateString() . '.pdf')
        ->and($this->reports->downloadName($this->id, 'PP_advice.pdf'))->toContain('planting-practices-advice');
});

it('still reads reports kept the old way, one folder per report', function () {
    Storage::disk('local')->put("reports/{$this->id}/fertilizer_advice.pdf", PDF_BYTES . "\nold");

    expect($this->reports->files($this->id))->toBe(['fertilizer_advice.pdf'])
        ->and(bodyOf($this->reports->download($this->id, 'fertilizer_advice.pdf')))->toBe(PDF_BYTES . "\nold")
        ->and($this->reports->links($this->id)['files'])->toHaveCount(1);

    // and a report made again goes to the new layout, which is the one that is read from then on
    $this->reports->store($this->id, [reportEntry('fertilizer_advice.pdf', PDF_BYTES . "\nnew")]);
    expect(bodyOf($this->reports->download($this->id, 'fertilizer_advice.pdf')))->toBe(PDF_BYTES . "\nnew")
        ->and(Storage::disk('local')->exists("reports/fertilizer_advice-{$this->id}.pdf"))->toBeTrue();
});

it('forgets a report in either layout', function () {
    $other = (string)Str::uuid();
    $this->reports->store($this->id, [reportEntry('fertilizer_advice.pdf'), reportEntry('SP_advice.pdf')]);
    $this->reports->store($other, [reportEntry('fertilizer_advice.pdf')]);
    Storage::disk('local')->put("reports/{$this->id}/intercrop_advice.pdf", PDF_BYTES);

    $this->reports->forget($this->id);
    $this->reports->forget('not-a-uuid');   // ignored

    expect($this->reports->files($this->id))->toBe([])
        ->and(Storage::disk('local')->allFiles('reports'))->toBe(["reports/fertilizer_advice-{$other}.pdf"]);  // the other one stays
});

it('keeps reports by default and prunes them by age when keep_days is set', function () {
    $old = (string)Str::uuid();
    $this->reports->store($this->id, [reportEntry('fertilizer_advice.pdf'), reportEntry('SP_advice.pdf')]);
    Storage::disk('local')->put("reports/{$old}/fertilizer_advice.pdf", PDF_BYTES);    // the old layout
    Storage::disk('local')->put('reports/notes.txt', 'not a report');                   // not the service's
    Storage::disk('local')->put('reports/_storage-check-' . Str::uuid() . '.txt', 'x'); // the check command's

    $this->travel(400)->days();
    expect($this->reports->prune())->toBe(0);                                          // keep_days is 0: nothing goes

    config(['akilimo-compute.reports.keep_days' => 30]);
    $this->travelBack();
    expect($this->reports->prune())->toBe(0);                                          // all of it is new

    $this->travel(31)->days();
    expect($this->reports->prune())->toBe(2)                                           // two reports (3 files), not 3
        ->and($this->reports->files($this->id))->toBe([])
        ->and($this->reports->files($old))->toBe([])
        ->and(Storage::disk('local')->allFiles('reports'))->toHaveCount(2);            // notes.txt and the check file stay
});

it('keeps a storage name stable: the kind in lower case, then the request id', function () {
    // the file name is the contract with what is already in a bucket: change it only with a migration
    $this->reports->store($this->id, [reportEntry('SP_advice.pdf')]);

    expect(Storage::disk('local')->allFiles('reports'))->toBe(["reports/sp_advice-{$this->id}.pdf"])
        ->and(RecommendationReportService::FILES)->toBe(['fertilizer_advice.pdf', 'intercrop_advice.pdf', 'PP_advice.pdf', 'SP_advice.pdf']);
});
