<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Jobs\SendChangeNotice;
use App\Repositories\CountryRepo;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells farmers about changes an admin made to what they rely on. A change only queues a delayed, de-duplicated
 * {@see SendChangeNotice} job (a bulk edit is one notice per kind and country); the job then stores one message per language
 * through {@see MessageService}, whose push is queued again. Nothing here talks to FCM.
 */
class FarmerNotifier
{
    /** Kinds of notice, each with texts in lang/<code>/farmer_notice.php and the message type it is sent as. */
    public const array KINDS = [
        'prices' => 'advice',
        'fertilizers' => 'advice',
        'starch' => 'advice',
        'app_update' => 'announcement',
        'app_forced_update' => 'announcement',
    ];

    /** Kinds that name no country: they go to every farmer in each language. */
    private const array GLOBAL_KINDS = ['app_update', 'app_forced_update'];

    public function __construct(
        private readonly MessageService $messages,
        private readonly CountryRepo $countries,
    ) {}

    /** Whether admin changes notify farmers: switched on, and not from a console command, seeder or migration. */
    public static function enabled(): bool
    {
        return (bool)config('farmer-notifications.enabled')
            && (!app()->runningInConsole() || (bool)config('farmer-notifications.in_console'));
    }

    /**
     * Queues a notice for a change. Never throws: a queue that is down must not stop the admin's save.
     *
     * @param string|null $country country code the change applies to; null for kinds that name none
     */
    public function request(string $kind, ?string $country = null): void
    {
        if (!self::enabled() || !isset(self::KINDS[$kind])) {
            return;
        }
        $country = in_array($kind, self::GLOBAL_KINDS, true) ? null : ($country ? strtoupper($country) : null);
        if ($country === null && !in_array($kind, self::GLOBAL_KINDS, true)) {
            return;
        }

        try {
            SendChangeNotice::dispatch($kind, $country)->delay(now()->addMinutes((int)config('farmer-notifications.debounce_minutes')));
        } catch (Throwable $e) {
            Log::warning('Farmer notice could not be queued', ['kind' => $kind, 'country' => $country, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Stores one message per language for the notice (each queues its own push). Runs inside the queue job.
     *
     * @return int messages stored
     */
    public function deliver(string $kind, ?string $country): int
    {
        if (!isset(self::KINDS[$kind])) {
            return 0;
        }
        $name = $country !== null ? ($this->countries->nameOf($country) ?? $country) : '';
        $count = 0;

        foreach ((array)config('farmer-notifications.languages') as $language) {
            $this->messages->publish([
                'type' => self::KINDS[$kind],
                'title' => __("farmer_notice.{$kind}.title", ['country' => $name], $language),
                'body' => __("farmer_notice.{$kind}.body", ['country' => $name], $language),
                'country' => $country,
                'language' => $language,
                'expires_at' => now()->addDays((int)config('farmer-notifications.expires_days')),
            ], null);
            $count++;
        }

        return $count;
    }
}
