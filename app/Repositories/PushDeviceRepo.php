<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\PushDevice;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepo<PushDevice>
 */
class PushDeviceRepo extends BaseRepo
{
    protected function model(): string
    {
        return PushDevice::class;
    }

    /** Saves or refreshes the phone under its device_token. @param array<string, mixed> $attributes */
    public function upsert(string $deviceToken, array $attributes): PushDevice
    {
        return $this->query()->updateOrCreate(['device_token' => $deviceToken], $attributes);
    }

    /** Drops every other phone that holds the same Firebase token (a reinstall under a new device_token). */
    public function deleteOthersWithFcmToken(string $fcmToken, string $keepDeviceToken): void
    {
        $this->query()->where('fcm_token', $fcmToken)->where('device_token', '!=', $keepDeviceToken)->delete();
    }

    public function deleteByDeviceToken(string $deviceToken): void
    {
        $this->query()->where('device_token', $deviceToken)->delete();
    }

    public function deleteByFcmToken(string $fcmToken): void
    {
        $this->query()->where('fcm_token', $fcmToken)->delete();
    }

    /** @return Collection<int, PushDevice> */
    public function forUser(int $userId): Collection
    {
        return $this->query()->where('user_id', $userId)->get();
    }

    /** @return Collection<int, PushDevice> */
    public function forDeviceToken(string $deviceToken): Collection
    {
        return $this->query()->where('device_token', $deviceToken)->get();
    }

    /** Registered phones whose device_token starts with [$prefix] (an admin searching by token). @return Collection<int, PushDevice> */
    public function searchByTokenPrefix(string $prefix, int $limit): Collection
    {
        return $this->query()->where('device_token', 'like', addcslashes($prefix, '%_\\') . '%')->limit($limit)->get();
    }

    public function userIdOf(string $deviceToken): ?int
    {
        $id = $this->query()->where('device_token', $deviceToken)->value('user_id');

        return $id === null ? null : (int)$id;
    }

    public function hasDeviceToken(string $deviceToken): bool
    {
        return $this->query()->where('device_token', $deviceToken)->exists();
    }
}
