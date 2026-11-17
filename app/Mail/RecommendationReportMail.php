<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The PDF advice of a recommendation, sent to the address the farmer gave. Queued, so a slow mail service never
 * holds back the recommendation. The PDFs are read from the reports disk when the mail is sent; the queue carries
 * only their paths, never the files or the farmer's details beyond the address.
 */
class RecommendationReportMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** Languages the mail is written in; anything else is sent in English */
    public const array LANGUAGES = ['en', 'sw'];

    /**
     * @param string $disk the reports disk
     * @param array<int, array{path: string, name: string}> $files stored PDFs and the names to attach them as
     */
    public function __construct(
        public readonly string $disk,
        public readonly array $files,
        public readonly string $lang = 'en',
    ) {
        // Renders the subject and body in the farmer's language (falls back to English via app.fallback_locale)
        $this->locale($lang);
    }

    public static function languageFor(?string $lang): string
    {
        $lang = strtolower(trim((string)$lang));

        return in_array($lang, self::LANGUAGES, true) ? $lang : 'en';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('report_mail.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.recommendation-report', with: ['body' => __('report_mail.body')]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return array_map(
            fn (array $file) => Attachment::fromStorageDisk($this->disk, $file['path'])->as($file['name'])->withMime('application/pdf'),
            $this->files,
        );
    }
}
