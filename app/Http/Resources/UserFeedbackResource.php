<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use App\Models\UserFeedback;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserFeedback */
class UserFeedbackResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'recommendation_id' => $this->id,
            'akilimo_usage' => $this->akilimo_usage,
            'use_case' => $this->use_case,
            'user_type' => $this->user_type,
            'recommendation_rating' => $this->akilimo_rec_rating,
            'useful_rating' => $this->akilimo_useful_rating,
            'language' => $this->language,
            'device_token' => $this->device_token,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
