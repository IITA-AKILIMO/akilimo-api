<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;

/** A message as an admin sees it: the public fields plus what became of its push. Never returned to phones. */
class AdminMessageResource extends MessageResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Message $message */
        $message = $this->resource;

        return [
            ...parent::toArray($request),
            /** queued, pushed, failed or not_pushed */
            'push_status' => $message->push_status->value,
            /** Why the last push attempt failed, when it did */
            'push_error' => $message->push_error,
            'pushed_at' => $message->pushed_at?->toIso8601String(),
        ];
    }
}
