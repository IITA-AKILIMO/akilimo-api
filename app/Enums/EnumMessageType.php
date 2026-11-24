<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Enums;

use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulEnums;

/** Kinds of message the mobile app understands; each has its own notification channel there. */
enum EnumMessageType: string
{
    use UsefulEnums;

    case Advice = 'advice';
    case Reminder = 'reminder';
    case Announcement = 'announcement';

    /** Screens a message may open in the app (`target`); null means the type's default there. */
    public const array TARGETS = ['hub', 'recommendations', 'settings', 'inbox'];
}
