<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Message $message */
        $message = $this->resource;

        return [
            /** Stable id, the same one sent in the push, so the app stores a message once */
            'id' => $message->id,
            /** advice, reminder or announcement */
            'type' => $message->type->value,
            'title' => $message->title,
            'body' => $message->body,
            /** Screen the message opens in the app, or null for the type's default */
            'target' => $message->target,
            'country' => $message->country,
            'language' => $message->language,
            'created_at' => $message->created_at?->toIso8601String(),
            'expires_at' => $message->expires_at?->toIso8601String(),
        ];
    }
}
