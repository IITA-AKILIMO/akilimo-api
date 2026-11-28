<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\PushDevice;
use App\Repositories\PushDeviceRepo;
use Illuminate\Support\Collection;

/** Keeps the list of phones that can be pushed to individually. */
class PushDeviceService
{
    public function __construct(private readonly PushDeviceRepo $devices) {}

    /**
     * Saves or refreshes a phone. `$userId` is the signed-in farmer or null (a sign-out clears the link). The same
     * Firebase token under another device_token (a reinstall) is dropped, so a push never reaches the same phone twice.
     *
     * @param array{device_token: string, fcm_token: string, platform?: ?string, app_version?: ?string, country?: ?string, language?: ?string} $data
     */
    public function register(array $data, ?int $userId): PushDevice
    {
        $this->devices->deleteOthersWithFcmToken($data['fcm_token'], $data['device_token']);

        return $this->devices->upsert($data['device_token'], [
            'fcm_token' => $data['fcm_token'],
            'platform' => $data['platform'] ?? 'android',
            'app_version' => $data['app_version'] ?? null,
            'country' => $data['country'] ?? null,
            'language' => $data['language'] ?? null,
            'user_id' => $userId,
            'last_seen_at' => now(),
        ]);
    }

    public function unregister(string $deviceToken): void
    {
        $this->devices->deleteByDeviceToken($deviceToken);
    }

    /**
     * The phones a recipient reaches: a farmer account (every phone signed in to it) or one phone by device_token.
     *
     * @param array{type: string, id: string|int} $recipient
     * @return Collection<int, PushDevice>
     */
    public function forRecipient(array $recipient): Collection
    {
        return $recipient['type'] === 'user'
            ? $this->devices->forUser((int)$recipient['id'])
            : $this->devices->forDeviceToken((string)$recipient['id']);
    }

    /**
     * How many distinct phones a list of recipients reaches (what the admin page shows before sending).
     *
     * @param array<int, array{type: string, id: string|int}> $recipients
     */
    public function countFor(array $recipients): int
    {
        return collect($recipients)->flatMap(fn (array $r) => $this->forRecipient($r))->unique('id')->count();
    }
}
