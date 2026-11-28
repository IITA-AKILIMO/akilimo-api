<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Enums;

/**
 * What became of a message's push (messages.push_status). Set when the message is stored and updated by the queue job
 * that does the push, so it always reflects the real outcome and not only that a push was queued.
 */
enum PushStatus: string
{
    /** A push job is waiting for, or running on, a queue worker */
    case Queued = 'queued';

    /** FCM accepted the push (for a private message: for at least one phone) */
    case Pushed = 'pushed';

    /** The push job used all its attempts and FCM never accepted it */
    case Failed = 'failed';

    /** No push was or can be sent: FCM off, no audience or registered phone, the message expired, the phone is gone */
    case NotPushed = 'not_pushed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Pushed => 'Pushed',
            self::Failed => 'Failed',
            self::NotPushed => 'Not pushed',
        };
    }
}
