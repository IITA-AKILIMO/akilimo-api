<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** A one-time code for a farmer account: confirming the email address or resetting the password. */
class AccountCodeMail extends Mailable
{
    public const VERIFY = 'verification';

    public const RESET = 'password reset';

    public function __construct(
        public readonly string $purpose,
        public readonly string $code,
        public readonly int $minutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your AKILIMO {$this->purpose} code");
    }

    public function content(): Content
    {
        return new Content(text: 'mail.account-code');
    }
}
