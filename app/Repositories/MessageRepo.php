<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Enums\PushStatus;
use App\Models\Message;
use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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

    /** Messages newest first, for the admin list. */
    public function latestPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->query()->latest('created_at')->paginate($perPage)->withQueryString();
    }

    /**
     * Records the outcome of a push job. The outcomes rank: a message that reached any phone stays pushed, a failure
     * only replaces queued or not pushed, and not pushed only replaces queued, so jobs for several phones of one message
     * finishing in any order leave the best result. One conditional UPDATE, safe when jobs run in parallel.
     */
    public function markPush(string $id, PushStatus $status, ?string $error = null): void
    {
        $replaces = match ($status) {
            PushStatus::Pushed => null,
            PushStatus::Failed => [PushStatus::Queued->value, PushStatus::NotPushed->value],
            default => [PushStatus::Queued->value],
        };

        $this->query()->whereKey($id)
            ->when($replaces !== null, fn ($q) => $q->whereIn('push_status', $replaces))
            ->update([
                'push_status' => $status->value,
                'push_error' => $status === PushStatus::Pushed ? null : ($error !== null ? mb_substr($error, 0, 255) : null),
                ...($status === PushStatus::Pushed ? ['pushed_at' => now()] : []),
            ]);
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
