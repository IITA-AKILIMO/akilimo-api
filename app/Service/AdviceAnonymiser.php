<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\ApiRequest;
use App\Repositories\ApiRequestRepo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Turns saved advice into an anonymous statistic, as the privacy policy says: the link to the account and the phone's
 * device token go, every personal detail in the kept request is replaced by an anonymised id (name, user name, email
 * address, phone number, farm name: those fields are required or validated, so they are never left empty), and the stored
 * PDF reports, which print the name, phone and farm, are deleted. Location and answers stay.
 * Used when one item, several items or the whole account is deleted. Never log what is scrubbed.
 */
class AdviceAnonymiser
{
    /** Keys that hold personal details, compared without case or underscores (so first_name and firstName match). */
    private const PERSONAL_KEYS = [
        'firstname', 'lastname', 'username', 'emailaddress', 'phonenumber', 'farmname', 'devicetoken',
    ];

    /** Placeholder email domain: reserved by RFC 2606, so it never reaches a real mailbox. */
    private const EMAIL_DOMAIN = 'deleted.invalid';

    public function __construct(
        private readonly RecommendationReportService $reports,
        private readonly ApiRequestRepo $requests,
    ) {}

    /**
     * Anonymise a farmer's saved advice: all of it, or only the given request ids (ids that are not theirs are skipped).
     *
     * @param list<string> $requestIds
     */
    public function releaseForUser(int $userId, array $requestIds = []): int
    {
        return $this->release($this->requests->forUser($userId, $requestIds));
    }

    /** Anonymise every request the query matches; returns how many. */
    public function release(Builder $query): int
    {
        $count = 0;

        $query->chunkById(100, function ($rows) use (&$count) {
            /** @var ApiRequest $row */
            foreach ($rows as $row) {
                $anonId = $this->anonId($row->request_id);

                $row->forceFill([
                    'user_id' => null,
                    'device_token' => null,
                    'droid_request' => $this->scrub((array)$row->droid_request, $anonId),
                    'plumber_request' => $this->scrub((array)$row->plumber_request, $anonId),
                ])->save();

                $this->reports->forget($row->request_id);
                $count++;
            }
        });

        return $count;
    }

    /** The same short id for every personal field of one request, derived from its request id, so it points at nobody. */
    public function anonId(string $requestId): string
    {
        return substr(hash('sha256', $requestId), 0, 10);
    }

    /**
     * Replace the personal details with anonymised ids: "anon-<id>" for names and phone, "anon-<id>@deleted.invalid" for
     * the email address, and a fresh random UUID for a device token (it must stay a valid UUID).
     *
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    public function scrub(array $data, string $anonId): array
    {
        foreach ($data as $key => $value) {
            $name = is_string($key) ? strtolower(str_replace('_', '', $key)) : null;

            if ($name !== null && in_array($name, self::PERSONAL_KEYS, true)) {
                $data[$key] = match ($name) {
                    'emailaddress' => "anon-{$anonId}@" . self::EMAIL_DOMAIN,
                    'devicetoken' => (string)Str::uuid(),
                    default => "anon-{$anonId}",
                };
            } elseif (is_array($value)) {
                $data[$key] = $this->scrub($value, $anonId);
            }
        }

        return $data;
    }
}
