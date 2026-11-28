<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

/**
 * Notices pushed to farmers when an admin changes something they rely on (prices, the fertilizer list, starch factories
 * and prices, the app version). Every push goes through the queue (see App\Jobs\PushMessage); a change only queues a
 * delayed, de-duplicated notice job, so a bulk edit sends one notice per country and kind, not one per row.
 */
return [
    'enabled' => (bool)env('FARMER_NOTIFICATIONS_ENABLED', true),

    // Changes made by artisan commands, seeders and migrations never notify farmers, unless this is on (tests turn it on)
    'in_console' => (bool)env('FARMER_NOTIFICATIONS_IN_CONSOLE', false),

    // One message per language, written in lang/<code>/farmer_notice.php
    'languages' => ['en', 'sw', 'rw'],

    // Wait this long after the first change before the notice goes out, so a batch of edits is one notice
    'debounce_minutes' => (int)env('FARMER_NOTIFICATIONS_DEBOUNCE_MINUTES', 10),

    // A notice stops being fetched by the sync after this many days
    'expires_days' => (int)env('FARMER_NOTIFICATIONS_EXPIRES_DAYS', 14),
];
