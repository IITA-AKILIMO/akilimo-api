<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Repositories\AppVersionRepo;

/**
 * What each mobile platform must run: the row in `app_versions`, set through the API.
 */
class AppVersionService
{
    public const array PLATFORMS = ['android', 'ios'];

    /** Seconds a phone or proxy may reuse the answer. */
    public const int CACHE_SECONDS = 300;

    public function __construct(protected AppVersionRepo $repo) {}

    /**
     * @return array{min_supported: int, latest: int, message: ?string, store_url: ?string}
     */
    public function forPlatform(string $platform): array
    {
        // The migration stores a row per platform; a missing one (deleted by hand) never blocks anybody
        $config = $this->repo->forPlatform($platform)?->only(['min_supported', 'latest', 'message', 'store_url']) ?? [];
        $min = max((int)($config['min_supported'] ?? 1), 1);
        $message = trim((string)($config['message'] ?? ''));
        $storeUrl = trim((string)($config['store_url'] ?? ''));

        return [
            'min_supported' => $min,
            // The newest version can never be older than the minimum
            'latest' => max((int)($config['latest'] ?? $min), $min),
            'message' => $message === '' ? null : $message,
            'store_url' => $storeUrl === '' ? null : $storeUrl,
        ];
    }

    /**
     * @return array<string, array{min_supported: int, latest: int, message: ?string, store_url: ?string}>
     */
    public function all(?string $platform = null): array
    {
        $platforms = $platform === null ? self::PLATFORMS : [$platform];

        return collect($platforms)->mapWithKeys(fn (string $p) => [$p => $this->forPlatform($p)])->all();
    }

    /**
     * Stores the given fields (the others keep their current value). The latest version is never kept below the minimum.
     *
     * @param array<string, mixed> $data min_supported, latest, message, store_url (null or missing = unchanged)
     * @return array{min_supported: int, latest: int, message: ?string, store_url: ?string}
     *
     * @throws \Throwable
     */
    public function update(string $platform, array $data): array
    {
        $values = array_merge(
            $this->forPlatform($platform),
            array_filter(
                array_intersect_key($data, array_flip(['min_supported', 'latest', 'message', 'store_url'])),
                fn ($v) => $v !== null,
            ),
        );
        $values['latest'] = max((int)$values['latest'], (int)$values['min_supported']);

        $this->repo->createOrUpdate([...$values, 'platform' => $platform], ['platform' => $platform]);

        return $this->forPlatform($platform);
    }

    public function cacheSeconds(): int
    {
        return self::CACHE_SECONDS;
    }
}
