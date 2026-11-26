<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Message;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepo<Message>
 */
class MessageRepo extends BaseRepo
{
    protected function model(): string
    {
        return Message::class;
    }

    /**
     * Messages a phone should have: for its country (or any country) and its language (or any language), not expired,
     * and the private ones for [$deviceToken] (or its account), from [$since] on (inclusive, the app ignores a repeat), oldest first so the app can pass the last one's time as the next [$since].
     *
     * @return Collection<int, Message>
     */
    public function forAudience(?string $country, ?string $language, ?CarbonInterface $since, int $limit, ?string $deviceToken = null): Collection
    {
        $userId = $deviceToken ? app(PushDeviceRepo::class)->userIdOf($deviceToken) : null;

        return $this->query()
            // A private message goes only to its own phone, or to the account that phone is signed in to
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->whereNull('recipient_device_token')->whereNull('recipient_user_id'))
                ->when($deviceToken, fn ($q) => $q->orWhere('recipient_device_token', $deviceToken))
                ->when($userId, fn ($q) => $q->orWhere('recipient_user_id', $userId)))
            ->where(fn ($q) => $q->whereNull('country')->when($country, fn ($q) => $q->orWhere('country', $country)))
            ->where(fn ($q) => $q->whereNull('language')->when($language, fn ($q) => $q->orWhere('language', $language)))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
